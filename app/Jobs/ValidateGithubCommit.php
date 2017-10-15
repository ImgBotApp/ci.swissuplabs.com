<?php

namespace App\Jobs;

use App;
use Activity;
use App\PushEvent;
use App\Lib\Terminal;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ValidateGithubCommit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const SUCCESS   = 'success';
    const ERROR     = 'error';
    const FAILURE   = 'failure';

    /**
     * @var PushEvent
     */
    protected $pushEvent;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PushEvent $pushEvent)
    {
        $this->pushEvent = $pushEvent;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Github\Exception\RuntimeException
     * @throws \Exception
     */
    public function handle()
    {
        $type = $this->pushEvent->getRepositoryType();
        if (!in_array($type, $this->getSupportedRepositoryTypes())) {
            return;
        }

        try {
            $status = self::SUCCESS;
            $targetUrl = '';

            $result = $this->downloadSources()->runTests();

            $failedTests = array_filter($result);
            if ($failedTests) {
                $status = self::ERROR;
                $targetUrl = $this->saveResult($result);
            }

            $description = sprintf(
                "%s / %s checks OK",
                count($result) - count($failedTests),
                count($result)
            );

            $this->pushEvent->createCommitStatus(
                $status,
                $description,
                $targetUrl
            );

            // Send notification if previous commit passed all tests only
            if ($status !== self::SUCCESS &&
                $this->pushEvent->getPreviousCommitStatus() === self::SUCCESS) {

                $compareUrl = $this->pushEvent->getCompareUrl();
                $this->pushEvent->createCommitComment(sprintf(
                    "Please verify your [commit](%s) as it didn't pass [some tests](%s)",
                    $compareUrl,
                    $targetUrl
                ));
            }

        } catch (\Github\Exception\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->pushEvent->createCommitStatus(self::FAILURE, 'Internal server error');
            throw $e;
        }
    }

    /**
     * Get array of supported package types
     *
     * @return array
     */
    private function getSupportedRepositoryTypes()
    {
        return array_keys(config('tests'));
    }

    /**
     * Save rendered result into public folder
     *
     * @param  array $text  result of runTests method
     * @return string       Public URL
     */
    private function saveResult($testResults)
    {
        $sha = $this->pushEvent->getSha();
        $repository = $this->pushEvent->getRepositoryFullName();

        $data = [
            'sha'        => $sha,
            'repository' => $repository,
            'results'    => $testResults,
        ];

        $filePath = 'phpcs/' . $repository . '/' . $sha . '.html';
        Storage::disk('public')->put(
            $filePath,
            view('github/phpcs', $data)->render()
        );

        return asset(Storage::disk('public')->url($filePath));
    }

    /**
     * Download module sources into 'storage/app' folder
     *
     * @return $this
     * @throws \Exception
     */
    private function downloadSources()
    {
        $repository = $this->pushEvent->getRepositoryFullName();
        $cloneUrl   = $this->pushEvent->getRepositoryCloneUrl();
        $cloneUrl   = str_replace(
            'https://',
            'https://' . config('github.token') . '@',
            $cloneUrl
        );
        $ref = $this->pushEvent->getRef();

        Storage::disk('local')->makeDirectory($repository);

        // @todo: replace this code with archive download
        // in case of slow git fetch command
        // @see https://developer.github.com/v3/repos/contents/#get-archive-link
        $folder = escapeshellarg(storage_path('app/' . $repository));
        $command = implode(' && ', [
            'cd ' . $folder,
            'git init',
            'git fetch ' . escapeshellarg($cloneUrl) . ' ' . escapeshellarg($ref),
            'git checkout ' . escapeshellarg($this->pushEvent->getSha()),
        ]);

        Terminal::exec($command);

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
        $tests = array_merge(
            config('tests.default', []),
            config('tests.' . $this->pushEvent->getRepositoryType(), [])
        );

        $result = [];
        foreach ($tests as $test) {
            $class = new $test;

            $output = $class
                ->setPath(storage_path('app/' . $this->pushEvent->getRepositoryFullName()))
                ->setRepositoryType($this->pushEvent->getRepositoryType())
                ->run();

            $result[$class->getTitle()] = trim($output);
        }
        return $result;
    }
}
