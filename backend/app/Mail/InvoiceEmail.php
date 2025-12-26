<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\Invoice;
use Illuminate\Mail\Mailables\Attachment;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, $pdfContent)
    {
        $this->invoice = $invoice;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice ' . $this->invoice->invoice_number . ' from ' . $this->invoice->business->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'businessName' => $this->invoice->business->name,
                'customerName' => $this->invoice->party->name,
                'invoiceNumber' => $this->invoice->invoice_number,
                'invoiceDate' => $this->invoice->date ? \Carbon\Carbon::parse($this->invoice->date)->format('d M, Y') : '',
                'amountDue' => number_format((float) $this->invoice->grand_total, 2),
                'dueDate' => $this->invoice->due_date ? \Carbon\Carbon::parse($this->invoice->due_date)->format('d M, Y') : '',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn() => $this->pdfContent,
                'Invoice-' . $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
