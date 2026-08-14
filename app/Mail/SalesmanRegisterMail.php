<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesmanRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $filePath,
        private readonly string $filename,
        private readonly string $format,
        private readonly Carbon $startDate,
        private readonly Carbon $endDate,
    ) {}

    public function envelope(): Envelope
    {
        $period = $this->startDate->format('d/m/Y').' – '.$this->endDate->format('d/m/Y');

        return new Envelope(
            subject: 'Salesman Register Report: '.$period,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.salesman-register',
            with: [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                'format' => strtoupper($this->format),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->filename)
                ->withMime($this->format === 'pdf' ? 'application/pdf' : 'text/csv'),
        ];
    }
}
