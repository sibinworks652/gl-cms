<?php

namespace Modules\Backup\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Backup\Services\BackupManager;
use Modules\Email\Mail\GenericMail;
use Modules\Email\Services\EmailService;

class BackupController extends Controller
{
    public function __construct(
        protected BackupManager $backups,
        protected EmailService $email
    )
    {
    }

    public function index()
    {
        $admin = auth('admin')->user()?->load('googleAccount');

        $template = emailTemplate('backup-notification') ?: emailTemplate('backup_notification');
        // dd($template->to_emails);

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
        $recipient ??= setting('mail_from_address', config('mail.from.address'));

        if (blank($recipient)) {
            Log::warning('Backup notification skipped because no recipient is configured.', [
                'backup_filename' => $result['filename'] ?? null,
            ]);

            return;
        }

        $template = emailTemplate('backup-notification') ?: emailTemplate('backup_notification');

        if (! $template) {
            Log::warning('Backup notification email template not found.', [
                'template_slug' => 'backup-notification',
                'backup_filename' => $result['filename'] ?? null,
            ]);

            return;
        }

        try {
            Mail::to($recipient)
                ->queue(new GenericMail($template, [
                    'backup_name' => $result['filename'] ?? 'Backup Creation',
                    'backup_date' => now()->format('d M Y H:i:s A'),
                    'file_size' => $this->formatBytes((int) ($result['size'] ?? 0)),
                    'google_status' => ($result['google_uploaded'] ?? false) ? 'Uploaded to Google Drive' : 'Not uploaded to Google Drive',
                    'google_error' => $result['google_error'] ?? '',
                    'fallback_status' => ($result['fallback_uploaded'] ?? false) ? 'Fallback uploaded' : 'Fallback not used',
                    'fallback_error' => $result['fallback_error'] ?? '',
                ]));
            $this->email->sendTemplate($template, [
                'backup_name' => $result['filename'] ?? 'Backup Creation',
                'backup_date' => now()->format('d M Y H:i:s A'),
                'file_size' => $this->formatBytes((int) ($result['size'] ?? 0)),
                'google_status' => ($result['google_uploaded'] ?? false) ? 'Uploaded to Google Drive' : 'Not uploaded to Google Drive',
                'google_error' => $result['google_error'] ?? '',
                'fallback_status' => ($result['fallback_uploaded'] ?? false) ? 'Fallback uploaded' : 'Fallback not used',
                'fallback_error' => $result['fallback_error'] ?? '',
            ], $recipient);
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
