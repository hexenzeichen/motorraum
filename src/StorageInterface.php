<?php

namespace Ank\Motorraum;

use Ank\Motorraum\Application\Item;

/**
 * Interface StorageInterface to store search results
 *
 * @package Ank\Motorraum
 */
interface StorageInterface
{
    /**
     * Store the item
     *
     * @param Item $item Item to be stored
     *
     * @return void
     */
    public function store(Item $item): void;

    /**
     * Read all items
     *
     * @return void
     */
    public function read(): void;

}
