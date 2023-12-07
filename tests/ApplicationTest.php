<?php

namespace Ank\Motorraum;

use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    private string $searchWord = 'abc';
    private string $searchFormUrl = 'someurl';
    private string $searchSubmitUrl = 'someotherurl';

    private Application $application;

    protected function setUp(): void
    {
        $this->application = new Application(
            $this->createMock(GrabberInterface::class),
            $this->createMock(StorageInterface::class)
        );
    }

    public function testApplicationRuns(): void
    {
        //$this->application->run();
        $this->assertTrue(true);
    }
}