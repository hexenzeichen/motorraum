<?php

namespace Ank\Motorraum\Application;

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
