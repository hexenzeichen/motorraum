<?php

namespace Ank\Motorraum;

use Ank\Motorraum\Application\Item;

/**
 * Interface GrabberInterface to grab search pages
 *
 * @package Ank\Motorraum
 */
interface GrabberInterface
{
    /**
     * Grab the content of the next search results page
     *
     * @return array<Item>
     */
    public function grabNextPage(): array;

}
