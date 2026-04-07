<?php

namespace Modules\Backup\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Backup\Models\BackupFile;
use Modules\Backup\Models\GoogleAccount;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupManager
{
    public function __construct(protected GoogleDriveOAuthService $googleDrive)
    {
    }

    public function create(bool $uploadToGoogle = true, ?Admin $user = null, ?GoogleAccount $googleAccount = null): array
    {
        $backupDirectory = $this->backupDirectory();
        File::ensureDirectoryExists($backupDirectory);
        $siteName = config('app.name');

        // optional: make it filesystem-safe
        $siteName = Str::slug($siteName);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = $siteName . '-backup-' . $timestamp . '.zip';
        $zipPath = $backupDirectory . DIRECTORY_SEPARATOR . $filename;
        $databasePath = $backupDirectory . DIRECTORY_SEPARATOR . 'database-' . $timestamp . '.sql';

        $this->writeDatabaseDump($databasePath);
        $this->writeZip($zipPath, $databasePath);
        File::delete($databasePath);

        $result = [
            'filename' => $filename,
            'path' => $zipPath,
            'size' => File::size($zipPath),
            'google_uploaded' => false,
            'google_path' => null,
            'google_error' => null,
            'fallback_disk' => null,
            'fallback_uploaded' => false,
            'fallback_error' => null,
        ];

        if ($uploadToGoogle) {
            $account = $googleAccount ?: $user?->googleAccount;

            if ($account) {
                $result = array_merge($result, $this->uploadToConnectedGoogleDrive($account, $zipPath, $filename));
            } else {
                $result['google_error'] = 'Google Drive is not connected for this user.';
                $result = array_merge($result, $this->storeFallbackCopy($zipPath, $filename));
            }
        }

        $this->recordBackup($result, $user);

        return $result;
    }

    public function backups(): array
    {
        $backupDirectory = $this->backupDirectory();
        File::ensureDirectoryExists($backupDirectory);

        $this->syncExistingBackupFiles();

        return BackupFile::query()
            ->latest('created_at')
            ->get()
            ->map(fn (BackupFile $backup) => [
                'filename' => $backup->filename,
                'path' => $backup->path,
                'size' => $backup->size,
                'created_at' => optional($backup->created_at)->format('Y-m-d H:i:s'),
                'google_uploaded' => $backup->google_uploaded,
                'google_path' => $backup->google_path,
            ])
            ->values()
            ->all();
    }

    public function path(string $filename): string
    {
        $filename = basename($filename);
        $path = $this->backupDirectory() . DIRECTORY_SEPARATOR . $filename;

        if (! File::exists($path)) {
            throw new RuntimeException('Backup file not found.');
        }

        return $path;
    }

    public function delete(string $filename, bool $deleteFromGoogle = false, ?Admin $user = null): void
    {
        $filename = basename($filename);
        $path = $this->path($filename);

        File::delete($path);
        BackupFile::where('filename', $filename)->delete();

        if ($deleteFromGoogle && $user?->googleAccount) {
            try {
                $this->googleDrive->deleteBackup($user->googleAccount, $filename);
            } catch (Throwable) {
                // The local backup is already deleted; a missing Drive copy should not fail the request.
            }
        }
    }

    public function googleDriveConfigured(): bool
    {
        return $this->googleDriveConfigurationError() === null;
    }

    public function googleDriveConfigurationError(): ?string
    {
        if (! filled(config('backup.google.client_id')) || ! filled(config('backup.google.client_secret'))) {
            return 'Google Drive OAuth is not configured for this CMS. Add the internal Google OAuth client ID and secret before users connect Drive.';
        }

        return null;
    }

    protected function backupDirectory(): string
    {
        return storage_path('app/private/backups');
    }

    protected function recordBackup(array $result, ?Admin $user = null): BackupFile
    {
        return BackupFile::updateOrCreate(
            ['filename' => $result['filename']],
            [
                'user_id' => $user?->getKey() ?? Auth::id(),
                'disk' => 'local',
                'path' => $result['path'],
                'size' => $result['size'],
                'google_uploaded' => $result['google_uploaded'],
                'google_path' => $result['google_path'],
                'google_error' => $result['google_error'],
                'created_at' => now(),
            ]
        );
    }

    protected function syncExistingBackupFiles(): void
    {
        foreach (File::files($this->backupDirectory()) as $file) {
            if ($file->getExtension() !== 'zip') {
                continue;
            }

            BackupFile::firstOrCreate(
                ['filename' => $file->getFilename()],
                [
                    'disk' => 'local',
                    'path' => $file->getPathname(),
                    'size' => $file->getSize(),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ]
            );
        }
    }

    protected function writeZip(string $zipPath, string $databasePath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create backup zip file.');
        }

        $zip->addFile($databasePath, 'database/database.sql');
        $zip->addFromString('manifest.json', json_encode([
            'created_at' => now()->toISOString(),
            'app_url' => config('app.url'),
            'database_connection' => config('database.default'),
            'includes' => [
                'database/database.sql',
                'project/app',
                'project/bootstrap',
                'project/config',
                'project/database',
                'project/Modules',
                'project/public',
                'project/resources',
                'project/routes',
                'project/storage/app/public',
            ],
            'excludes' => $this->excludedBackupPaths(),
        ], JSON_PRETTY_PRINT));

        $this->addProjectFilesToZip($zip, $zipPath, $databasePath);

        $zip->close();
    }

    protected function addProjectFilesToZip(ZipArchive $zip, string $zipPath, string $databasePath): void
    {
        $excludedFiles = [
            $zipPath,
            $databasePath,
        ];

        foreach (['app', 'bootstrap', 'config', 'database', 'Modules', 'public', 'resources', 'routes', 'tests'] as $directory) {
            $path = base_path($directory);

            if (File::isDirectory($path)) {
                $this->addDirectoryToZip($zip, $path, 'project/' . $directory, $excludedFiles);
            }
        }

        $publicStoragePath = storage_path('app/public');

        if (File::isDirectory($publicStoragePath)) {
            $this->addDirectoryToZip($zip, $publicStoragePath, 'project/storage/app/public', $excludedFiles);
        }

        foreach (['.editorconfig', '.env', '.env.example', '.gitattributes', '.gitignore', 'artisan', 'composer.json', 'composer.lock', 'package.json', 'phpunit.xml', 'README.md', 'vite.config.js'] as $file) {
            $path = base_path($file);

            if (File::isFile($path) && is_readable($path)) {
                $zip->addFile($path, 'project/' . $file);
            }
        }
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipDirectory, array $excludedFiles = []): void
    {
        $excludedFiles = array_map(fn (string $path) => $this->normalizePath($path), $excludedFiles);

        foreach (File::allFiles($directory) as $file) {
            $path = $this->normalizePath($file->getPathname());

            if (in_array($path, $excludedFiles, true) || $this->shouldSkipBackupPath($path)) {
                continue;
            }

            if ($file->isLink() || ! $file->isReadable()) {
                continue;
            }

            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getPathname(), trim($zipDirectory . '/' . $relativePath, '/'));
        }
    }

    protected function shouldSkipBackupPath(string $path): bool
    {
        foreach ($this->excludedBackupPaths() as $excludedPath) {
            if ($path === $excludedPath || str_starts_with($path, $excludedPath . '/')) {
                return true;
            }
        }

        return false;
    }

    protected function excludedBackupPaths(): array
    {
        return [
            $this->normalizePath(base_path('.git')),
            $this->normalizePath(base_path('node_modules')),
            $this->normalizePath(base_path('vendor')),
            $this->normalizePath(storage_path('framework/cache')),
            $this->normalizePath(storage_path('framework/sessions')),
            $this->normalizePath(storage_path('framework/testing')),
            $this->normalizePath(storage_path('framework/views')),
            $this->normalizePath(storage_path('logs')),
            $this->normalizePath(storage_path('app/private/backups')),
            $this->normalizePath(public_path('storage')),
        ];
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function uploadToConnectedGoogleDrive(GoogleAccount $account, string $zipPath, string $filename): array
    {
        $configurationError = $this->googleDriveConfigurationError();

        if ($configurationError !== null) {
            return [
                'google_uploaded' => false,
                'google_path' => null,
                'google_error' => $configurationError,
            ];
        }

        try {
            $uploaded = $this->googleDrive->uploadBackup($account, $zipPath, $filename);

            return [
                'google_uploaded' => true,
                'google_path' => $uploaded['webViewLink'] ?? $uploaded['name'] ?? $filename,
                'google_error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'google_uploaded' => false,
                'google_path' => $filename,
                'google_error' => $this->googleDriveErrorMessage($exception),
            ];
        }
    }

    protected function storeFallbackCopy(string $zipPath, string $filename): array
    {
        $disk = config('backup.fallback_disk', 'local');

        if ($disk === 'local') {
            return [
                'fallback_disk' => $disk,
                'fallback_uploaded' => true,
                'fallback_error' => null,
            ];
        }

        try {
            Storage::disk($disk)->put('backups/' . $filename, File::get($zipPath));

            return [
                'fallback_disk' => $disk,
                'fallback_uploaded' => true,
                'fallback_error' => null,
            ];
        } catch (Throwable $exception) {
            return [
                'fallback_disk' => $disk,
                'fallback_uploaded' => false,
                'fallback_error' => $exception->getMessage(),
            ];
        }
    }

    protected function googleDriveErrorMessage(\Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'Invalid token format')) {
            return 'Google Drive authentication failed. Please create a new refresh token using the same Google client ID and secret, then update GOOGLE_DRIVE_REFRESH_TOKEN.';
        }

        return $message;
    }

    protected function writeDatabaseDump(string $path): void
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $this->writeMysqlDump($path);
            return;
        }

        if ($driver === 'sqlite') {
            $database = $connection->getDatabaseName();

            if (File::exists($database)) {
                File::copy($database, $path);
                return;
            }
        }

        File::put($path, '-- Database dump is not supported for driver: ' . $driver . PHP_EOL);
    }

    protected function writeMysqlDump(string $path): void
    {
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->values();

        $handle = fopen($path, 'wb');

        if (! $handle) {
            throw new RuntimeException('Unable to create database dump file.');
        }

        try {
            fwrite($handle, 'SET FOREIGN_KEY_CHECKS=0;' . PHP_EOL . PHP_EOL);

            foreach ($tables as $table) {
                $quotedTable = '`' . str_replace('`', '``', $table) . '`';
                $create = DB::selectOne('SHOW CREATE TABLE ' . $quotedTable);
                $createSql = (array) $create;

                fwrite($handle, 'DROP TABLE IF EXISTS ' . $quotedTable . ';' . PHP_EOL);
                fwrite($handle, ($createSql['Create Table'] ?? array_values($createSql)[1]) . ';' . PHP_EOL . PHP_EOL);

                DB::table($table)
                    ->orderByRaw('1')
                    ->chunk(500, function ($rows) use ($handle, $table, $quotedTable) {
                        foreach ($rows as $row) {
                            $data = (array) $row;
                            $columns = collect(array_keys($data))
                                ->map(fn ($column) => '`' . str_replace('`', '``', $column) . '`')
                                ->implode(', ');
                            $values = collect(array_values($data))
                                ->map(fn ($value) => $this->sqlValue($value))
                                ->implode(', ');

                            fwrite($handle, 'INSERT INTO ' . $quotedTable . ' (' . $columns . ') VALUES (' . $values . ');' . PHP_EOL);
                        }
                    });

                fwrite($handle, PHP_EOL);
            }

            fwrite($handle, 'SET FOREIGN_KEY_CHECKS=1;' . PHP_EOL);
        } finally {
            fclose($handle);
        }
    }

    protected function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return DB::connection()->getPdo()->quote((string) $value);
    }
}
