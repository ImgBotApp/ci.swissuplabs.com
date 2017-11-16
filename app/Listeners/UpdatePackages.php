<?php

namespace App\Listeners;

use App\Events\PushRecieved;
use App\Jobs\DebouncedJob;
use App\Jobs\UpdateComposerPackages;

class UpdatePackages
{
    /**
     * Handle the event.
     *
     * @param  PushRecieved  $event
     * @return void
     */
    public function handle(PushRecieved $event)
    {
        if (!$event->push->isTag() || $event->push->isDeleted()) {
            return;
        }

        DebouncedJob::dispatch(new UpdateComposerPackages($event->push), 600);
    }
}
