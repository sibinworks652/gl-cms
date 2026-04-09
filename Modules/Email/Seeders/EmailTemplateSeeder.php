<?php

namespace Modules\Email\Seeders;

use Illuminate\Database\Seeder;
use Modules\Email\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Contact Form Email',
                'slug' => 'contact-form',
                'subject' => 'New message from {name}',
                'body' => '<h2 style="margin:0 0 12px;">New contact message</h2><p><strong>Name:</strong> {name}</p><p><strong>Email:</strong> {email}</p><p>{message}</p>',
                'variables' => ['name', 'email', 'message', 'date'],
            ],
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {site_name}, {name}',
                'body' => '<h2 style="margin:0 0 12px;">Welcome, {name}</h2><p>We are glad to have you here.</p><p>You can reach us anytime at {email}.</p>',
                'variables' => ['name', 'email', 'date'],
            ],
            [
                'name' => 'Backup Completed Email',
                'slug' => 'backup-completed',
                'subject' => 'Backup completed on {date}',
                'body' => '<h2 style="margin:0 0 12px;">Backup completed</h2><p>{site_name} backup finished successfully on {date}.</p><p>Status: {status}</p>',
                'variables' => ['site_name', 'date', 'status'],
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template + ['status' => true]
            );
        }
    }
}
