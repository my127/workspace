<?php

namespace my127\Workspace\Tests\Test\Types\Harness\Repository;

use Exception;
use Generator;
use RuntimeException;
use my127\Workspace\File\FileLoader;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Tests\IntegrationTestCase;
use my127\Workspace\Types\Harness\Repository\ArchiveRepository;
use my127\Workspace\Types\Harness\Repository\Exception\CouldNotLoadSource;
use my127\Workspace\Types\Harness\Repository\Exception\UnknownPackage;
use my127\Workspace\Types\Harness\Repository\PackageRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use PHPUnit\Framework\TestCase;

class PackageRepositoryTest extends IntegrationTestCase
{
    /** @test */
    public function it_throws_exception_when_requesting_an_invalid_package_name(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('invalid');
        $repository = $this->createRepository();
        $repository->get('http://foobar');
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_get_an_unknown_package(): void
    {
        $this->expectException(UnknownPackage::class);
        $this->expectExceptionMessage('is not registered');
        $repository = $this->createRepository();
        $repository->get('foo/bar');
    }

    /** @test */
    public function it_gets_a_package(): void
    {
        $repository = $this->createRepository();
        $repository->addPackage('foo/bar', 'v1.0.0', []);

        $package = $repository->get('foo/bar');

        self::assertEquals('bar', $package->getName());
        self::assertEquals('v1.0.0', $package->getVersion());
    }

    /** @test */
    public function it_imports_package_from_source(): void
    {
        $this->workspace()->put('example.json', json_encode([
            'test/package' => [
                '1.0.0' => [],
            ],
        ]));

        $repository = $this->createRepository();
        $repository->addSource($this->workspace()->path('example.json'));
        $package = $repository->get('test/package');

        self::assertEquals('package', $package->getName());
        self::assertEquals('1.0.0', $package->getVersion());
    }

    /** @test */
    public function it_throws_an_exception_if_source_cannot_be_loaded(): void
    {
        $this->expectException(CouldNotLoadSource::class);

        $repository = $this->createRepository();
        $repository->addSource($this->workspace()->path('example.json'));

        $repository->get('test/package');
    }

    /** @test */
    public function it_throws_exception_on_vainlid_version_String(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('version string');

        $repository = $this->createRepository();
        $repository->addPackage('test/package', '1.0.0', []);

        $repository->get('test/package:1.2.3');
    }

    /**
     * @test
     * @dataProvider provideResolvesVersion
     */
    public function it_resolves_version(string $version, array $availableVersions, string $expectedVersion): void
    {
        $repository = $this->createRepository();
        foreach ($availableVersions as $version) {
            $repository->addPackage('test/package', $version, []);
        }

        $package = $repository->get(sprintf('test/package:%s', $version));
        self::assertEquals($expectedVersion, $package->getVersion());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideResolvesVersion(): Generator
    {
        yield 'single explicit version' => [
            'v1.0.0',
            [
                'v1.0.0',
            ],
            'v1.0.0',
        ];

        yield 'choose highest version for major' => [
            'v1',
            [
                'v1.0.0',
                'v1.1.0',
                'v1.1.1',
            ],
            'v1.1.1',
        ];

        yield 'choose highest version for minor' => [
            'v1.1',
            [
                'v1.0.0',
                'v1.1.0',
                'v1.1.1',
                'v1.1.2',
            ],
            'v1.1.2',
        ];
    }

    /**
     * @test
     */
    public function it_throws_exception_if_version_cannot_be_resolved(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Could not resolve');
        $repository = $this->createRepository();
        $repository->addPackage('test/package', 'v1.0.0', []);
        $repository->get('test/package:v2.0.0');
    }

    private function createRepository(): PackageRepository
    {
        $repository = new PackageRepository(new JsonLoader(new FileLoader()));
        return $repository;
    }
}
