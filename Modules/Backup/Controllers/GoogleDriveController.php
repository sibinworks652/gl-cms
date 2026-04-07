<?php

namespace Modules\Backup\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Backup\Services\GoogleDriveOAuthService;
use Throwable;

class GoogleDriveController extends Controller
{
    public function __construct(protected GoogleDriveOAuthService $googleDrive)
    {
    }

    public function redirect(Request $request)
    {
        $admin = $request->user('admin');

        return redirect()->away($this->googleDrive->authorizationUrl($admin));
    }

    public function callback(Request $request)
    {
        if ($request->string('state')->toString() !== session()->pull('google_drive_oauth_state')) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Google Drive connection could not be verified. Please try again.');
        }

        if ($request->filled('error')) {
            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Google Drive connection was cancelled.');
        }

        try {
            $account = $this->googleDrive->connect($request->user('admin'), (string) $request->string('code'));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.backups.index')
                ->with('error', 'Google Drive connection failed: ' . $exception->getMessage());
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Google Drive connected: ' . $account->google_email);
    }

    public function disconnect(Request $request)
    {
        $this->googleDrive->disconnect($request->user('admin'));

        return redirect()
            ->route('admin.backups.index')
            ->with('success', 'Google Drive disconnected successfully.');
    }
}
