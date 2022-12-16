<?php

namespace my127\Workspace\Plugin\TraefikEndpoints;

use my127\Workspace\File\JsonLoader;

class TraefikEndpointProvider
{
    public const TRAEFIK_BASE_URL = 'https://my127.site';

    /**
     * @var JsonLoader
     */
    private $loader;

    public function __construct(JsonLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return string[]
     */
    public function links(): array
    {
        $providers = $this->loader->loadArray(sprintf(
            '%s/api/providers',
            self::TRAEFIK_BASE_URL
        ));

        $links = [];
        foreach ($providers['docker']['frontends'] as $frontend) {
            foreach ($frontend['routes'] as $route) {
                foreach (explode(',', $route['rule']) as $host) {
                    $links[] = sprintf('https://%s', preg_replace('{^Host:(.*)$}', '\1', $host));
                }
            }
        }

        return $links;
    }
}
