<?php

namespace Modules\Backup\Services;

use App\Models\Admin;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Backup\Models\GoogleAccount;
use RuntimeException;

class GoogleDriveOAuthService
{
    protected const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    protected const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    protected const DRIVE_UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';
    protected const DRIVE_FOLDER_MIME_TYPE = 'application/vnd.google-apps.folder';

    public function authorizationUrl(Admin $admin): string
    {
        $state = Str::random(40);
        session(['google_drive_oauth_state' => $state]);

        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', [
                'openid',
                'email',
                'https://www.googleapis.com/auth/drive',
            ]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
            'login_hint' => $admin->email,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(Admin $admin, string $code): GoogleAccount
    {
        $token = $this->tokenRequest([
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        $userInfo = Http::withToken($token['access_token'])
            ->acceptJson()
            ->get(self::USERINFO_URL)
            ->throw()
            ->json();

        $existingAccount = $admin->googleAccount()->first();
        $refreshToken = $token['refresh_token'] ?? $existingAccount?->refresh_token;

        if (! $refreshToken) {
            throw new RuntimeException('Google did not return a refresh token. Please disconnect and connect Google Drive again.');
        }

        return GoogleAccount::updateOrCreate(
            ['user_id' => $admin->getKey()],
            [
                'google_email' => $userInfo['email'] ?? $admin->email,
                'access_token' => $token['access_token'],
                'refresh_token' => $refreshToken,
                'access_token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
                'refresh_token_expires_at' => isset($token['refresh_token_expires_in'])
                    ? now()->addSeconds((int) $token['refresh_token_expires_in'])
                    : null,
                'drive_folder_id' => $existingAccount?->drive_folder_id,
            ]
        );
    }

    public function disconnect(Admin $admin): void
    {
        $admin->googleAccount()->delete();
    }

    public function validAccessToken(GoogleAccount $account): string
    {
        if (! $account->accessTokenExpired()) {
            return $account->access_token;
        }

        if (! $account->refresh_token || $account->refresh_token_expires_at?->isPast()) {
            throw new RuntimeException('Google Drive connection expired. Please connect Google Drive again.');
        }

        $token = $this->tokenRequest([
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $account->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        $account->forceFill([
            'access_token' => $token['access_token'],
            'access_token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600) - 60),
        ])->save();

        return $account->access_token;
    }

    public function uploadBackup(GoogleAccount $account, string $path, string $filename): array
    {
        $folderId = $account->drive_folder_id ?: $this->ensureBackupFolder($account);
        $metadata = [
            'name' => $filename,
            'parents' => [$folderId],
        ];

        $boundary = 'cms_backup_' . Str::random(24);
        $body = implode("\r\n", [
            '--' . $boundary,
            'Content-Type: application/json; charset=UTF-8',
            '',
            json_encode($metadata, JSON_THROW_ON_ERROR),
            '--' . $boundary,
            'Content-Type: application/zip',
            '',
            file_get_contents($path),
            '--' . $boundary . '--',
            '',
        ]);

        $response = Http::withToken($this->validAccessToken($account))
            ->withBody($body, 'multipart/related; boundary=' . $boundary)
            ->post(self::DRIVE_UPLOAD_URL . '?' . http_build_query([
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink',
            ]))
            ->throw()
            ->json();

        return [
            'id' => $response['id'] ?? null,
            'name' => $response['name'] ?? $filename,
            'webViewLink' => $response['webViewLink'] ?? null,
        ];
    }

    public function deleteBackup(GoogleAccount $account, string $filename): void
    {
        $folderId = $account->drive_folder_id;

        if (! $folderId) {
            return;
        }

        $query = sprintf(
            "name='%s' and '%s' in parents and trashed=false",
            str_replace(["\\", "'"], ["\\\\", "\\'"], $filename),
            str_replace("'", "\\'", $folderId)
        );

        $files = Http::withToken($this->validAccessToken($account))
            ->acceptJson()
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id,name)',
            ])
            ->throw()
            ->json('files', []);

        foreach ($files as $file) {
            if (! empty($file['id'])) {
                Http::withToken($this->validAccessToken($account))
                    ->delete('https://www.googleapis.com/drive/v3/files/' . $file['id'])
                    ->throw();
            }
        }
    }

    protected function ensureBackupFolder(GoogleAccount $account): string
    {
        $response = Http::withToken($this->validAccessToken($account))
            ->acceptJson()
            ->post('https://www.googleapis.com/drive/v3/files?fields=id,name', [
                'name' => config('backup.google.folder_name', 'CMS Backups'),
                'mimeType' => self::DRIVE_FOLDER_MIME_TYPE,
            ])
            ->throw()
            ->json();

        $folderId = $response['id'] ?? null;

        if (! $folderId) {
            throw new RuntimeException('Google Drive folder could not be created.');
        }

        $account->forceFill(['drive_folder_id' => $folderId])->save();

        return $folderId;
    }

    protected function tokenRequest(array $payload): array
    {
        return $this->assertSuccessfulTokenResponse(
            Http::asForm()->acceptJson()->post(self::TOKEN_URL, $payload)
        );
    }

    protected function assertSuccessfulTokenResponse(Response $response): array
    {
        if ($response->failed()) {
            throw new RuntimeException($response->json('error_description') ?: $response->json('error') ?: 'Google authentication failed.');
        }

        return $response->json();
    }

    protected function clientId(): string
    {
        return (string) config('backup.google.client_id');
    }

    protected function clientSecret(): string
    {
        return (string) config('backup.google.client_secret');
    }

    protected function redirectUri(): string
    {
        return config('backup.google.redirect_uri') ?: route('admin.backups.google.callback');
    }
}
