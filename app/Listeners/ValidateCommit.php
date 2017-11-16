<?php

namespace App\Listeners;

use App\Events\PushRecieved;
use App\Jobs\ValidateGithubCommit;

class ValidateCommit
{
    /**
     * Handle the event.
     *
     * @param  PushRecieved  $event
     * @return void
     */
    public function handle(PushRecieved $event)
    {
        if ($event->push->isTag() || $event->push->isDeleted()) {
            return;
        }

        ValidateGithubCommit::dispatch($event->push);
    }
}
