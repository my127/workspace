<?php

namespace my127\Workspace\Types\Harness\Repository\Package;

class Package
{
    private $name;
    private $version;
    private $dist;

    public function __construct(array $dist = [])
    {
        $this->dist = $dist;
    }

    /**
     * @deprecated This is not used and can probably be removed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated This is not used and can probably be removed
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDist(): array
    {
        return $this->dist;
    }
}
