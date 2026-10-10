<?php

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '1.7.8.0');
}

$testsDir = dirname(__FILE__);
require_once $testsDir . '/Support/PrestaShop/Core/Module/WidgetInterface.php';
require_once $testsDir . '/Support/PrestaShop/Adapter/Image/ImageRetriever.php';
require_once $testsDir . '/Support/PrestaShop/Adapter/Product/PriceFormatter.php';
require_once $testsDir . '/Support/PrestaShop/Adapter/Product/ProductColorsRetriever.php';
require_once $testsDir . '/Support/PrestaShop/Adapter/Presenter/Product/ProductListingPresenter.php';
require_once $testsDir . '/Support/PrestaShop/Core/Product/ProductListingPresenter.php';
require_once $testsDir . '/Support/Stubs.php';
require_once $testsDir . '/Support/ModuleTestCase.php';

require_once dirname($testsDir) . '/ps_newproducts.php';
