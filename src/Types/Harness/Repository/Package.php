<?php

namespace my127\Workspace\Types\Harness\Repository;

class Package
{
    private $vendor;
    private $name;
    private $version;
    private $url;
    private $type;

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
