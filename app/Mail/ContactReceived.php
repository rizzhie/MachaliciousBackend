<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactReceived extends Mailable
{
    public function __construct(public ContactMessage $contactMessage)
    {
    }

    public function envelope(): Envelope
    {
        // Strip line breaks so a visitor can't inject extra email headers via the subject.
        $subject = trim(preg_replace('/\s+/', ' ', $this->contactMessage->subject));

        return new Envelope(
            // Hitting "Reply" in your inbox answers the visitor directly.
            replyTo: [new Address($this->contactMessage->email)],
            subject: 'New message: '.$subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact-received',
            with: ['adminUrl' => rtrim(config('matcha.frontend_url'), '/').'/admin'],
        );
    }
}
