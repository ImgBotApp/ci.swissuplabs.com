<?php

namespace App\Events;

use App\PushEvent;
use Illuminate\Queue\SerializesModels;

class PushRecieved
{
    use SerializesModels;

    public $push;

    /**
     * Create a new event instance.
     *
     * @param PushEvent $push
     */
    public function __construct(PushEvent $push)
    {
        $this->push = $push;
    }
}
