<?php

namespace my127\Workspace\Tests\Test\Types\Harness\Repository;

use RuntimeException;
use my127\Workspace\File\FileLoader;
use my127\Workspace\Types\Harness\Repository\ArchiveRepository;
use my127\Workspace\Types\Harness\Repository\PackageRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use PHPUnit\Framework\TestCase;

class PackageRepositoryTest extends TestCase
{
    /** @test */
    public function it_throws_exception_when_requesting_an_invalid_package_name()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('invalid');
        $repository = $this->createRepository();
        $repository->get('http://foobar');
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_get_an_unknown_package()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not registered');
        $repository = $this->createRepository();
        $repository->get('foo/bar');
    }

    /** @test */
    public function it_gets_a_package()
    {
        $repository = $this->createRepository();
        $repository->addPackage('foo/bar', '', []);
        $package = $repository->get('foo/bar');
        self::assertEquals('bar', $package->getName());
        self::assertEquals('', $package->getVersion());
    }

    private function createRepository(): PackageRepository
    {
        $repository = new PackageRepository(new FileLoader());
        return $repository;
    }
}
