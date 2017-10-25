<?php

namespace App\Jobs;

use App;
use Activity;
use App\PushEvent;
use App\Lib\Terminal;
use App\Downloader\Github as GithubDownloader;
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
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

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
        try {
            $status = self::SUCCESS;
            $targetUrl = '';

            $this->downloadSources();
            $result = $this->runTests();
            $this->removeSources();

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

        $filePath = 'tests/' . $repository . '/' . $sha . '.html';
        Storage::disk('public')->put(
            $filePath,
            view('github/tests', $data)->render()
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
        $downloader = new GithubDownloader;
        $downloader->download([
            'username'   => $this->pushEvent->getRepositoryOwnerName(),
            'repository' => $this->pushEvent->getRepositoryName(),
            'ref'        => $this->pushEvent->getRef()
        ], $this->pushEvent->getRepositoryFullName());

        return $this;
    }

    /**
     * Remove module sources from 'storage/app' folder
     *
     * @return $this
     * @throws \Exception
     */
    private function removeSources()
    {
        Storage::deleteDirectory($this->pushEvent->getRepositoryFullName());

        return $this;
    }

    /**
     * Run tests against downloaded sources
     *
     * @return array        [Test Title => Error string] pairs
     * @throws \Exception
     */
    private function runTests()
    {
        $result = [];

        foreach (config('tests') as $class) {
            $test = (new $class)
                ->setPath(storage_path('app/' . $this->pushEvent->getRepositoryFullName()))
                ->setRepositoryType($this->pushEvent->getRepositoryType());

            if (!$test->canRun()) {
                continue;
            }

            $output = $test->run();
            $output = str_replace(storage_path(), '', $output);
            $result[$test->getTitle()] = trim($output);
        }

        return $result;
    }
}
