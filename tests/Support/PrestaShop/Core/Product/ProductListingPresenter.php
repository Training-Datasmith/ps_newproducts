<?php

namespace PrestaShop\PrestaShop\Core\Product;

class ProductListingPresenter
{
    public static $constructCount = 0;

    public static $presented = array();

    public static function reset()
    {
        self::$constructCount = 0;
        self::$presented = array();
    }

    public function __construct($a, $b, $c, $d, $e)
    {
        ++self::$constructCount;
    }

    public function present($settings, $product, $language)
    {
        $row = $product;
        $row['presented'] = true;
        self::$presented[] = $row;

        return $row;
    }
}
