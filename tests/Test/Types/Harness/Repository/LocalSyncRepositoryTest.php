<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\LocalSyncRepository;
use my127\Workspace\Types\Harness\Repository\Package\Package;
use PHPUnit\Framework\TestCase;

class LocalSyncRepositoryTest extends TestCase
{
    /** @test */
    public function itCreatesAPackageFromThePackageUrl()
    {
        $sut = new LocalSyncRepository();
        $got = $sut->get('sync://foo/bar');

        $this->assertEquals(new Package(['url' => '/foo/bar/', 'localsync' => true]), $got);
    }

    /* @test * */
    public function itHandlesOnlySyncUrls()
    {
        $sut = new LocalSyncRepository();

        $this->assertTrue($sut->handles('sync://foo/bar'));
        $this->assertFalse($sut->handles('file:///foo/bar'));
    }
}
