<?php

namespace App\Events;

use App\Push;
use Illuminate\Queue\SerializesModels;

class PushValidated
{
    use SerializesModels;

    /**
     * @var Push
     */
    public $push;

    /**
     * @var string
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param Push $push
     */
    public function __construct(Push $push, $status)
    {
        $this->push = $push;
        $this->status = $status;
    }
}
