<?php

namespace Mindigo\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MindigoIdOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $email = '',
        public readonly string $type = 'login' // 'login' | 'forgot_password'
    ) {
        $this->locale(app()->getLocale());
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'forgot_password'
            ? __('Mindigo-auth::app.mail.otp_forgot_password_subject')
            : __('Mindigo-auth::app.mail.otp_login_subject');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'Mindigo-auth::otp',
            with: [
                'otp' => $this->otp,
                'email' => $this->email,
                'type' => $this->type,
            ],
        );
    }
}
