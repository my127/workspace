<?php

namespace Test\my127\Workspace\Types;

use my127\Workspace\Tests\IntegrationTestCase;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use my127\Workspace\Types\Harness\Repository\Repository;
use my127\Workspace\Types\Workspace\Creator;

class CreatorTest extends IntegrationTestCase
{
    private function createHarness(string $harnessContent)
    {
        $harnessTar = $this->workspace()->path('harness.tar');

        $tar = new \PharData($harnessTar);
        $tar->addFromString('foo/harness.yml', $harnessContent);
        $targz = $tar->compress(\Phar::GZ);
        unset($tar);
        \PharData::unlinkArchive($harnessTar);

        $path = $targz->getPath();
        unset($targz);

        return $path;
    }

    public function testWorkspaceCreateWithNoHarness()
    {
        $expectedYaml = <<<EOF
workspace('foo'):
  description: 'generated local workspace for foo.'

EOF;

        $repo = $this->createMock(Repository::class);
        $creator = new Creator($repo);
        $creator->create('foo', null, $this->workspace()->path('test'));

        $this->assertEquals($expectedYaml, $this->workspace()->getContents('test/workspace.yml'));
    }

    public function testWorkspaceCreateWithFlatHarness()
    {
        $harnessFile = $this->createHarness(<<<EOF
harness('my127/foo'):
  description: Much foo

EOF
        );

        $package = new Package(['url' => $harnessFile]);

        $repo = $this->createMock(Repository::class);
        $repo->expects($this->once())
            ->method('get')
            ->with($harnessFile)
            ->willReturn($package);

        $expectedYaml = <<<EOF
workspace('foo'):
  description: 'generated local workspace for foo.'
  harnessLayers:
    - {$harnessFile}

EOF;

        try {
            $creator = new Creator($repo);
            $creator->create('foo', $harnessFile, $this->workspace()->path('test'));

            $this->assertEquals($expectedYaml, $this->workspace()->getContents('test/workspace.yml'));
        } finally {
            \PharData::unlinkArchive($harnessFile);
        }
    }

    public function testWorkspaceCreateWithDocBoundaryHarness()
    {
        $harnessFile = $this->createHarness(<<<EOF
---
harness('my127/foo'):
  description: Much foo
---
attributes('app'):
  app: foo
EOF
        );

        $package = new Package(['url' => $harnessFile]);

        $repo = $this->createMock(Repository::class);
        $repo->expects($this->once())
            ->method('get')
            ->with($harnessFile)
            ->willReturn($package);

        $expectedYaml = <<<EOF
workspace('foo'):
  description: 'generated local workspace for foo.'
  harnessLayers:
    - {$harnessFile}

EOF;

        try {
            $creator = new Creator($repo);
            $creator->create('foo', $harnessFile, $this->workspace()->path('test'));

            $this->assertEquals($expectedYaml, $this->workspace()->getContents('test/workspace.yml'));
        } finally {
            \PharData::unlinkArchive($harnessFile);
        }
    }

    public function testWorkspaceCreateWithMultipleHarnessLayers()
    {
        $harnessFile = $this->createHarness(<<<EOF
harness('my127/foo'):
  description: Much foo
  parentLayers:
    - my127/bar:v1.2.3

EOF
        );

        $package = new Package(['url' => $harnessFile]);

        $repo = $this->createMock(Repository::class);
        $repo->expects($this->once())
            ->method('get')
            ->with($harnessFile)
            ->willReturn($package);

        $expectedYaml = <<<EOF
workspace('foo'):
  description: 'generated local workspace for foo.'
  harnessLayers:
    - 'my127/bar:v1.2.3'
    - {$harnessFile}

EOF;

        try {
            $creator = new Creator($repo);
            $creator->create('foo', $harnessFile, $this->workspace()->path('test'));

            $this->assertEquals($expectedYaml, $this->workspace()->getContents('test/workspace.yml'));
        } finally {
            \PharData::unlinkArchive($harnessFile);
        }
    }
}
