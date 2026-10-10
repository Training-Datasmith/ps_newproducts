<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

$script = isset($argv[1]) ? $argv[1] : '';

if ($script === 'module-guard') {
    if (defined('_PS_VERSION_')) {
        echo 'VERSION_DEFINED=1';
        exit(0);
    }
    register_shutdown_function(function () {
        echo 'CLASS=' . (class_exists('Ps_NewProducts', false) ? '1' : '0');
    });
    include dirname(dirname(__DIR__)) . '/ps_newproducts.php';
    echo 'AFTER_INCLUDE';
    exit(0);
}

if ($script === 'directory-guard') {
    $target = isset($argv[2]) ? $argv[2] : '';
    include $target;
    echo 'AFTER_INCLUDE';
    exit(0);
}

if ($script === 'widget-version') {
    if (!defined('_PS_VERSION_')) {
        define('_PS_VERSION_', getenv('CHILD_PS_VERSION'));
    }
    $bulk = getenv('CHILD_BULK') === '1';
    if (!$bulk) {
        define('PS_TEST_ASSEMBLER_SINGLE', true);
    }
    require dirname(__DIR__) . '/Support/PrestaShop/Core/Module/WidgetInterface.php';
    require dirname(__DIR__) . '/Support/PrestaShop/Adapter/Image/ImageRetriever.php';
    require dirname(__DIR__) . '/Support/PrestaShop/Adapter/Product/PriceFormatter.php';
    require dirname(__DIR__) . '/Support/PrestaShop/Adapter/Product/ProductColorsRetriever.php';
    require dirname(__DIR__) . '/Support/PrestaShop/Adapter/Presenter/Product/ProductListingPresenter.php';
    require dirname(__DIR__) . '/Support/PrestaShop/Core/Product/ProductListingPresenter.php';
    require dirname(__DIR__) . '/Support/Stubs.php';
    require dirname(dirname(__DIR__)) . '/ps_newproducts.php';
    Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
    Configuration::set('NEW_PRODUCTS_NBR', 8);
    $module = new Ps_NewProducts();
    Product::$returnValue = array(array('id' => 1), array('id' => 2));
    $vars = $module->getWidgetVariables('displayHome', array());
    $adapter = PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter::$constructCount;
    $core = PrestaShop\PrestaShop\Core\Product\ProductListingPresenter::$constructCount;
    $presenter = $adapter > 0 ? 'adapter' : ($core > 0 ? 'core' : 'none');
    $assemble = $bulk ? 'bulk' : 'single';
    $ids = array();
    foreach ($vars['products'] as $p) {
        $ids[] = (string) $p['id'];
    }
    echo 'PRESENTER=' . $presenter . "\n";
    echo 'ASSEMBLE=' . $assemble . "\n";
    echo 'IDS=' . implode(',', $ids) . "\n";
    echo 'LINK=' . $vars['allNewProductsLink'] . "\n";
    exit(0);
}

fwrite(STDERR, "Unknown child script\n");
exit(1);
