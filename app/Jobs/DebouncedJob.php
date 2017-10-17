<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DebouncedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Identifier to identify debounced job in storage
     *
     * If not supplied, get_class on a job will be used
     */
    protected $identifier;

    /**
     * The Job that is being debounced, in serialized form.
     *
     * @var string
     */
    protected $debounced;

    /**
     * Amount of time (in seconds) to debounce the Job.
     *
     * @var int
     */
    protected $wait;

    /**
     * @param ShouldQueue $job        Job to debounce
     * @param int         $wait       Seconds to wait
     * @param string      $identifier Job identifier. Used to store last call time.
     */
    public function __construct(ShouldQueue $job, $wait, $identifier = '')
    {
        $this->identifier = $identifier ?: get_class($job);
        $this->debounced = serialize($job);
        $this->wait = $wait;

        if (config('queue.default') !== 'sync') {
            $this->saveCurrentTime();
            $this->delay($this->wait);
        }
    }

    public function handle()
    {
        if (!$this->canHandle()) {
            return;
        }

        cache()->forget($this->getCacheKey());

        dispatch(unserialize($this->debounced));
    }

    /**
     * Check if we can run debounced job
     *
     * @return boolean
     */
    protected function canHandle()
    {
        $now = time();

        $lastCall = cache()->get($this->getCacheKey());

        return !$lastCall || $now >= $lastCall;
    }

    /**
     * Save last time of job invokation
     *
     * @return void
     */
    protected function saveCurrentTime()
    {
        cache()->put(
            $this->getCacheKey(),
            time() + $this->wait,
            $this->wait / 60 + 2 // this is not required. Just in case of fire.
        );
    }

    /**
     * Get cache key to store job call time
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return 'debounced_' . $this->identifier;
    }
}
