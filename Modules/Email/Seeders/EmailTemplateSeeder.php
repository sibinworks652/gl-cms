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
                'name' => 'Invoice Email',
                'slug' => 'invoice',
                'subject' => 'Invoice {invoice_number} from {site_name}',
                'body' => '<h2 style="margin:0 0 12px;">Invoice {invoice_number}</h2><p>Hello {name},</p><p>Your invoice amount is <strong>{amount}</strong>.</p><p>Status: {status}</p><a href="{button_url}" style="display:inline-block; background:var(--bs-primary); color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none;">View Invoice</a>',
                'variables' => ['name', 'invoice_number', 'amount', 'status', 'button_url', 'date'],
            ],
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {site_name}, {name}',
                'body' => '<h2 style="margin:0 0 12px;">Welcome, {name}</h2><p>We are glad to have you here.</p><p>You can reach us anytime at {email}.</p>',
                'variables' => ['name', 'email', 'date'],
            ],
            [
                'name' => 'Notification Email',
                'slug' => 'notification',
                'subject' => '{site_name} notification',
                'body' => '<h2 style="margin:0 0 12px;">Notification</h2><p>Hello {name},</p><p>{message}</p>',
                'variables' => ['name', 'message', 'date'],
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
