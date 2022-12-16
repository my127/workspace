<?php

namespace my127\Workspace\Tests\Test\Updater;

use my127\Workspace\Updater\Release;
use PHPUnit\Framework\TestCase;

class ReleaseTest extends TestCase
{
    /**
     * @dataProvider versions
     *
     * @test
     */
    public function determinesMoreRecentVersions(string $current, string $target, bool $expected)
    {
        $release = new Release('', $current);
        $this->assertEquals($expected, $release->isMoreRecentThan($target));
    }

    public function versions()
    {
        return [
            ['1.0.0', '0.9.0', true],
            ['1.0.0', '1.1.0', false],
            ['1.0.0-alpha1', '1.0.0-alpha2', false],
            ['1.0.0-alpha2', '1.0.0-alpha1', true],
            ['1.0.0-beta', '1.0.0-beta', false],
            ['foo', 'bar', false],
        ];
    }
}
