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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->canHandle()) {
            return;
        }

        try {
            $status = self::SUCCESS;
            $description = 'Validation Succeeded';
            $targetUrl = '';

            $errors = $this->downloadSources()->runTests();

            if ($errors) {
                $status = self::ERROR;
                $description = 'Validation Failed';
                $targetUrl = $this->saveResult($errors);
            }

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
     * Check if we can test the commit
     *
     * @return boolean
     */
    private function canHandle()
    {
        try {
            $json = Github::api('repo')->contents()->show(
                $this->getRepositoryOwnerName(),
                $this->getRepositoryName(),
                'composer.json',
                $this->getSha()
            );

            $json = json_decode(base64_decode($json['content']), true);

            if (!$json) {
                $this->createCommitStatus(self::ERROR, 'Error in composer.json file');
            }

            if (!$json
                || !isset($json['type'])
                || $json['type'] !== 'magento2-module') {

                return false;
            }

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

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
     * @param  string $text Errors
     * @return string       Public URL
     */
    private function saveResult($text)
    {
        $sha = $this->getSha();
        $repository = $this->getRepositoryFullName();

        $data = [
            'sha'        => $sha,
            'repository' => $repository,
            'text'       => $text,
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

        Storage::disk('local')->makeDirectory($repository);

        // @todo: replace this code with archive download
        // in case of slow git fetch command
        // @see https://developer.github.com/v3/repos/contents/#get-archive-link
        $folder = escapeshellarg(storage_path('app/' . $repository));
        $command = implode(' && ', [
            'cd ' . $folder,
            'git init',
            'git fetch ' . escapeshellarg($cloneUrl),
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
        $repository = $this->getRepositoryFullName();
        $folder = escapeshellarg(storage_path('app/' . $repository));

        $command = implode(' && ', [
            sprintf(
                "%s/vendor/bin/phpcs %s --standard=MEQP2 --severity=10",
                storage_path('app/tools/meqp'),
                $folder
            )
        ]);

        return Terminal::exec($command);
    }
}
