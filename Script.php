<?php

const SEARCH_FORM_URL = 'https://search.ipaustralia.gov.au/trademarks/search/advanced';
const SEARCH_SUBMIT_URL = 'https://search.ipaustralia.gov.au/trademarks/search/doSearch';
const RESULTS_FILE = __DIR__ . '/var/results.csv';

/**
 * Class Item to encapsulate the search results item
 *
 * @package Ank\Motorraum\Application
 */
class Item
{
    private string $number = '';
    private string $urlLogo = '';
    private string $name = '';
    private string $class = '';
    private string $status = '';
    private string $urlDetailsPage = '';

    /**
     * Headers to be used in csv or output
     *
     * @var array<string>
     */
    private array $headers = [
        'number',
        'url_logo',
        'name',
        'class',
        'status',
        'url_details_page'
    ];

    /**
     * Item number
     *
     * @param string $number Number
     *
     * @return void
     */
    public function setNumber(string $number): void
    {
        $this->number = $number;
    }

    /**
     * Item URL logo
     *
     * @param string $urlLogo URL logo
     *
     * @return void
     */
    public function setUrlLogo(string $urlLogo): void
    {
        $this->urlLogo = $urlLogo;
    }

    /**
     * Item name
     *
     * @param string $name Name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Item class
     *
     * @param string $class Class
     *
     * @return void
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * Item status
     *
     * @param string $status Status
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * Item URL details
     *
     * @param string $urlDetailsPage URL details page
     *
     * @return void
     */
    public function setUrlDetails(string $urlDetailsPage): void
    {
        $this->urlDetailsPage = $urlDetailsPage;
    }

    /**
     * Headers to be used in csv or output
     *
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Values, composed as array
     *
     * @return array<string>
     */
    public function toArray(): array
    {
        return [
            $this->number,
            $this->urlLogo,
            $this->name,
            $this->class,
            $this->status,
            $this->urlDetailsPage
        ];

    }
}

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
     * @return void
     * @throws Exception
     *
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

/**
 * Class PostBody to encapsulate post request form fields
 *
 * @package Ank\Motorraum\Application\Grabber
 */
class PostBody
{
    private string $searchWord;
    private string $csrfToken;

    /**
     * PostBody constructor.
     *
     * @param string $searchWord The word we search for
     * @param string $csrfToken  CSRF token value
     */
    public function __construct(string $searchWord, string $csrfToken)
    {
        $this->searchWord = $searchWord;
        $this->csrfToken = $csrfToken;
    }

    /**
     * Prepare post fields to be used in request
     *
     * @return string
     */
    public function __toString(): string
    {
        $postFields = [
            "_csrf" => $this->csrfToken,
            "wv[0]" => $this->searchWord,
            "wt[0]" => "PART",
            "weOp[0]" => "AND",
            "wv[1]" => "",
            "wt[1]" => "PART",
            "wrOp" => "AND",
            "wv[2]" => "",
            "wt[2]" => "PART",
            "weOp[1]" => "AND",
            "wv[3]" => "",
            "wt[3]" => "PART",
            "iv[0]" => "",
            "it[0]" => "PART",
            "ieOp[0]" => "AND",
            "iv[1]" => "",
            "it[1]" => "PART",
            "irOp" => "AND",
            "iv[2]" => "",
            "it[2]" => "PART",
            "ieOp[1]" => "AND",
            "iv[3]" => "",
            "it[3]" => "PART",
            "wp" => "",
            "_sw" => "on",
            "classList" => "",
            "ct" => "A",
            "status" => "",
            "dateType" => "LODGEMENT_DATE",
            "fromDate" => "",
            "toDate" => "",
            "ia" => "",
            "gsd" => "",
            "endo" => "",
            "nameField[0]" => "OWNER",
            "name[0]" => "",
            "attorney" => "",
            "oAcn" => "",
            "idList" => "",
            "ir" => "",
            "publicationFromDate" => "",
            "publicationToDate" => "",
            "i" => "",
            "c" => "",
            "originalSegment" => ""
        ];
        $postString = '';
        foreach ($postFields as $name => $value) {
            $postString .= $name . '=' . $value . '&';
        }
        return substr_replace($postString, '', -1);
    }
}

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

/**
 * Class Grabber to find the search results page and grab it with the next pages
 *
 * @package Ank\Motorraum\Application
 */
class Grabber implements GrabberInterface
{
    private string $searchWord;
    private string $searchFormUrl;
    private string $searchSubmitUrl;
    private string $resultsUrl;
    private int $page = 0;

    public ParserInterface $parser;

    /**
     * Grabber constructor.
     *
     * @param string          $searchWord      The word we are searching for
     * @param string          $searchFormUrl   The url of the advanced search form
     * @param string          $searchSubmitUrl The url where POST request is sent
     * @param ParserInterface $parser          Parser instance
     *
     * @throws Exception
     */
    public function __construct(
        string $searchWord,
        string $searchFormUrl,
        string $searchSubmitUrl,
        ParserInterface $parser
    ) {
        if (!extension_loaded('curl')) {
            throw new Exception('This app requires ext-curl');
        }

        $this->searchWord = $searchWord;
        $this->searchFormUrl = $searchFormUrl;
        $this->searchSubmitUrl = $searchSubmitUrl;
        $this->parser = $parser;
        $this->resultsUrl = $this->findResultsUrl();
    }

    /**
     * Grab the next page
     *
     * @return array<Item>
     */
    public function grabNextPage(): array
    {
        $ch = curl_init($this->resultsUrl . '&p=' . $this->page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $this->page++;
        $items = $this->parser->parse($result, $this->resultsUrl);
        return $items;
    }

    /**
     * Find the url with search results
     *
     * @return string
     */
    private function findResultsUrl(): string
    {
        $cookies = $this->getCookies();
        $cookieString = self::makeCookieString($cookies);
        $postBody = new PostBody($this->searchWord, $cookies['XSRF-TOKEN']);
        $ch = curl_init($this->searchSubmitUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_COOKIE, $cookieString);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody->__toString());
        curl_exec($ch);
        $location = curl_getinfo($ch)['redirect_url'];
        if (empty($location)) {
            throw new Exception('Unable to find the results url');
        }
        curl_close($ch);

        return $location;
    }

    /**
     * Cookies given with the response, structured as array
     *
     * @return array<string>
     */
    protected function getCookies(): array
    {
        $ch = curl_init($this->searchFormUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = [];
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        curl_close($ch);
        return $cookies;
    }

    /**
     * Cookies to be used in requrest
     *
     * @param array<string> $cookies Cookies
     *
     * @return string
     */
    protected static function makeCookieString(array $cookies): string
    {
        $cookieString = '';
        foreach ($cookies as $name => $value) {
            $cookieString .= $name . '=' . $value . ';';
        }
        return $cookieString;
    }
}

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

