<?php

namespace Modules\Email\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Email\Models\EmailTemplate;

class TemplateService
{
    public function create(array $data): EmailTemplate
    {
        return EmailTemplate::create($this->payload($data));
    }

    public function update(EmailTemplate $template, array $data): EmailTemplate
    {
        $template->update($this->payload($data, $template));

        return $template->fresh();
    }

    public function payload(array $data, ?EmailTemplate $template = null): array
    {
        return [
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['slug'] ?? $data['name'], $template?->id),
            'subject' => $this->sanitizeText($data['subject']),
            'body' => $this->sanitizeHtml($data['body'] ?? ''),
            'variables' => $this->normalizeVariables($data['variables'] ?? []),
            'use_header' => (bool) ($data['use_header'] ?? false),
            'use_footer' => (bool) ($data['use_footer'] ?? false),
            'use_signature' => (bool) ($data['use_signature'] ?? false),
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    public function normalizeVariables(array|string|null $variables): array
    {
        if (is_string($variables)) {
            $variables = preg_split('/[\r\n,]+/', $variables) ?: [];
        }

        return collect($variables ?: [])
            ->map(fn ($variable) => trim((string) $variable))
            ->filter(fn ($variable) => $variable !== '' && preg_match('/^[A-Za-z0-9_]+$/', $variable))
            ->unique()
            ->values()
            ->all();
    }

    public function dummyData(EmailTemplate $template): array
    {
        $defaults = [
            'name' => 'Alex Morgan',
            'email' => 'alex@example.com',
            'message' => 'This is a preview message from the CMS Builder email system.',
            'invoice_number' => 'INV-10042',
            'date' => now()->format('d M Y'),
            'site_name' => config('app.name'),
            'amount' => '249.00',
            'status' => 'Completed',
            'button_url' => url('/'),
        ];

        foreach ($template->variables ?: [] as $variable) {
            $defaults[$variable] = Arr::get($defaults, $variable, Str::headline($variable) . ' value');
        }

        return $defaults;
    }

    public function sanitizeHtml(?string $html): string
    {
        $html = (string) $html;
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html) ?? '';
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html) ?? $html;
        $html = preg_replace('/\son\w+\s*=\s*"[^"]*"/i', '', $html) ?? $html;
        $html = preg_replace("/\son\w+\s*=\s*'[^']*'/i", '', $html) ?? $html;
        $html = preg_replace('/javascript\s*:/i', '', $html) ?? $html;
        $html = preg_replace('/data:text\/html[^"\']*/i', '', $html) ?? $html;

        return strip_tags($html, $this->allowedTags());
    }

    protected function sanitizeText(string $text): string
    {
        return trim(strip_tags($text));
    }

    protected function allowedTags(): string
    {
        return '<a><b><br><button><div><em><h1><h2><h3><h4><hr><i><img><li><ol><p><span><strong><table><tbody><td><tfoot><th><thead><tr><u><ul>';
    }

    protected function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (EmailTemplate::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
