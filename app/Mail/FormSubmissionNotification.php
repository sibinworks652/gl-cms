<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\FormBuilder\Models\Form;
use Modules\FormBuilder\Models\FormSubmission;

class FormSubmissionNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Form $form,
        public FormSubmission $submission
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Enquiry: ' . $this->form->name
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission'
        );
    }
}
