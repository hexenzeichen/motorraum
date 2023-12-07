<?php

namespace Ank\Motorraum\Application\Grabber;

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
