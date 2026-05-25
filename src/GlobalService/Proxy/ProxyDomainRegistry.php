<?php

namespace my127\Workspace\GlobalService\Proxy;

use Symfony\Component\Yaml\Yaml;

class ProxyDomainRegistry
{
    public const RELATIVE_PATH = '.config/my127/workspace/proxy-domains.yml';

    public function __construct(private string $home)
    {
    }

    public function path(): string
    {
        return $this->home . '/' . self::RELATIVE_PATH;
    }

    public function displayPath(): string
    {
        return '~/' . self::RELATIVE_PATH;
    }

    public function read(): array
    {
        if (!file_exists($this->path())) {
            return [];
        }

        return ProxyDomainConfiguration::extractDomains(Yaml::parseFile($this->path()) ?? []);
    }

    public function write(array $domains): void
    {
        unset($domains['default']);

        $directory = dirname($this->path());
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->path(), Yaml::dump([
            'attributes' => [
                'global' => [
                    'service' => [
                        'proxy' => [
                            'domains' => $domains,
                        ],
                    ],
                ],
            ],
        ], 10, 2));
    }
}
