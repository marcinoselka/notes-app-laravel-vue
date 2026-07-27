<?php

namespace App\Mail;

use App\Models\Note;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoteCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Note $note) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nowa notatka: {$this->note->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.note-created',
            with: [
                'recipientName' => $this->note->user->name,
                'title' => $this->note->title,
                'excerpt' => str($this->note->content ?? '')->limit(200)->toString(),
            ],
        );
    }
}
