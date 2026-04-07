<?php

namespace Modules\Backup\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Backup\Services\BackupManager;

class BackupController extends Controller
{
    public function __construct(protected BackupManager $backups)
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
