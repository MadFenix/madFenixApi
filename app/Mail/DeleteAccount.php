<?php

namespace App\Mail;

use App\Modules\User\Domain\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeleteAccount extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected User $userToDelete,
        protected string $lastToken,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mad Fénix delete account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.deleteAccount',
            with: [
                'userId' => $this->userToDelete->id,
                'userName' => $this->userToDelete->name,
                'userEmail' => $this->userToDelete->email,
                'lastToken' => $this->lastToken,
            ],
        );
    }
}
