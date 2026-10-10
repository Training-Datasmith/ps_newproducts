<?php

class GetContentValidationTest extends ModuleTestCase
{
    private function submit(array $fields)
    {
        $fields['submitBlockNewProducts'] = '1';
        Tools::setRequest($fields);
    }

    private function assertRejected($output, $nbr, $days)
    {
        $this->assertContains('[error]', $output);
        $this->assertNotContains('[ok]', $output);
        $this->assertContains('FORM:', $output);
        $this->assertSame($nbr, Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame($days, Configuration::get('PS_NB_DAYS_NEW_PRODUCT'));
    }

    public function testEmptyProductStringIsNotSaved()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testMissingProductKeyIsNotSaved()
    {
        $this->seedConfiguration();
        $this->submit(array('PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testZeroProductStringIsNotSaved()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '0', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testNegativeProductCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '-5', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testTrailingGarbageProductCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '3abc', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testFractionalProductCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '2.7', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testNonScalarProductCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => array('8'), 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testOutOfRangeProductCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '4294967296', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testZeroDayCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '8', 'PS_NB_DAYS_NEW_PRODUCT' => '0'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testNegativeDayCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '8', 'PS_NB_DAYS_NEW_PRODUCT' => '-1'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testTrailingGarbageDayCountIsRejected()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '8', 'PS_NB_DAYS_NEW_PRODUCT' => '3abc'));
        $this->assertRejected($this->module->getContent(), 3, 15);
    }

    public function testValidSaveStoresIntsAndConfirms()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '8', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $output = $this->module->getContent();
        $this->assertContains('[ok]', $output);
        $this->assertNotContains('[error]', $output);
        $this->assertContains('FORM:', $output);
        $this->assertSame(8, Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame(20, Configuration::get('PS_NB_DAYS_NEW_PRODUCT'));
    }

    public function testValidSaveAcceptsUpperBound()
    {
        $this->seedConfiguration();
        $this->submit(array('NEW_PRODUCTS_NBR' => '4294967295', 'PS_NB_DAYS_NEW_PRODUCT' => '1000000000'));
        $output = $this->module->getContent();
        $this->assertContains('[ok]', $output);
        $this->assertSame(4294967295, Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame(1000000000, Configuration::get('PS_NB_DAYS_NEW_PRODUCT'));
    }

    public function testNoSubmitLeavesConfigurationUntouched()
    {
        $this->seedConfiguration();
        $output = $this->module->getContent();
        $this->assertNotContains('[error]', $output);
        $this->assertNotContains('[ok]', $output);
        $this->assertContains('FORM:', $output);
        $this->assertSame(3, Configuration::get('NEW_PRODUCTS_NBR'));
        $this->assertSame(15, Configuration::get('PS_NB_DAYS_NEW_PRODUCT'));
    }

    public function testSuccessfulSaveRefreshesWarmCache()
    {
        $this->warmWidgetWithProduct(1);
        $this->seedConfiguration();
        Product::$returnValue = array(array('id' => 2));
        Context::getContext()->smarty->fetchResult = 'RENDER-2';
        $this->submit(array('NEW_PRODUCTS_NBR' => '8', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->module->getContent();
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertSame('RENDER-2', $rendered);
    }

    public function testInvalidSubmitPreservesWarmCache()
    {
        $this->warmWidgetWithProduct(1);
        $this->seedConfiguration();
        Product::$returnValue = array(array('id' => 2));
        Context::getContext()->smarty->fetchResult = 'RENDER-2';
        $this->submit(array('NEW_PRODUCTS_NBR' => '3abc', 'PS_NB_DAYS_NEW_PRODUCT' => '20'));
        $this->module->getContent();
        $rendered = $this->module->renderWidget('displayHome', array());
        $this->assertSame('RENDER-1', $rendered);
    }
}
