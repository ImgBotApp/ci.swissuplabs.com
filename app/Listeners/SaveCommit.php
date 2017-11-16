<?php

namespace App\Listeners;

use App\EventRepository;
use App\Events\PushRecieved;

class SaveCommit
{
    /**
     * Handle the event.
     *
     * @param  PushRecieved  $event
     * @return void
     */
    public function handle(PushRecieved $event)
    {
        if ($event->push->isDeleted()) {
            return;
        }

        EventRepository::add($event->push);
    }
}
