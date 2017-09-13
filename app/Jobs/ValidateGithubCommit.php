<?php

namespace App\Jobs;

use App;
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
        try {
            $testErrors = $this->downloadSources()->runTests();
            // update commit status at github.com
        } catch (\Exception $e) {
            Activity::log('ValidateGithubCommit: Failure. ' . $e->getMessage());
        }
    }

    /**
     * Download module sources into 'storage/app' folder
     *
     * @return $this
     * @throws \Exception
     */
    private function downloadSources()
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
        $folder = escapeshellarg(storage_path('app/' . $repository));
        $command = implode(' && ', [
            'cd ' . $folder,
            'git init',
            'git fetch ' . escapeshellarg($cloneUrl),
            'git checkout ' . escapeshellarg(array_get($this->payload, 'head_commit.id')),
        ]);

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf(
                'Input: %s; Output: %s',
                $command,
                $process->getErrorOutput()
            ));
        }

        return $this;
    }

    /**
     * Run tests against downloaded sources
     *
     * @return string       Error messages
     * @throws \Exception
     */
    private function runTests()
    {
        $repository = array_get($this->payload, 'repository.full_name');
        $folder = escapeshellarg(storage_path('app/' . $repository));

        $command = implode(' && ', [
            sprintf(
                "%s/vendor/bin/phpcs --config-set installed_paths %s > /dev/null",
                App::basePath(),
                App::basePath() . '/vendor/magento/marketplace-eqp'
            ),
            sprintf(
                "%s/vendor/bin/phpcs %s --standard=%s --severity=8",
                App::basePath(),
                $folder,
                App::basePath() . '/vendor/magento/marketplace-eqp/MEQP2'
            )
        ]);

        $process = new Process($command);
        $process->run();

        // phpcs uses exit(1) if validation errors where found, so in order
        // to detect if it was really a terminal error - checkout error_output too
        if (!$process->isSuccessful() && $process->getErrorOutput()) {
            throw new \Exception(sprintf(
                'Input: %s; Output: %s',
                $command,
                $process->getErrorOutput()
            ));
        }

        return $process->getOutput();
    }
}
