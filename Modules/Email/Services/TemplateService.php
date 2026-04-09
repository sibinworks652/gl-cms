<?php

namespace Modules\Email\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
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
            'to_emails' => $this->normalizeRecipients($data['to_emails'] ?? []),
            'cc_emails' => $this->normalizeRecipients($data['cc_emails'] ?? []),
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

    public function normalizeRecipients(array|string|null $recipients): array
    {
        if (is_string($recipients)) {
            $recipients = preg_split('/[\r\n,;]+/', $recipients) ?: [];
        }

        return collect($recipients ?: [])
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
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

        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if (! $wrapper) {
            return '';
        }

        $this->sanitizeNode($wrapper);

        $output = '';
        foreach ($wrapper->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    protected function sanitizeText(string $text): string
    {
        return trim(strip_tags($text));
    }

    protected function allowedTags(): array
    {
        return ['a', 'b', 'br', 'button', 'div', 'em', 'h1', 'h2', 'h3', 'h4', 'hr', 'i', 'img', 'li', 'ol', 'p', 'span', 'strong', 'table', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr', 'u', 'ul'];
    }

    protected function allowedAttributes(): array
    {
        return [
            'a' => ['href', 'style', 'target', 'rel'],
            'button' => ['style', 'type'],
            'div' => ['style', 'class'],
            'img' => ['src', 'alt', 'style', 'width', 'height'],
            'p' => ['style', 'class'],
            'span' => ['style', 'class'],
            'table' => ['style', 'width', 'cellpadding', 'cellspacing', 'role'],
            'tbody' => ['style'],
            'td' => ['style', 'colspan', 'rowspan', 'align', 'valign'],
            'tfoot' => ['style'],
            'th' => ['style', 'colspan', 'rowspan', 'align', 'valign'],
            'thead' => ['style'],
            'tr' => ['style'],
            'h1' => ['style'],
            'h2' => ['style'],
            'h3' => ['style'],
            'h4' => ['style'],
            'hr' => ['style'],
            'li' => ['style'],
            'ol' => ['style'],
            'ul' => ['style'],
            'b' => ['style'],
            'em' => ['style'],
            'i' => ['style'],
            'strong' => ['style'],
            'u' => ['style'],
        ];
    }

    protected function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (! in_array($child->tagName, $this->allowedTags(), true)) {
                    $this->unwrapNode($child);
                    continue;
                }

                $this->sanitizeAttributes($child);
                $this->sanitizeNode($child);
            }
        }
    }

    protected function sanitizeAttributes(DOMElement $element): void
    {
        $allowed = $this->allowedAttributes()[$element->tagName] ?? [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);
            $value = trim($attribute->nodeValue ?? '');

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if (in_array($name, ['href', 'src'], true) && preg_match('/^(javascript|data):/i', $value)) {
                $element->removeAttribute($attribute->nodeName);
                continue;
            }

            if ($name === 'style') {
                $element->setAttribute('style', $this->sanitizeStyle($value));
            }
        }
    }

    protected function sanitizeStyle(string $style): string
    {
        $allowedProperties = [
            'background',
            'background-color',
            'border',
            'border-radius',
            'border-collapse',
            'color',
            'display',
            'font-size',
            'font-weight',
            'height',
            'line-height',
            'margin',
            'margin-bottom',
            'margin-top',
            'max-height',
            'max-width',
            'padding',
            'padding-bottom',
            'padding-left',
            'padding-right',
            'padding-top',
            'text-align',
            'text-decoration',
            'width',
        ];

        $cleanRules = [];
        foreach (explode(';', $style) as $rule) {
            [$property, $value] = array_pad(explode(':', $rule, 2), 2, null);
            $property = strtolower(trim((string) $property));
            $value = trim((string) $value);

            if ($property === '' || $value === '' || ! in_array($property, $allowedProperties, true)) {
                continue;
            }

            if (preg_match('/expression|javascript:|data:/i', $value)) {
                continue;
            }

            $cleanRules[] = $property . ':' . $value;
        }

        return implode('; ', $cleanRules);
    }

    protected function unwrapNode(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
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
