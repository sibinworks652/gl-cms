<?php

namespace Modules\Backup\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ModuleRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Backup\Services\BackupManager;
use Modules\Settings\Models\Setting;

class BackupController extends Controller
{
    public function __construct(
        protected BackupManager $backups
    )
    {
    }

    public function index()
    {
        $admin = auth('admin')->user()?->load('googleAccount');

        return view('backup::index', [
            'backups' => $this->backups->backups(),
            'googleConfigured' => $this->backups->googleDriveConfigured(),
            'googleConfigurationError' => $this->backups->googleDriveConfigurationError(),
            'googleAccount' => $admin?->googleAccount,
            'googleRedirectUri' => config('backup.google.redirect_uri') ?: route('admin.backups.google.callback'),
        ]);
    }

    public function store(Request $request)
    {
        $uploadToGoogle = (bool) $request->boolean('upload_to_google');
        $result = $this->backups->create(
            uploadToGoogle: $uploadToGoogle,
            user: $request->user('admin')->load('googleAccount')
        );

        $this->queueBackupNotification($result);

        if (! $uploadToGoogle) {
            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Backup created successfully.');
        }

        if ($result['google_uploaded']) {
            return redirect()
                ->route('admin.backups.index')
                ->with('success', 'Backup created and uploaded to Google Drive successfully.');
        }

        if ($result['fallback_uploaded']) {
            return redirect()
                ->route('admin.backups.index')
                ->with('warning', 'Backup created locally. Google Drive upload skipped: ' . $result['google_error']);
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('warning', 'Backup created locally. Google Drive upload skipped: ' . $result['google_error'] . ' Fallback upload failed: ' . $result['fallback_error']);
    }

    protected function queueBackupNotification(array $result, $recipient = null): void
    {
        if (! $this->emailModuleAvailable()) {
            return;
        }

        $recipient ??= $this->setting('mail_from_address', config('mail.from.address'));

        if (blank($recipient)) {
            Log::warning('Backup notification skipped because no recipient is configured.', [
                'backup_filename' => $result['filename'] ?? null,
            ]);

            return;
        }

        $template = $this->emailTemplate('backup-notification') ?: $this->emailTemplate('backup_notification');

        if (! $template) {
            Log::warning('Backup notification email template not found.', [
                'template_slug' => 'backup-notification',
                'backup_filename' => $result['filename'] ?? null,
            ]);

            return;
        }

        try {
            $payload = [
                'backup_name' => $result['filename'] ?? 'Backup Creation',
                'backup_date' => now()->format('d M Y H:i:s A'),
                'file_size' => $this->formatBytes((int) ($result['size'] ?? 0)),
                'google_status' => ($result['google_uploaded'] ?? false) ? 'Uploaded to Google Drive' : 'Not uploaded to Google Drive',
                'google_error' => $result['google_error'] ?? '',
                'fallback_status' => ($result['fallback_uploaded'] ?? false) ? 'Fallback uploaded' : 'Fallback not used',
                'fallback_error' => $result['fallback_error'] ?? '',
            ];

            $genericMailClass = \Modules\Email\Mail\GenericMail::class;
            $emailService = $this->emailService();

            if (class_exists($genericMailClass)) {
                Mail::to($recipient)->queue(new $genericMailClass($template, $payload));
            }

            $emailService?->sendTemplate($template, $payload, $recipient);
        } catch (\Throwable $exception) {
            Log::error('Unable to queue backup notification email.', [
                'backup_filename' => $result['filename'] ?? null,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $size = $bytes;

        while ($size >= 1024 && $index < count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return round($size, 2) . ' ' . $units[$index];
    }

    protected function emailService(): ?object
    {
        $class = \Modules\Email\Services\EmailService::class;

        if (! $this->emailModuleAvailable() || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }

    protected function emailModuleAvailable(): bool
    {
        return ModuleRegistry::enabled('email')
            && class_exists(\Modules\Email\Services\EmailService::class)
            && class_exists(\Modules\Email\Models\EmailTemplate::class);
    }

    protected function emailTemplate(string $slug): mixed
    {
        $class = \Modules\Email\Models\EmailTemplate::class;

        if (! $this->emailModuleAvailable() || ! class_exists($class)) {
            return null;
        }

        return $class::query()->where('slug', $slug)->first();
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        if (! class_exists(Setting::class)) {
            return $default;
        }

        return Setting::value($key, $default);
    }

    public function download(string $filename)
    {
        return response()->download($this->backups->path($filename));
    }

    public function destroy(Request $request, string $filename)
    {
        $this->backups->delete($filename, (bool) $request->boolean('delete_google'), $request->user('admin')->load('googleAccount'));

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Backup deleted successfully.');
    }
}
