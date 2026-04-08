<?php

use Modules\Email\Models\EmailTemplate;
use Modules\Settings\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key, $default);
    }
}

if (! function_exists('emailTemplate')) {
    function emailTemplate(string $slug): ?EmailTemplate
    {
        return EmailTemplate::query()->where('slug', $slug)->first();
    }
}
