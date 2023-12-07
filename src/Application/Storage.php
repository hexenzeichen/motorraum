<?php

namespace Ank\Motorraum\Application;

use Ank\Motorraum\StorageInterface;
use Exception;

/**
 * Class Storage to store results in CSV
 *
 * @package Ank\Motorraum\Application
 */
class Storage implements StorageInterface
{
    private string $path;

    /**
     * File handler
     *
     * @var false|resource
     */
    private $handle;

    private int $stored = 0;

    /**
     * Storage constructor.
     *
     * @param string $path Path to CSV file to use
     *
     * @throws Exception
     *
     * @return void
     */
    public function __construct(string $path)
    {
        $this->path = $path;
        $dir = dirname($path);
        if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception('Unable to create directory to store results');
        }

        if (!file_exists($path) && !touch($path)) {
            throw new Exception('Unable to write to the file to store results');
        }

        $this->handle = fopen($path, 'w');
    }

    /**
     * Store item
     *
     * @param Item $item Item of search results.
     *
     * @return void
     */
    public function store(Item $item): void
    {
        if ($this->stored == 0) {
            fputcsv($this->handle, $item->getHeaders());
        }
        $itemFields = $item->toArray();
        array_walk(
            $itemFields,
            function (&$field) {
                $field = addslashes($field);
            }
        );
        fputcsv($this->handle, $itemFields);
        $this->stored++;
    }

    /**
     * Read the results from csv and show on the screen
     *
     * @return void
     */
    public function read(): void
    {
        fclose($this->handle);
        $this->handle = fopen($this->path, 'r');
        $row = 1;
        echo '[' . PHP_EOL;
        $headers = fgetcsv($this->handle, 0, ",");
        while (($data = fgetcsv($this->handle, 0, ",")) !== false) {
            $num = count($data);
            echo '    ' . $row . '. {' . PHP_EOL;
            $row++;
            for ($c = 0; $c < $num; $c++) {
                echo '        "' .
                    $headers[$c] .
                    '":"' .
                    stripslashes($data[$c]) .
                    '"'
                    . PHP_EOL;
            }
            echo '    },' . PHP_EOL;
        }
        echo ']' . PHP_EOL;
    }

    /**
     * Tear down the object
     */
    public function __destruct()
    {
        fclose($this->handle);
    }

}
