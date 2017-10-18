<?php

namespace App\Jobs;

use App\PushEvent;
use App\Lib\Github;
use App\Lib\Terminal;
use App\Downloader\Github as GithubDownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateComposerPackages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * @var PushEvent
     */
    protected $pushEvent;

    /**
     * Satis packages repository settings
     *
     * @var array
     */
    protected $settings;

    /**
     * Path to the satis packages repository
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new job instance.
     *
     * @param PushEvent $pushEvent
     * @return void
     */
    public function __construct(PushEvent $pushEvent)
    {
        $this->pushEvent = $pushEvent;
    }

    /**
     * Run satis build task and push the result to the remote repository
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $this->settings = $this->getSatisSettings();
        if (!$this->settings) {
            return;
        }

        $this->path = 'satis/' . $this->settings['folder'];

        $this->download()->build()->push();
    }

    /**
     * Download satis packages repository
     *
     * @return $this
     * @throws \App\Exceptions\TerminalException
     */
    protected function download()
    {
        Storage::deleteDirectory($this->path);
        Storage::makeDirectory($this->path);

        $downloader = new GithubDownloader;
        $downloader->download($this->settings, $this->path);

        return $this;
    }

    /**
     * Run satis build command inside downloaded repository
     *
     * @return $this
     * @throws \App\Exceptions\TerminalException
     */
    protected function build()
    {
        $command = implode(' && ', [
            'cd ' . storage_path("app/{$this->path}"),
            sprintf("%s/satis build satis.json .", storage_path('app/tools/satis/bin'))
        ]);

        Terminal::exec($command);

        return $this;
    }

    /**
     * Create and push new commit to the remote reposotory via GitHub API
     *
     * @return $this
     * @throws \Github\Exception\RuntimeException
     */
    protected function push()
    {
        $owner = $this->getRepositoryOwnerName();
        $name = $this->getRepositoryName();

        // 1. Get Reference to the HEAD
        $reference = Github::api('gitData')->references()
            ->show($owner, $name, 'heads/' . $this->getRef());

        // 2. Grab the commit that HEAD points to
        $headCommit = Github::api('gitData')->commits()
            ->show($owner, $name, $reference['object']['sha']);

        // 3. Create a tree containing updated files
        $newTree = Github::api('gitData')->trees()
            ->create($owner, $name, $this->getTreeData());

        // 4. Create a new commit
        $newCommit = Github::api('gitData')->commits()
            ->create($owner, $name, [
                'message' => 'satis build satis.json .',
                'parents' => [$headCommit['sha']],
                'tree' => $newTree['sha']
            ]);

        // 5. Update HEAD
        Github::api('gitData')->references()
            ->update($owner, $name, 'heads/' . $this->getRef(), [
                'sha' => $newCommit['sha']
            ]);
    }

    /**
     * Prepare tree data for github
     *
     * @return array
     */
    public function getTreeData()
    {
        $treeData = [];

        foreach (Storage::allFiles($this->path, true) as $filePath) {
            $absolutePath = storage_path('app/' . $filePath);
            $treeData['tree'][] = [
                'path' => str_replace($this->path . '/', '', $filePath),
                'mode' => sprintf('%o', fileperms($absolutePath)),
                'type' => 'blob',
                'content' => Storage::get($filePath)
            ];
        }

        return $treeData;
    }

    /**
     * Satis packages repository owner
     *
     * @return string
     */
    protected function getRepositoryOwnerName()
    {
        return $this->settings['username'];
    }

    /**
     * Satis packages repository name
     *
     * @return string
     */
    protected function getRepositoryName()
    {
        return $this->settings['repository'];
    }

    /**
     * Satis packages repository reference
     *
     * @return string
     */
    protected function getRef()
    {
        return $this->settings['ref'];
    }

    /**
     * Get satis repository settings
     *
     * @return mixed
     */
    protected function getSatisSettings()
    {
        $type = $this->pushEvent->getRepositoryType();

        foreach (config('satis') as $key => $values) {
            if (!in_array($type, $values['types'])) {
                continue;
            }

            $values['folder'] = $key;
            return $values;
        }

        return false;
    }
}
