<?php

namespace Modules\Backup\Commands;

use Illuminate\Console\Command;
use Modules\Backup\Models\GoogleAccount;
use Modules\Backup\Services\BackupManager;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup:create
        {--no-google : Create the local backup without uploading to Google Drive}
        {--all-connected : Create one backup for each admin user with a connected Google Drive account}';

    protected $description = 'Create a CMS backup zip and upload it to Google Drive when configured.';

    public function handle(BackupManager $backups): int
    {
        if ($this->option('all-connected')) {
            return $this->createForConnectedAccounts($backups);
        }

        $result = $backups->create(uploadToGoogle: ! $this->option('no-google'));

        $this->info('Backup created: ' . $result['filename']);

        if ($result['google_uploaded']) {
            $this->info('Uploaded to Google Drive: ' . $result['google_path']);
        } elseif (! $this->option('no-google')) {
            $this->warn('Google Drive upload skipped or failed: ' . $result['google_error']);
        }

        return self::SUCCESS;
    }

    protected function createForConnectedAccounts(BackupManager $backups): int
    {
        $accounts = GoogleAccount::with('user')->get();

        if ($accounts->isEmpty()) {
            $this->warn('No Google Drive accounts are connected.');
            return self::SUCCESS;
        }

        foreach ($accounts as $account) {
            $result = $backups->create(uploadToGoogle: true, googleAccount: $account);

            $this->info('Backup created for ' . $account->google_email . ': ' . $result['filename']);

            if ($result['google_uploaded']) {
                $this->info('Uploaded to Google Drive: ' . $result['google_path']);
            } else {
                $this->warn('Google Drive upload failed: ' . $result['google_error']);
            }
        }

        return self::SUCCESS;
    }
}
