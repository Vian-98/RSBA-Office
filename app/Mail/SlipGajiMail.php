<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SlipGajiMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $slipData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $slipData)
    {
        $this->slipData = $slipData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Slip Gaji RS Bintang Amin - ' . $this->slipData['periode'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.slip_gaji',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.slip_gaji', ['slipData' => $this->slipData])->output(),
                'Slip_Gaji_' . str_replace(' ', '_', $this->slipData['nama']) . '_' . str_replace(' ', '_', $this->slipData['periode']) . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
