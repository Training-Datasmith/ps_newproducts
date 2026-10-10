<?php

class TemplateContractTest extends ModuleTestCase
{
    public function testHookTemplateUsesAssignedVariables()
    {
        $path = dirname(dirname(__DIR__)) . '/views/templates/hook/ps_newproducts.tpl';
        $content = file_get_contents($path);
        $this->assertContains('$products', $content);
        $this->assertContains('$allNewProductsLink', $content);
    }
}
