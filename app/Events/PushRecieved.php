<?php

namespace App\Events;

use App\Push;
use Illuminate\Queue\SerializesModels;

class PushRecieved
{
    use SerializesModels;

    public $push;

    /**
     * Create a new event instance.
     *
     * @param Push $push
     */
    public function __construct(Push $push)
    {
        $this->push = $push;
    }
}
