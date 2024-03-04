<?php

namespace my127\Workspace\Tests\Test\Types\Harness\Repository;

use my127\Workspace\File\FileLoader;
use my127\Workspace\File\JsonLoader;
use my127\Workspace\Tests\IntegrationTestCase;
use my127\Workspace\Types\Harness\Repository\Exception\CouldNotLoadSource;
use my127\Workspace\Types\Harness\Repository\Exception\UnknownPackage;
use my127\Workspace\Types\Harness\Repository\PackageRepository;

class PackageRepositoryTest extends IntegrationTestCase
{
    /** @test */
    public function itHandlesPackageNames()
    {
        $sut = $this->createRepository();

        $this->assertTrue($sut->handles('test/package:v2.0.0'));
        $this->assertFalse($sut->handles('foobar'));
    }

    /** @test */
    public function itThrowsExceptionWhenRequestingAnInvalidPackageName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('invalid');
        $repository = $this->createRepository();
        $repository->get('http://foobar');
    }

    /** @test */
    public function itThrowsAnExceptionWhenTryingToGetAnUnknownPackage(): void
    {
        $this->expectException(UnknownPackage::class);
        $this->expectExceptionMessage('is not registered');
        $repository = $this->createRepository();
        $repository->get('foo/bar');
    }

    /** @test */
    public function itGetsAPackage(): void
    {
        $repository = $this->createRepository();
        $repository->addPackage('foo/bar', 'v1.0.0', []);

        $package = $repository->get('foo/bar');

        self::assertEquals('bar', $package->getName());
        self::assertEquals('v1.0.0', $package->getVersion());
    }

    /** @test */
    public function itImportsPackageFromSource(): void
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
    public function itThrowsAnExceptionIfSourceCannotBeLoaded(): void
    {
        $this->expectException(CouldNotLoadSource::class);

        $repository = $this->createRepository();
        $repository->addSource($this->workspace()->path('example.json'));

        $repository->get('test/package');
    }

    /** @test */
    public function itThrowsExceptionOnVainlidVersionString(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('version string');

        $repository = $this->createRepository();
        $repository->addPackage('test/package', '1.0.0', []);

        $repository->get('test/package:1.2.3');
    }

    /**
     * @test
     *
     * @dataProvider provideResolvesVersion
     */
    public function itResolvesVersion(string $version, array $availableVersions, string $expectedVersion): void
    {
        $repository = $this->createRepository();
        foreach ($availableVersions as $version) {
            $repository->addPackage('test/package', $version, []);
        }

        $package = $repository->get(sprintf('test/package:%s', $version));
        self::assertEquals($expectedVersion, $package->getVersion());
    }

    /**
     * @return \Generator<mixed>
     */
    public function provideResolvesVersion(): \Generator
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
    public function itThrowsExceptionIfVersionCannotBeResolved(): void
    {
        $this->expectException(\Exception::class);
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
