<?php

class WidgetRenderTest extends ModuleTestCase
{
    public function testSkipsLookupWhenDayCountIsFalsy()
    {
        $falsy = array(false, null, '', '0', 0);
        foreach ($falsy as $value) {
            $this->resetAll();
            $this->module = new Ps_NewProducts();
            Configuration::set('PS_NB_DAYS_NEW_PRODUCT', $value);
            Configuration::set('NEW_PRODUCTS_NBR', 8);
            $this->assertFalse($this->module->renderWidget('displayHome', array()));
            $this->assertSame(0, count(Product::$calls));
        }
    }

    public function testCoreFalseReturnsFalseWithoutFetching()
    {
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Product::$returnValue = false;
        $this->assertFalse($this->module->getWidgetVariables('displayHome', array()));
        $this->assertFalse($this->module->renderWidget('displayHome', array()));
    }

    public function testCoreEmptyArrayReturnsFalse()
    {
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Product::$returnValue = array();
        $this->assertFalse($this->module->getWidgetVariables('displayHome', array()));
        Product::$calls = array();
        $this->assertFalse($this->module->renderWidget('displayHome', array()));
        $this->assertSame(1, count(Product::$calls));
    }

    public function testPresentsProductsOnCacheMiss()
    {
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Configuration::set('NEW_PRODUCTS_NBR', 8);
        Context::getContext()->language->id = 4;
        Product::$returnValue = array(array('id' => 10), array('id' => 11));
        $vars = $this->module->getWidgetVariables('displayHome', array());
        $this->assertInternalType('array', $vars);
        $this->assertSame('https://shop.test/new-products', $vars['allNewProductsLink']);
        $this->assertSame(1, count(Product::$calls));
        $this->assertSame(array(4, 0, 8), Product::$calls[0]);
        $this->assertGreaterThan(0, PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter::$constructCount);
        $this->assertCount(2, $vars['products']);
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertNotFalse($rendered);
        $this->assertSame($vars, Context::getContext()->smarty->assigned);
    }
}
