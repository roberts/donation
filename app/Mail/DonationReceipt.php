<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelPdf\Facades\Pdf;

class DonationReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Donation $donation
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank You for Your Donation to IBE Foundation - Receipt #'.$this->donation->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.donation-receipt',
            with: [
                'donation' => $this->donation,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Generate PDF receipt as attachment using base64
        $pdfContent = Pdf::view('pdf.receipt', ['donation' => $this->donation->load(['school', 'transactions'])])
            ->format('letter')
            ->base64();

        return [
            Attachment::fromData(fn () => base64_decode($pdfContent), 'donation-receipt-'.$this->donation->id.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
