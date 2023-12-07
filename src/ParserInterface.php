<?php

namespace Ank\Motorraum;

use Ank\Motorraum\Application\Item;

/**
 * Interface ParserInterface To parse pages with search results
 *
 * @package Ank\Motorraum
 */
interface ParserInterface
{
    /**
     * Parse the page.
     *
     * @param string $page Page contents to be parsed
     * @param string $url  Page location url to be included in results
     *
     * @return array<Item>
     */
    public function parse(string $page, string $url): array;
}
