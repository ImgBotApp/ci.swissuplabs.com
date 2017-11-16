<?php

namespace App;

use App\Lib\Github;

class Push
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
     * Check if event is a delete event
     *
     * @return boolean [description]
     */
    public function isDeleted()
    {
        return array_get($this->payload, 'deleted');
    }

    /**
     * Create commit status at github.com
     *
     * @param  string $state
     * @param  string $description
     * @param  string $targetUrl
     * @return void
     * @throws \Github\Exception\RuntimeException
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
     * Get commit status at github.com for previous commit
     *
     * @return string
     * @throws \Github\Exception\RuntimeException
     */
    public function getPreviousCommitStatus()
    {
        $statuses = Github::api('repo')->statuses()->show(
            $this->getRepositoryOwnerName(),
            $this->getRepositoryName(),
            array_get($this->payload, 'before')
        );

        if (!count($statuses)) {
            return false;
        }

        return $statuses[0]['state'];
    }

    /**
     * Create commit comment at github.com
     *
     * @param  string $comment
     * @return void
     * @throws \Github\Exception\RuntimeException
     */
    public function createCommitComment($comment)
    {
        Github::api('repo')->comments()->create(
            $this->getRepositoryOwnerName(),
            $this->getRepositoryName(),
            $this->getSha(),
            [
                'body' => $comment
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
    public function getRepositoryUrl()
    {
        return array_get($this->payload, 'repository.url');
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
     * Retrieve compare url
     *
     * @return string
     */
    public function getCompareUrl()
    {
        return array_get($this->payload, 'compare');
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
                $this->createCommitStatus('error', 'Error in composer.json file');
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

    public function getTagName()
    {
        if (!$this->isTag()) {
            return null;
        }

        return str_replace('refs/tags/', '', $this->getRef());
    }

    /**
     * Retrieve any parameter from payload data
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getData($key, $default = null)
    {
        return array_get($this->payload, $key, $default);
    }
}
