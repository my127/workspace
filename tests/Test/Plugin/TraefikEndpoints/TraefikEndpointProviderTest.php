<?php

namespace my127\Workspace\Tests\Test\Plugin\TraefikEndpoints;

use my127\Workspace\File\FileLoader\TestFileLoader;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Plugin\TraefikEndpoints\TraefikEndpointProvider;
use PHPUnit\Framework\TestCase;

class TraefikEndpointProviderTest extends TestCase
{
    public function testListEndpoints(): void
    {
        $loader = new JsonLoader(new TestFileLoader((string) file_get_contents(__DIR__ . '/fixture/traefik-providers.json')));
        self::assertEquals([
            'https://kafka-ui.projectx-notify',
            'https://kafka.projectx-ecomm',
            'https://mail.my127.site',
            'https://my127.site',
            'https://search-project.my127.site',
            'https://projectx-ecomm.my127.site',
            'https://hub-project.my127.site',
            'https://a1-project.my127.site',
            'https://a2-project.my127.site',
            'https://projectx-example.my127.site',
            'https://zookeeper.projectx-ecomm',
        ], (new TraefikEndpointProvider($loader))->links());
    }
}
