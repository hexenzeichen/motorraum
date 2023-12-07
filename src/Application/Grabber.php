<?php

namespace Ank\Motorraum\Application;

use Ank\Motorraum\Application\Grabber\PostBody;
use Ank\Motorraum\GrabberInterface;
use Ank\Motorraum\ParserInterface;
use Exception;

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
