<?php

namespace Mindigo\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MindigoIdMagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string $link) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Liên kết đăng nhập MindigoID');
    }

    public function content(): Content
    {
        return new Content(
            view: 'Mindigo-auth::magic-link',  
            with: ['link' => $this->link],
        );
    }
}