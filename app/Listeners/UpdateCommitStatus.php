<?php

namespace App\Listeners;

use App\Commit;
use App\Events\PushValidated;

class UpdateCommitStatus
{
    /**
     * Handle the event.
     *
     * @param  PushValidated  $event
     * @return void
     */
    public function handle(PushValidated $event)
    {
        Commit::where('sha', $event->push->getSha())
            ->first()
            ->update([
                'status' => $event->status
            ]);
    }
}
