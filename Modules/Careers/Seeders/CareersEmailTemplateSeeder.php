<?php

namespace Modules\Careers\Seeders;

use Illuminate\Database\Seeder;
use Modules\Email\Models\EmailTemplate;

class CareersEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Career Application Confirmation',
                'slug' => 'career-application-confirmation',
                'subject' => 'Application received for {job_title} at {site_name}',
                'body' => '<h2 style="margin:0 0 12px;">Thanks for applying, {name}</h2><p>We received your application for <strong>{job_title}</strong>.</p><p>Location: {job_location}<br>Type: {job_type}</p><p>Our hiring team will review your profile and contact you if your experience matches the role.</p><div style="text-align:left;"><table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; display:inline-table;"><tr><td bgcolor="#ff6c2f" style="background:#ff6c2f; border-radius:8px;"><a href="{job_url}" style="display:inline-block; background:#ff6c2f; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none; font-weight:600;">View Job</a></td></tr></table></div>',
                'variables' => ['name', 'job_title', 'job_location', 'job_type', 'job_url', 'site_name'],
            ],
            [
                'name' => 'Career Application Admin Notification',
                'slug' => 'career-application-admin-notification',
                'subject' => 'New job application: {job_title}',
                'body' => '<h2 style="margin:0 0 12px;">New application submitted</h2><p><strong>{name}</strong> applied for <strong>{job_title}</strong>.</p><p>Email: {email}<br>Phone: {phone}<br>LinkedIn: {linkedin_url}</p><p>Resume: {resume_filename}</p><p>Cover Letter:</p><p>{cover_letter}</p><div style="text-align:left;"><table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate; display:inline-table;"><tr><td bgcolor="#ff6c2f" style="background:#ff6c2f; border-radius:8px;"><a href="{button_url}" style="display:inline-block; background:#ff6c2f; color:#ffffff; padding:12px 18px; border-radius:8px; text-decoration:none; font-weight:600;">Review Application</a></td></tr></table></div>',
                'variables' => ['name', 'email', 'phone', 'job_title', 'linkedin_url', 'resume_filename', 'cover_letter', 'button_url'],
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::query()->updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'variables' => $template['variables'],
                    'use_header' => true,
                    'use_footer' => true,
                    'use_signature' => true,
                    'status' => true,
                ]
            );
        }
    }
}
