<?php

include 'simple_html_dom.php';
include "DiplomskiRadovi.php";

class DataManager
{
    private $url;
    private $data;
    private $htmlParser;
    private $diplomskiRadovi;

    public function __construct($url)
    {
        $this->url = $url;
        $this->htmlParser = new simple_html_dom();
        $this->diplomskiRadovi = new DiplomskiRadovi();
    }

    public function fetchData()
    {
        $data = [];

        for ($i = 2; $i < 6; $i++) {
            $fullUrl = $this->url . $i;
            $curl = curl_init($fullUrl);
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);
            $result = curl_exec($curl);

            array_push($data, $result);
            curl_close($curl);
        }

        $this->parseData($data);
    }

    private function parseData($data)
    {
        foreach ($data as $result) {
            $imagesUrl = [];
            $oibs = [];
            $hrefElements = [];
            $titleElements = [];
            $textElements = [];

            $html = $this->htmlParser->load($result);

            // find images url
            foreach ($html->find('img') as $element) {
                if (strpos($element, "logos") !== false) {
                    array_push($imagesUrl, $element->src);
                }
            }

            // find oibs
            foreach ($imagesUrl as $image) {
                $oibWithImageExtension = explode("logos/", $image);
                array_push($oibs, substr($oibWithImageExtension[1], 0, 11));
            }

            // find link & name
            foreach ($html->find('a') as $element) {
                array_push($hrefElements, $element->href);
                array_push($titleElements, $element->plaintext);
            }

            $filtered = $this->filterHrefElements($hrefElements, $titleElements);
            $hrefElements = $filtered[0];
            $titleElements = $filtered[1];

            // find text
            // save in DB
            for ($i = 0; $i < count($imagesUrl); $i++) {
                array_push($textElements, $this->getText($hrefElements[$i]));
                $this->diplomskiRadovi->create($titleElements[$i], $textElements[$i], $hrefElements[$i], $oibs[$i]);
                $this->diplomskiRadovi->save();
            }
        }
    }

    private function filterHrefElements($hrefElements, $textElements) {
        // remove unused elements
        for ($i = 0; $i <= 26; $i++) {
            unset($hrefElements[$i]);
            unset($textElements[$i]);
        }

        // remove unused elements
        for ($i = 51; $i <= 61; $i++) {
            unset($hrefElements[$i]);
            unset($textElements[$i]);
        }

        // reset indices
        $hrefElements = array_values($hrefElements);
        $textElements = array_values($textElements);

        $hrefFiltered = [];
        $textFiltered = [];

        // filter unused elements
        for ($i = 0; $i < count($hrefElements) / 4; $i++) {
            $hrefFiltered[$i] = $hrefElements[$i * 4];
            $textFiltered[$i] = $textElements[$i * 4];
        }

        return array($hrefFiltered, $textFiltered);
    }

    private function getText($link) {
        $textResult = [];
        $curl = curl_init($link);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $results = curl_exec($curl);
        array_push($textResult, $results);
        curl_close($curl);

        $paragraphs = [];

        foreach ($textResult as $result) {
            $html = $this->htmlParser->load($result);
            foreach ($html->find("div.post-content") as $element) {
                foreach($element->find('p') as $paragraph){
                    $paragraphs[]=strip_tags($paragraph->innertext);
                }
            }
        }

        return implode("\n", $paragraphs);
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getDiplomskiRadovi()
    {
        return $this->diplomskiRadovi;
    }


}
