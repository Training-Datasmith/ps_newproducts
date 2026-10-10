<?php

abstract class ModuleTestCase extends PHPUnit_Framework_TestCase
{
    /** @var Ps_NewProducts */
    protected $module;

    protected function setUp()
    {
        $this->resetAll();
        $this->module = new Ps_NewProducts();
    }

    protected function tearDown()
    {
        $this->resetAll();
    }

    protected function resetAll()
    {
        Configuration::reset();
        Tools::reset();
        Hook::reset();
        Product::reset();
        Context::reset();
        Module::resetStatics();
        PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter::reset();
        PrestaShop\PrestaShop\Core\Product\ProductListingPresenter::reset();
    }

    protected function seedConfiguration($nbr = 3, $days = 15)
    {
        Configuration::set('NEW_PRODUCTS_NBR', $nbr);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', $days);
        Configuration::set('PS_LANG_DEFAULT', 1);
    }

    protected function runChild($script, array $env = array(), array $args = array())
    {
        $php = PHP_BINARY;
        $child = dirname(__DIR__) . '/runtime/child.php';
        $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $cmd = escapeshellarg($php)
            . ' -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 '
            . escapeshellarg($child) . ' ' . escapeshellarg($script);
        foreach ($args as $arg) {
            $cmd .= ' ' . escapeshellarg($arg);
        }
        $proc = proc_open($cmd, $descriptorSpec, $pipes, dirname(dirname(__DIR__)), $env);
        $this->assertTrue(is_resource($proc));
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($proc);

        return array('exit' => $exit, 'stdout' => $stdout, 'stderr' => $stderr);
    }

    protected function assertNoPhpDiagnostics($stdout, $stderr)
    {
        $combined = $stdout . "\n" . $stderr;
        $this->assertNotRegExp(
            '/Notice:|Warning:|Deprecated:|Strict Standards:|Parse error:|Fatal error:|Catchable fatal error:|Recoverable fatal error:/',
            $combined
        );
    }

    protected function warmWidgetWithProduct($productId, $countryId = 8)
    {
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Configuration::set('NEW_PRODUCTS_NBR', 8);
        Context::getContext()->country->id = $countryId;
        Product::$returnValue = array(array('id' => $productId));
        Context::getContext()->smarty->fetchResult = 'RENDER-' . $productId;
        $this->module->renderWidget('displayHome', array());
    }
}
