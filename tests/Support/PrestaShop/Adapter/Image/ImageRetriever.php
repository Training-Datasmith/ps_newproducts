<?php

namespace PrestaShop\PrestaShop\Adapter\Image;

class ImageRetriever
{
    public $link;

    public function __construct($link)
    {
        $this->link = $link;
    }
}
