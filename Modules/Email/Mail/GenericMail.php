<?php

namespace Modules\Email\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Email\Models\EmailTemplate;
use Modules\Email\Services\EmailService;
use Modules\Email\Services\TemplateService;

class GenericMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $renderedHtml;

    public string $renderedSubject;

    public function __construct(
        public EmailTemplate $template,
        public array $data = []
    ) {
        $service = app(EmailService::class);

        if ($this->data === []) {
            $this->data = app(TemplateService::class)->dummyData($template);
        }

        $this->renderedHtml = $service->render($template, $this->data);
        $this->renderedSubject = $service->subject($template, $this->data);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->renderedSubject
        );
    }

    public function send($mailer)
    {
        app(EmailService::class)->applySavedMailConfig();

        return parent::send($mailer);
    }

    public function content(): Content
    {
        return new Content(
            view: 'email::emails.generic',
            with: [
                'html' => $this->renderedHtml,
            ]
        );
    }
}
