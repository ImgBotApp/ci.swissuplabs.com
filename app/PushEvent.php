<?php

namespace App;

use App\Lib\Github;

class PushEvent
{
    protected $payload;

    protected $repositoryType;

    /**
     * @param string
     */
    public function __construct($payload)
    {
        $this->payload = json_decode($payload, true);
    }

    /**
     * Check if event is a tag event
     *
     * @return boolean
     */
    public function isTag()
    {
        return strpos($this->getRef(), 'refs/tags/') === 0;
    }

    /**
     * Create commit status at github.com
     *
     * @param  string $state
     * @param  string $description
     * @param  string $targetUrl
     * @return void
     */
    public function createCommitStatus($state, $description, $targetUrl = '')
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
     * Retrieve repo name
     *
     * @return string
     */
    public function getRepositoryOwnerName()
    {
        return array_get($this->payload, 'repository.owner.name');
    }

    /**
     * Retrieve repo name
     *
     * @return string
     */
    public function getRepositoryName()
    {
        return array_get($this->payload, 'repository.name');
    }

    /**
     * Retrieve repo full name
     *
     * @return string
     */
    public function getRepositoryFullName()
    {
        return array_get($this->payload, 'repository.full_name');
    }

    /**
     * @return string
     */
    public function getRepositoryCloneUrl()
    {
        return array_get($this->payload, 'repository.clone_url');
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return array_get($this->payload, 'ref');
    }

    /**
     * Retrieve commit sha
     *
     * @return string
     */
    public function getSha()
    {
        return array_get($this->payload, 'head_commit.id');
    }

    /**
     * Read package type from composer.json
     *
     * @return string|false
     */
    public function getRepositoryType()
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
}
