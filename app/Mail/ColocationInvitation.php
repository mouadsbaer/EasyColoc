<?php

namespace App\Mail;

use App\Models\Collocation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ColocationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $collocation;
    public $sender;
    public $token;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Collocation $collocation, User $sender, string $token, ?string $customMessage = null)
    {
        $this->collocation = $collocation;
        $this->sender = $sender;
        $this->token = $token;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation à rejoindre la colocation: ' . $this->collocation->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
