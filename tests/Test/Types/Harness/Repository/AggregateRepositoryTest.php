<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\AggregateRepository;
use my127\Workspace\Types\Harness\Repository\LocalSyncRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;
use PHPUnit\Framework\TestCase;

class AggregateRepositoryTest extends TestCase
{
    /** @test */
    public function itUsesPackageRepositoryForPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $packageRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('inviqa/go:v0.7.0');

        $this->assertSame($package1, $got);
    }

    /** @test */
    public function itUsesArchiveRepositoryForUrlBasedPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('https://foo.com/inviqa/go/master.tgz');

        $this->assertInstanceOf(Package::class, $got);
    }

    /** @test */
    public function itUsesArchiveRepositoryForFileUriBasedPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('file:///home/inviqa/go/master.tgz');

        $this->assertInstanceOf(Package::class, $got);
    }

    /** @test */
    public function itUsesArchiveRepositoryForFileBasedPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('/home/inviqa/go/master.tgz');

        $this->assertInstanceOf(Package::class, $got);
    }

    /** @test */
    public function itUsesArchiveRepositoryForUriWithoutPathBasedPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createStub(Repository::class);

        $package1 = new Package();
        $archiveRepo->method('get')->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('example:<anything>');

        $this->assertInstanceOf(Package::class, $got);
    }

    /** @test */
    public function itUsesLocalSyncRepositoryForSyncUriBasedPackageNames()
    {
        $packageRepo = $this->createStub(Repository::class);
        $archiveRepo = $this->createStub(Repository::class);
        $localSyncRepo = $this->createMock(LocalSyncRepository::class);

        $package1 = new Package();
        $localSyncRepo
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/home/inviqa/go/master'))
            ->willReturn($package1);

        $sut = new AggregateRepository($packageRepo, $archiveRepo, $localSyncRepo);
        $got = $sut->get('sync:///home/inviqa/go/master');

        $this->assertEquals($package1, $got);
    }
}
