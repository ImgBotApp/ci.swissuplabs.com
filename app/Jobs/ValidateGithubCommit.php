<?php

namespace App\Jobs;

use App;
use Activity;
use App\Events\PushValidated;
use App\Push;
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
     * @var Push
     */
    protected $push;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Push $push)
    {
        $this->push = $push;
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
            $status = self::FAILURE;
            $result = [];
            $errors = [];
            $resultUrl = '';

            $this->downloadSources();
            $result = $this->runTests();
            $this->removeSources();

            $errors = array_filter($result);
            $status = $errors ? self::ERROR : self::SUCCESS;
            if ($errors) {
                $resultUrl = $this->saveResult($result);
            }
        } catch (\Github\Exception\RuntimeException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            event(new PushValidated($this->push, [
                'status' => $status,
                'errors' => $errors,
                'result' => $result,
                'resultUrl' => $resultUrl,
            ]));
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
        $sha = $this->push->getSha();
        $repository = $this->push->getRepositoryFullName();

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
            'username'   => $this->push->getRepositoryOwnerName(),
            'repository' => $this->push->getRepositoryName(),
            'ref'        => $this->push->getRef()
        ], $this->push->getRepositoryFullName());

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
        Storage::deleteDirectory($this->push->getRepositoryFullName());

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
                ->setPath(storage_path('app/' . $this->push->getRepositoryFullName()))
                ->setRepositoryType($this->push->getRepositoryType());

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
