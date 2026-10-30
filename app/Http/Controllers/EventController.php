<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function show(Event $event)
    {
        abort_unless($event->is_active, 404);

        return view('events.show', compact('event'));
    }
}
