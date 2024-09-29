<?php

namespace App\Mail;

use App\Models\RetourCommande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RetourOrderUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $order_return;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct(RetourCommande $order_return, string $status)
    {
        $this->order_return = $order_return;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mise à jour de la demande de retour',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-return-update',
            with: ['retour' => $this->order_return]
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
