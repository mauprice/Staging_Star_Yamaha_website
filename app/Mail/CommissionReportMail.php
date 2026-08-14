<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommissionReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $pdfContent,
        private readonly string $pdfFilename,
        private readonly Carbon $start,
        private readonly Carbon $end,
        private readonly ?string $salesman,
        private readonly float  $total,
        private readonly int    $count,
    ) {}

    public function envelope(): Envelope
    {
        $subject = 'Commission Report: '.$this->start->format('d M Y').' – '.$this->end->format('d M Y');

        if ($this->salesman) {
            $subject .= ' ('.$this->salesman.')';
        }

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.commission-report', with: [
            'start'    => $this->start,
            'end'      => $this->end,
            'salesman' => $this->salesman,
            'total'    => $this->total,
            'count'    => $this->count,
        ]);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
