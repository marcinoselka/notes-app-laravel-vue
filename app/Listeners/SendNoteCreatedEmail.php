<?php

namespace App\Listeners;

use App\Events\NoteCreated;
use App\Mail\NoteCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendNoteCreatedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(NoteCreated $event): void
    {
        Mail::to($event->note->user)->send(new NoteCreatedMail($event->note));
    }
}
