<?php

class GetConfigFieldsValuesTest extends ModuleTestCase
{
    public function testPostedValueOverridesStoredConfiguration()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 8);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        Tools::setRequest(array(
            'NEW_PRODUCTS_NBR' => '4',
            'PS_NB_DAYS_NEW_PRODUCT' => '5',
        ));
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame('4', $values['NEW_PRODUCTS_NBR']);
        $this->assertSame('5', $values['PS_NB_DAYS_NEW_PRODUCT']);
    }

    public function testAbsentValueFallsBackToStoredConfiguration()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 8);
        Configuration::set('PS_NB_DAYS_NEW_PRODUCT', 20);
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame(8, $values['NEW_PRODUCTS_NBR']);
        $this->assertSame(20, $values['PS_NB_DAYS_NEW_PRODUCT']);
    }

    public function testPresentEmptyStringDoesNotFallBack()
    {
        Configuration::set('NEW_PRODUCTS_NBR', 8);
        Tools::setRequest(array('NEW_PRODUCTS_NBR' => ''));
        $values = $this->module->getConfigFieldsValues();
        $this->assertSame('', $values['NEW_PRODUCTS_NBR']);
    }
}
