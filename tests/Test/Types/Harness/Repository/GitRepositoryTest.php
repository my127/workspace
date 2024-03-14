<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\GithubRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use PHPUnit\Framework\TestCase;

class GitRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAPackageFromThePackageUrl()
    {
        $sut = new GithubRepository();
        $got = $sut->get('github:git@github.com:inviqa/harness-base-php.git:0.4.x');

        $this->assertEquals(new Package(['url' => 'git@github.com:inviqa/harness-base-php.git', 'ref' => '0.4.x', 'git' => true]), $got);
    }

    /* @test * */
    public function itHandlesOnlyGithubSshUrls()
    {
        $sut = new GithubRepository();

        $this->assertTrue($sut->handles('github:git@github.com:inviqa/harness-base-php.git:0.4.x'));
        $this->assertFalse($sut->handles('inviqa/harness-foo:v1.2.3'));
    }
}
