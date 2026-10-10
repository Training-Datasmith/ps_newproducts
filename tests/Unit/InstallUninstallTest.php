<?php

class InstallUninstallTest extends ModuleTestCase
{
    private function registeredHookSet()
    {
        $hooks = Module::$registeredHooks;
        sort($hooks);

        return $hooks;
    }

    public function testInstallSetsDefaultAndRegistersHooks()
    {
        $this->warmWidgetWithProduct(1);
        Product::$returnValue = array(array('id' => 2));
        Context::getContext()->smarty->fetchResult = 'RENDER-2';
        $this->assertTrue($this->module->install());
        $this->assertSame(8, Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame(
            array(
                'actionProductAdd',
                'actionProductDelete',
                'actionProductUpdate',
                'displayHome',
            ),
            $this->registeredHookSet()
        );
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertSame('RENDER-2', $rendered);
    }

    public function testInstallAbortsWhenParentInstallFails()
    {
        Module::$installResult = false;
        $this->assertFalse($this->module->install());
        $this->assertSame(array(), Module::$registeredHooks);
        $this->assertFalse(Configuration::get('NEW_PRODUCTS_NBR'));
    }

    public function testInstallAbortsWhenConfigurationFails()
    {
        Configuration::setUpdateResult(false);
        $this->assertFalse($this->module->install());
        $this->assertSame(array(), Module::$registeredHooks);
    }

    public function testInstallAbortsWhenHookRegistrationFails()
    {
        Module::$registerHookResults['actionProductUpdate'] = false;
        $this->assertFalse($this->module->install());
        $this->assertLessThan(4, count(Module::$registeredHooks));
        $this->assertSame(8, Configuration::get('NEW_PRODUCTS_NBR'));
    }

    public function testUninstallRemovesProductCountKeepsDayCount()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 3);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        $this->warmWidgetWithProduct(1);
        Product::$returnValue = array(array('id' => 2));
        Context::getContext()->smarty->fetchResult = 'RENDER-2';
        $this->assertTrue($this->module->uninstall());
        $this->assertFalse(Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame(20, Configuration::get('PS_NB_DAYS_NEW_PRODUCT'));
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertSame('RENDER-2', $rendered);
    }

    public function testUninstallAbortsWhenParentUninstallFails()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 3);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Module::$uninstallResult = false;
        $this->assertFalse($this->module->uninstall());
        $this->assertSame(3, Configuration::get('NEW_PRODUCTS_NBR'));
    }

    public function testUninstallAbortsWhenDeleteFails()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 3);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Configuration::setDeleteResult(false);
        $this->assertFalse($this->module->uninstall());
        $this->assertSame(3, Configuration::get('NEW_PRODUCTS_NBR'));
    }
}
