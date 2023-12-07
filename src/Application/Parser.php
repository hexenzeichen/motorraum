<?php

namespace Ank\Motorraum\Application;

use Ank\Motorraum\ParserInterface;
use DOMDocument;
use DOMElement;

/**
 * Class Parser to parse the page content
 *
 * @package Ank\Motorraum\Application
 */
class Parser implements ParserInterface
{
    private DomDocument $dom;

    /**
     * Parser constructor.
     *
     * @param DOMDocument $dom Page's DOM
     */
    public function __construct(DOMDocument $dom)
    {
        $this->dom = $dom;
    }

    /**
     * Parse the page content
     *
     * @param string $page Page content to parse
     * @param string $url  URL of the page to be used in fields
     *
     * @return array<Item>
     */
    public function parse(string $page, string $url): array
    {
        $this->dom->loadHTML($page);
        $resultsTable = $this->dom->getElementById('resultsTable');
        $items = [];
        if (!$resultsTable) {
            return $items;
        }
        $rows = $resultsTable->getElementsByTagName('tr');
        foreach ($rows as $row) {
            $oneItem = new Item();
            $cells = $row->getElementsByTagName('td');
            if ($cells->length) {
                foreach ($cells as $cell) {
                    $class = $cell->getAttribute("class");
                    switch ($class) {
                    case "number":
                        $oneItem->setNumber(self::parseNumber($cell));
                        $oneItem->setUrlDetails(self::parseUrlDetails($cell, $url));
                        break;
                    case "trademark words":
                        $oneItem->setName(self::parseName($cell));
                        break;
                    case "trademark image":
                        $oneItem->setUrlLogo(self::parseUrlLogo($cell));
                        break;
                    case "classes ":
                        $oneItem->setClass(self::parseClass($cell));
                        break;
                    case "status":
                        $oneItem->setStatus(self::parseStatus($cell));
                        break;
                    }
                }
                $items[] = $oneItem;
            }
        }
        return $items;
    }

    /**
     * Parse the status
     *
     * @param DOMElement $cell TD cell
     *
     * @return string
     */
    private static function parseStatus(DOMElement $cell): string
    {
        $italics = $cell->getElementsByTagName('i');
        if ($italics->length) {
            $italics[0]->parentNode->removeChild($italics[0]);
        }
        return trim($cell->nodeValue);
    }

    /**
     * Parse the status
     *
     * @param DOMElement $cell TD cell
     * @param string     $url  Url to be added to the URL
     *
     * @return string
     */
    private static function parseUrlDetails(DOMElement $cell, string $url): string
    {
        $value = $cell->getElementsByTagName('a')[0]->getAttribute('href');
        return parse_url($url, PHP_URL_SCHEME) .
            '://'
            . parse_url($url, PHP_URL_HOST) .
            strtok(trim($value), '?');
    }

    /**
     * Parse the URL logo
     *
     * @param DOMElement $cell TD cell
     *
     * @return string
     */
    private static function parseUrlLogo(\DOMElement $cell): string
    {
        return trim($cell->getElementsByTagName('img')[0]->getAttribute('src'));
    }

    /**
     * Parse the number
     *
     * @param DOMElement $cell TD cell
     *
     * @return string
     */
    private static function parseNumber(\DOMElement $cell): string
    {
        return trim($cell->getElementsByTagName('a')[0]->nodeValue);
    }

    /**
     * Parse the class
     *
     * @param DOMElement $cell TD cell
     *
     * @return string
     */
    private static function parseClass(\DOMElement $cell): string
    {
        return trim(str_replace(PHP_EOL, '', $cell->nodeValue));
    }

    /**
     * Parse the name
     *
     * @param DOMElement $cell TD cell
     *
     * @return string
     */
    private static function parseName(\DOMElement $cell): string
    {
        return trim(str_replace(PHP_EOL, '', $cell->nodeValue));
    }
}
