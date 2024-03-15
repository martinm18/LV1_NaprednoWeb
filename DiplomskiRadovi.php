<?php

include "iRadovi.php";
include 'MyPDO.php';
include 'DiplomskiRadoviDBHelper.php';

class DiplomskiRadovi implements iRadovi
{
    private $name;
    private $text;
    private $link;
    private $oib;

    private $diplomskiPdo;

    public function __construct()
    {
        $this->diplomskiPdo = DiplomskiRadoviDBHelper::getInstance(MyPDO::getInstance());
    }

    public function create($name, $text, $link, $oib)
    {
        $this->name = $name;
        $this->text = $text;
        $this->link = $link;
        $this->oib = $oib;
    }

    public function save()
    {
        $this->diplomskiPdo->insert($this->name, $this->text, $this->link, $this->oib);
    }

    public function read()
    {
        return json_encode($this->diplomskiPdo->findAll());
    }

    public function finish() {
        $this->diplomskiPdo->destroy();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link)
    {
        $this->link = $link;
    }

    public function getOib()
    {
        return $this->oib;
    }

    public function setOib($oib)
    {
        $this->oib = $oib;
    }
}