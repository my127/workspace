<?php

namespace Test\Types\Harness\Repository;

use my127\Workspace\Types\Harness\Repository\AggregateRepository;
use my127\Workspace\Types\Harness\Repository\HandlingRepository;
use PHPUnit\Framework\TestCase;

class AggregateRepositoryTest extends TestCase
{
    /** @test */
    public function itSelectsTheFirstHandlingRepository()
    {
        $repoNo = $this->createMock(HandlingRepository::class);
        $repoNo
            ->method('handles')
            ->willReturn(false);
        $repoNo
            ->expects($this->never())
            ->method('get');

        $repoYes = $this->createMock(HandlingRepository::class);
        $repoYes
            ->method('handles')
            ->willReturn(true);

        $repoYes
            ->expects($this->once())
            ->method('get');

        $sut = new AggregateRepository($repoNo, $repoYes);

        $sut->get('foobar');
    }

    /** @test */
    public function itThrowsAnExceptionWhenNoRepositoryHandles()
    {
        $repoNo = $this->createMock(HandlingRepository::class);
        $repoNo
            ->method('handles')
            ->willReturn(false);

        $sut = new AggregateRepository($repoNo);

        $this->expectException(\Exception::class);

        $sut->get('foobar');
    }
}
