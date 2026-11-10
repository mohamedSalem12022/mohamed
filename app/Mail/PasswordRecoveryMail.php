<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordRecoveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $newPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'استعادة كلمة المرور — MO Habit Tracker',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-recovery',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
