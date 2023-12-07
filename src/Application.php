<?php

namespace Ank\Motorraum;

/**
 * Class Application to grab and parse some australian search results
 *
 * @package Ank\Motorraum
 */
class Application
{
    private GrabberInterface $grabber;
    private StorageInterface $storage;

    /**
     * Application constructor.
     *
     * @param GrabberInterface $grabber Grabber to grab pages
     * @param StorageInterface $storage Storage to store results
     */
    public function __construct(
        GrabberInterface $grabber,
        StorageInterface $storage
    ) {
        $this->grabber = $grabber;
        $this->storage = $storage;
    }

    /**
     * Run the application
     *
     * @return void
     */
    public function run(): void
    {
        while ($items = $this->grabber->grabNextPage()) {
            foreach ($items as $item) {
                $this->storage->store($item);
            }
        }
        $this->storage->read();
    }

}
