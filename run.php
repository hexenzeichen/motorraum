#! /bin/php
<?php
require __DIR__ . '/vendor/autoload.php';

use Ank\Motorraum\Application;
use Ank\Motorraum\Application\Grabber;
use Ank\Motorraum\Application\Parser;
use Ank\Motorraum\Application\Storage;

const SEARCH_FORM_URL = 'https://search.ipaustralia.gov.au/trademarks/search/advanced';
const SEARCH_SUBMIT_URL = 'https://search.ipaustralia.gov.au/trademarks/search/doSearch';
const RESULTS_FILE = __DIR__ . '/var/results.csv';

try {

    if (!isset($argv[1])) {
        throw new Exception('No search word provided');
    }

    if (!extension_loaded('dom')) {
        throw new Exception('This app requires ext-dom');
    }

    if (function_exists('libxml_use_internal_errors')) {
        // to hide incorrect HTML warnings in stdout
        libxml_use_internal_errors(true);
    }

    $parser = new Parser(new DOMDocument());
    $storage = new Storage(RESULTS_FILE);
    $grabber = new Grabber(
        $argv[1],
        SEARCH_FORM_URL,
        SEARCH_SUBMIT_URL,
        $parser
    );

    $app = new Application(
        $grabber,
        $storage
    );

    $app->run();

} catch (Exception $exception) {
    $message = $exception->getMessage();
    echo $message . PHP_EOL;
}

