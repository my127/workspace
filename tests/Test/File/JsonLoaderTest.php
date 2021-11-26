<?php

namespace my127\Workspace\Tests\Test\File;

use PHPUnit\Framework\TestCase;
use my127\Workspace\File\Exception\CouldNotDecodeJson;
use my127\Workspace\File\Exception\CouldNotLoadFile;
use my127\Workspace\File\FileLoader;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Tests\IntegrationTestCase;

class JsonLoaderTest extends IntegrationTestCase
{
    /** @test */
    public function test_it_loads_json_file_as_an_array(): void
    {
        $this->workspace()->put('test', json_encode(['foobar' => 'barfoo']));
        self::assertEquals(['foobar' => 'barfoo'], $this->load($this->workspace()->path('test')));
    }

    /** @test */
    public function test_it_throws_an_exception_if_the_json_cannot_be_decoded(): void
    {
        $this->expectException(CouldNotDecodeJson::class);
        $this->workspace()->put('test', ' [ asd');
        self::assertEquals(['foobar' => 'barfoo'], $this->load($this->workspace()->path('test')));
    }

    private function load(string $url): array
    {
        return (new JsonLoader(new FileLoader()))->loadArray($url);
    }
}
