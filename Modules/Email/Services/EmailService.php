<?php

namespace Modules\Email\Services;

use InvalidArgumentException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Email\Mail\GenericMail;
use Modules\Email\Models\EmailTemplate;
use Modules\Settings\Models\Setting;

class EmailService
{
    public function __construct(protected TemplateService $templates)
    {
    }

    public function settings(): array
    {
        return Setting::pairs();
    }

    public function saveSettings(array $data, mixed $logo = null): void
    {
        foreach (['email_header', 'email_footer', 'email_signature'] as $key) {
            $data[$key] = $this->templates->sanitizeHtml($data[$key] ?? '');
        }

        foreach (['email_header', 'email_footer', 'email_signature', 'email_theme_color', 'email_text_color'] as $key) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => 'email',
                    'value' => $data[$key] ?? null,
                ]
            );
        }

        if ($logo) {
            $existing = Setting::query()->where('key', 'email_logo')->value('value');

            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            Setting::updateOrCreate(
                ['key' => 'email_logo'],
                [
                    'group' => 'email',
                    'value' => $logo->storeAs('email/branding', $this->datedOriginalFilename($logo), 'public'),
                ]
            );
        }

        Setting::clearCache();
    }

    public function render(EmailTemplate $template, array $data = []): string
    {
        $settings = $this->settings();
        $data = array_merge([
            'site_name' => $settings['site_name'] ?? config('app.name'),
            'date' => now()->format('d M Y'),
        ], $data);

        $body = $this->replaceVariables($template->body ?? '', $data);
        $header = $template->use_header ? $this->replaceVariables($settings['email_header'] ?? '', $data) : '';
        $footer = $template->use_footer ? $this->replaceVariables($settings['email_footer'] ?? '', $data) : '';
        $signature = $template->use_signature ? $this->replaceVariables($settings['email_signature'] ?? '', $data) : '';
        $subject = $this->replaceVariables($template->subject, $data);

        return view('email::emails.layout', [
            'subject' => $subject,
            'body' => $body,
            'header' => $header,
            'footer' => $footer,
            'signature' => $signature,
            'logoUrl' => ! empty($settings['email_logo']) ? asset('storage/' . $settings['email_logo']) : null,
            'themeColor' => $settings['email_theme_color'] ?? '#ff6c2f',
            'emailTextColor' => $settings['email_text_color'] ?? null,
            'siteName' => $settings['site_name'] ?? config('app.name'),
        ])->render();
    }

    public function subject(EmailTemplate $template, array $data = []): string
    {
        return $this->replaceVariables($template->subject, $data);
    }

    public function sendTest(EmailTemplate $template, string|array $email, array $data = [], string|array $cc = []): void
    {
        $this->sendTemplate($template, $data, $email, $cc);
    }

    public function sendTemplate(EmailTemplate $template, array $data = [], string|array $to = [], string|array $cc = []): void
    {
        $this->applySavedMailConfig();

        $toRecipients = $this->mergeRecipients($template->to_emails ?? [], $to);
        $ccRecipients = $this->mergeRecipients($template->cc_emails ?? [], $cc);

        if ($toRecipients === [] && $ccRecipients !== []) {
            $toRecipients = $ccRecipients;
            $ccRecipients = [];
        }

        if ($toRecipients === []) {
            throw new InvalidArgumentException('No email recipients configured for this template.');
        }

        $mailer = Mail::to($toRecipients);

        if ($ccRecipients !== []) {
            $mailer->cc($ccRecipients);
        }

        $mailer->send(new GenericMail($template, $data));
    }

    public function uploadBuilderImage(mixed $image): array
    {
        $path = $image->storeAs('email/builder', $this->datedOriginalFilename($image), 'public');

        return [
            'path' => $path,
            'url' => asset('storage/' . $path),
        ];
    }

    public function testSmtp(array $config): array
    {
        $this->applyMailConfig($config);

        try {
            $transport = Mail::mailer('smtp')->getSymfonyTransport();
            $transport->start();
            $transport->stop();

            return [
                'type' => 'success',
                'message' => 'SMTP connection successful.',
            ];
        } catch (\Throwable $exception) {
            return [
                'type' => 'danger',
                'message' => 'SMTP connection failed: ' . $exception->getMessage(),
            ];
        }
    }

    public function testSavedSmtp(): array
    {
        $settings = $this->settings();

        return $this->testSmtp([
            'mail_host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail_port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail_username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail_password' => $this->decrypt($settings['mail_password'] ?? null) ?? config('mail.mailers.smtp.password'),
            'mail_encryption' => $settings['mail_encryption'] ?? null,
            'mail_from_address' => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mail_from_name' => $settings['mail_from_name'] ?? config('mail.from.name'),
        ]);
    }

    public function applySavedMailConfig(): void
    {
        $settings = $this->settings();

        $this->applyMailConfig([
            'mail_host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail_port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail_username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail_password' => $this->decrypt($settings['mail_password'] ?? null) ?? config('mail.mailers.smtp.password'),
            'mail_encryption' => $settings['mail_encryption'] ?? null,
            'mail_from_address' => $settings['mail_from_address'] ?? config('mail.from.address'),
            'mail_from_name' => $settings['mail_from_name'] ?? config('mail.from.name'),
        ]);
    }

    public function applyMailConfig(array $mailConfig): void
    {
        $scheme = match ($mailConfig['mail_encryption'] ?? null) {
            'ssl' => 'smtps',
            default => 'smtp',
        };

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.scheme', $scheme);
        Config::set('mail.mailers.smtp.host', $mailConfig['mail_host'] ?? null);
        Config::set('mail.mailers.smtp.port', (int) ($mailConfig['mail_port'] ?? 587));
        Config::set('mail.mailers.smtp.username', $mailConfig['mail_username'] ?? null);
        Config::set('mail.mailers.smtp.password', $mailConfig['mail_password'] ?? null);
        Config::set('mail.from.address', $mailConfig['mail_from_address'] ?: config('mail.from.address'));
        Config::set('mail.from.name', $mailConfig['mail_from_name'] ?: config('mail.from.name'));

        app('mail.manager')->forgetMailers();
    }

    public function replaceVariables(?string $content, array $data): string
    {
        $content = (string) $content;

        return preg_replace_callback('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}|\{\s*([A-Za-z0-9_]+)\s*\}/', function (array $matches) use ($data) {
            $variable = $matches[1] ?: $matches[2];
            $value = $data[$variable] ?? $matches[0];

            if (is_array($value) || is_object($value)) {
                return e(json_encode($value));
            }

            return e((string) $value);
        }, $content) ?? $content;
    }

    protected function decrypt(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    protected function datedOriginalFilename(mixed $file): string
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($name) ?: 'file';

        return $safeName . '-' . now()->format('Y-m-d-His') . ($extension ? '.' . strtolower($extension) : '');
    }

    protected function mergeRecipients(array|string|null ...$groups): array
    {
        return collect($groups)
            ->flatMap(function (array|string|null $group) {
                if (is_string($group)) {
                    return preg_split('/[\r\n,;]+/', $group) ?: [];
                }

                return $group ?: [];
            })
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();
    }
}
