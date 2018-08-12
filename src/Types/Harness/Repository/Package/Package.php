<?php

namespace my127\Workspace\Types\Harness\Repository\Package;

class Package
{
    private $name;
    private $version;
    private $dist;

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDist(): array
    {
        return $this->dist;
    }
}
