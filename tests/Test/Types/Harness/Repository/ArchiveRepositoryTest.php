<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\ArchiveRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use PHPUnit\Framework\TestCase;

class ArchiveRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAPackageFromThePackageUrl()
    {
        $sut = new ArchiveRepository();
        $got = $sut->get('https://github.com/inviqa/harness-go/archive/master.tar.gz');

        $this->assertEquals(new Package(['url' => 'https://github.com/inviqa/harness-go/archive/master.tar.gz']), $got);
    }
}
