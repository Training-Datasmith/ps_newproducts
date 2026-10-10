<?php

class RenderFormTest extends ModuleTestCase
{
    public function testHelperReceivesFieldNamesAndSubmitAction()
    {
        Configuration::set('PS_LANG_DEFAULT', 1);
        $this->module->renderForm();
        $helper = HelperForm::$lastInstance;
        $this->assertNotNull($helper);
        $this->assertSame('submitBlockNewProducts', $helper->submit_action);
        $names = array();
        foreach ($helper->lastForms[0]['form']['input'] as $input) {
            $names[] = $input['name'];
        }
        sort($names);
        $this->assertSame(array('NEW_PRODUCTS_NBR', 'PS_NB_DAYS_NEW_PRODUCT'), $names);
    }

    public function testCurrentIndexIncludesConfigureParameter()
    {
        Configuration::set('PS_LANG_DEFAULT', 1);
        $this->module->renderForm();
        $helper = HelperForm::$lastInstance;
        $this->assertContains('configure=ps_newproducts', $helper->currentIndex);
        $this->assertContains('module_name=ps_newproducts', $helper->currentIndex);
        $this->assertSame('token-AdminModules', $helper->token);
    }
}
