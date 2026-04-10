<?php

use Modules\ActivityLogs\Services\ActivityLogManager;

if (! function_exists('activity_log')) {
    function activity_log(string $action, string $module, string $description, array $attributes = [])
    {
        return app(ActivityLogManager::class)->log(array_merge($attributes, [
            'action' => $action,
            'module' => $module,
            'description' => $description,
        ]));
    }
}
