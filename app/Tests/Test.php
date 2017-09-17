<?php

namespace App\Tests;

class Test
{
    protected $path;

    protected $repositoryType;

    public function getTitle()
    {
        return static::class;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setRepositoryType($type)
    {
        $this->repositoryType = $type;

        return $this;
    }

    public function getRepositoryType()
    {
        return $this->repositoryType;
    }
}
