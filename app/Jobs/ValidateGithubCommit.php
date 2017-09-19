<?php

namespace App\Jobs;

use App;
use Activity;
use App\Lib\Github;
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

    protected $payload;

    protected $repositoryType;

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
     * Retrieve repo name
     *
     * @return string
     */
    private function getRepositoryOwnerName()
    {
        return array_get($this->payload, 'repository.owner.name');
    }

    /**
     * Retrieve repo name
     *
     * @return string
     */
    private function getRepositoryName()
    {
        return array_get($this->payload, 'repository.name');
    }

    /**
     * Retrieve repo full name
     *
     * @return string
     */
    private function getRepositoryFullName()
    {
        return array_get($this->payload, 'repository.full_name');
    }

    /**
     * Retrieve commit sha
     *
     * @return string
     */
    private function getSha()
    {
        return array_get($this->payload, 'head_commit.id');
    }

    /**
     * Read package type from composer.json
     *
     * @return string|false
     */
    private function getRepositoryType()
    {
        if (null !== $this->repositoryType) {
            return $this->repositoryType;
        }

        $this->repositoryType = false;

        try {
            $fileInfo = Github::api('repo')->contents()->show(
                $this->getRepositoryOwnerName(),
                $this->getRepositoryName(),
                'composer.json',
                $this->getSha()
            );

            $json = json_decode(base64_decode($fileInfo['content']), true);

            if (!$json) {
                $this->createCommitStatus(self::ERROR, 'Error in composer.json file');
            }

            if (isset($json['type'])) {
                $this->repositoryType = $json['type'];
            }

        } catch (\Exception $e) {
            // composer.json file not found
            return false;
        }

        return $this->repositoryType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->getRepositoryType();
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

            $this->createCommitStatus(
                $status,
                $description,
                $targetUrl
            );

        } catch (\Exception $e) {
            Activity::log('ValidateGithubCommit: Failure. ' . $e->getMessage());
            $this->createCommitStatus(self::FAILURE, 'Internal server error');
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
     * Create commit status at github.com
     *
     * @param  string $state
     * @param  string $description
     * @param  string $targetUrl
     * @return void
     */
    private function createCommitStatus($state, $description, $targetUrl = '')
    {
        Github::api('repo')->statuses()->create(
            $this->getRepositoryOwnerName(),
            $this->getRepositoryName(),
            $this->getSha(),
            [
                'state'       => $state,
                'description' => $description,
                'target_url'  => $targetUrl,
                'context'     => 'continuous-integration/ci.swissuplabs.com'
            ]
        );
    }

    /**
     * Save rendered result into public folder
     *
     * @param  array $text  result of runTests method
     * @return string       Public URL
     */
    private function saveResult($testResults)
    {
        $sha = $this->getSha();
        $repository = $this->getRepositoryFullName();

        $data = [
            'sha'        => $sha,
            'repository' => $repository,
            'results'    => $testResults,
        ];

        $filePath = 'phpcs/' . $repository . '/' . $this->getSha() . '.html';
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
        $repository = $this->getRepositoryFullName();
        $cloneUrl   = array_get($this->payload, 'repository.clone_url');
        $cloneUrl   = str_replace(
            'https://',
            'https://' . config('github.token') . '@',
            $cloneUrl
        );
        $ref = array_get($this->payload, 'ref');

        Storage::disk('local')->makeDirectory($repository);

        // @todo: replace this code with archive download
        // in case of slow git fetch command
        // @see https://developer.github.com/v3/repos/contents/#get-archive-link
        $folder = escapeshellarg(storage_path('app/' . $repository));
        $command = implode(' && ', [
            'cd ' . $folder,
            'git init',
            'git fetch ' . escapeshellarg($cloneUrl) . ' ' . escapeshellarg($ref),
            'git checkout ' . escapeshellarg($this->getSha()),
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
            config('tests.' . $this->getRepositoryType(), [])
        );

        $result = [];
        foreach ($tests as $test) {
            $class = new $test;

            $output = $class
                ->setPath(storage_path('app/' . $this->getRepositoryFullName()))
                ->setRepositoryType($this->getRepositoryType())
                ->run();

            $result[$class->getTitle()] = trim($output);
        }
        return $result;
    }
}
