<?php

namespace App\Tests;

class Test
{
    protected $path;

    protected $repositoryType;

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setRepositoryType($type)
    {
        $this->repositoryType = $type;
    }

    public function getRepositoryType()
    {
        return $this->repositoryType;
    }
}
