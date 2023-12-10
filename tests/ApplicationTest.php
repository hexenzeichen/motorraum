<?php

namespace Ank\Motorraum;

use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    private Application $application;

    private GrabberInterface $grabberMock;

    private StorageInterface $storageMock;

    protected function setUp(): void
    {
        $this->grabberMock = $this->createMock(GrabberInterface::class);
        $this->storageMock = $this->createMock(StorageInterface::class);
        $this->application = new Application(
            $this->grabberMock,
            $this->storageMock
        );
    }

    public function testApplicationRuns(): void
    {
        $this->grabberMock->expects($this->exactly(1))->method('grabNextPage');
        $this->storageMock->expects($this->exactly(1))->method('read');
        $this->application->run();
    }
}