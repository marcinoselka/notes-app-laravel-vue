<?php

namespace App\Events;

use App\Models\Note;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Note $note) {}
}
