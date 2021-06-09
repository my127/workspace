<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\AggregateRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;
use PHPUnit\Framework\TestCase;

class AggregateRepositoryTest extends TestCase
{
    /** @test */
    public function it_uses_package_repository_for_package_names()
    {
        $archiveRepo = $this->createStub(Repository::class);
        $packageRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($archiveRepo, $packageRepo);
        $got = $sut->get('inviqa/go:v0.7.0');

        $this->assertSame($package1, $got);
    }

    /** @test */
    public function it_uses_archive_repository_for_url_based_package_names()
    {
        $archiveRepo = $this->createStub(Repository::class);
        $packageRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($archiveRepo, $packageRepo);
        $got = $sut->get('https://foo.com/inviqa/go/master.tgz');

        $this->assertInstanceOf(Package::class, $got);
    }
}
