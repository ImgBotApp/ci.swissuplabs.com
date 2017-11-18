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
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param Push $push
     * @param array $data
     */
    public function __construct(Push $push, array $data)
    {
        $this->push = $push;
        $this->data = $data;
    }
}
