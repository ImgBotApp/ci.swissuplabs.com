<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\PushRecieved' => [
            'App\Listeners\SaveCommit',
            'App\Listeners\ValidateCommit',
            'App\Listeners\UpdatePackages',
        ],
        'App\Events\PushValidated' => [
            'App\Listeners\UpdateCommitStatus',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
