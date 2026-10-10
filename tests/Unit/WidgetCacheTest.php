<?php

class WidgetCacheTest extends ModuleTestCase
{
    private function assertHookRefreshesCache($method)
    {
        $this->warmWidgetWithProduct(1);
        Context::getContext()->smarty->storeCache(
            'module:ps_newproducts/views/templates/hook/ps_newproducts.tpl',
            'other-module|1',
            'classic',
            'OTHER'
        );
        Product::$returnValue = array(array('id' => 2));
        Context::getContext()->smarty->fetchResult = 'RENDER-2';
        $this->module->$method(array('id_product' => 5));
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertSame('RENDER-2', $rendered);
        $this->assertTrue(
            Context::getContext()->smarty->isCachedEntry(
                'module:ps_newproducts/views/templates/hook/ps_newproducts.tpl',
                'other-module|1',
                'classic'
            )
        );
    }

    public function testProductAddHookDropsWarmCache()
    {
        $this->assertHookRefreshesCache('hookActionProductAdd');
    }

    public function testProductUpdateHookDropsWarmCache()
    {
        $this->assertHookRefreshesCache('hookActionProductUpdate');
    }

    public function testProductDeleteHookDropsWarmCache()
    {
        $this->assertHookRefreshesCache('hookActionProductDelete');
    }
}
