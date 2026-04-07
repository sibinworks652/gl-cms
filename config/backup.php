<?php

return [
    'fallback_disk' => env('BACKUP_FALLBACK_DISK', 'local'),

    'google' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_DRIVE_REDIRECT_URI'),
        'folder_name' => env('GOOGLE_DRIVE_FOLDER_NAME', 'CMS Backups'),
    ],

    'schedule' => [
        'enabled' => env('BACKUP_SCHEDULE_ENABLED', false),
        'frequency' => env('BACKUP_SCHEDULE_FREQUENCY', 'daily'),
        'time' => env('BACKUP_SCHEDULE_TIME', '02:00'),
    ],
];
