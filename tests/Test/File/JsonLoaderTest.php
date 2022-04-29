<?php

namespace my127\Workspace\Tests\Test\File;

use my127\Workspace\File\Exception\CouldNotDecodeJson;
use my127\Workspace\File\FileLoader\FileGetContentsLoader;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Tests\IntegrationTestCase;

class JsonLoaderTest extends IntegrationTestCase
{
    /** @test */
    public function testItLoadsJsonFileAsAnArray(): void
    {
        $this->workspace()->put('test', json_encode(['foobar' => 'barfoo']));
        self::assertEquals(['foobar' => 'barfoo'], $this->load($this->workspace()->path('test')));
    }

    /** @test */
    public function testItThrowsAnExceptionIfTheJsonCannotBeDecoded(): void
    {
        $this->expectException(CouldNotDecodeJson::class);
        $this->workspace()->put('test', ' [ asd');
        self::assertEquals(['foobar' => 'barfoo'], $this->load($this->workspace()->path('test')));
    }

    private function load(string $url): array
    {
        return (new JsonLoader(new FileGetContentsLoader()))->loadArray($url);
    }
}
