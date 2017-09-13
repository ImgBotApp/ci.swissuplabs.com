<?php

namespace App\Jobs;

use Activity;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ValidateGithubCommit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repository = array_get($this->payload, 'repository.full_name');
        $cloneUrl   = array_get($this->payload, 'repository.clone_url');
        $cloneUrl   = str_replace(
            'https://',
            'https://' . config('github.token') . '@',
            $cloneUrl
        );

        Storage::makeDirectory($repository);

        // @todo: replace this code with archive download
        // in case of slow git fetch command
        // @see https://developer.github.com/v3/repos/contents/#get-archive-link
        $command = implode(' && ', [
            'cd ' . escapeshellarg(storage_path('app/' . $repository)),
            'git init',
            'git fetch ' . escapeshellarg($cloneUrl),
            'git checkout ' . escapeshellarg(array_get($this->payload, 'head_commit.id')),
        ]);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            Activity::log(sprintf(
                'ValidateGithubCommit: Failure. Input: %s; Output: %s',
                $command,
                $process->getErrorOutput()
            ));
        } else {
            // run tests
        }

        // update commit status at github.com
    }
}
