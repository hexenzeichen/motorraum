<?php

namespace Ank\Motorraum\Application;

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    private const RESULTS_PAGE_DUMMY = __DIR__ . '/../../dummies/searchResults.html';
    private const NO_RESULTS_PAGE_DUMMY = __DIR__ . '/../../dummies/noSearchResults.html';

    private Parser $parser;
    private string $noResultsPage;
    private string $resultsPage;

    protected function setUp(): void
    {
        // not quite correct, but this is internal PHP class, so whatever
        $dom = new \DOMDocument();
        // this probably should be moved to the bootstrap
        libxml_use_internal_errors(true);

        $this->parser = new Parser($dom);
        $this->resultsPage = file_get_contents(self::RESULTS_PAGE_DUMMY);
        $this->noResultsPage = file_get_contents(self::NO_RESULTS_PAGE_DUMMY);
    }

    public function testItReturnsEmptyWhenNoResults(): void
    {
        $result = $this->parser->parse($this->noResultsPage);
        $this->assertEmpty($result);
    }

    public function testItReturnsArrayWhenNoResults(): void
    {
        $result = $this->parser->parse($this->noResultsPage);
        $this->assertIsArray($result);
    }

    public function testItReturnsNotEmptyWhenResultsPresent(): void
    {
        $result = $this->parser->parse($this->resultsPage);
        $this->assertNotEmpty($result);
    }

    public function testItReturns40ItemsWhenResultsPresent(): void
    {
        $result = $this->parser->parse($this->resultsPage);
        $this->assertEquals(40, count($result));
    }

    public function testItParsesStatusCorrectly(): void
    {
        $result = $this->parser->parse($this->resultsPage);
        $this->assertEquals('Protected: Registered/protected', $result[37]['status']);
    }

    public function testItParsesClassCorrectly(): void
    {
        $result = $this->parser->parse($this->resultsPage);
        $this->assertEquals('16,38,41,44', $result[37]['class']);
    }

    public function testItParsesNameCorrectly(): void
    {
        $result = $this->parser->parse($this->resultsPage);
        $this->assertEquals('REDIALFASHION CONNECTION THROUGHOUT THE WORLDABCDEFGHJK 1234567890', $result[9]['name']);
    }
}