<?php

class ModuleMetadataTest extends ModuleTestCase
{
    public function testConstructorPublishesModuleIdentity()
    {
        $this->assertSame('ps_newproducts', $this->module->name);
        $this->assertSame('front_office_features', $this->module->tab);
        $this->assertSame('PrestaShop', $this->module->author);
        $this->assertSame('1.0.5', $this->module->version);
        $this->assertSame(0, $this->module->need_instance);
        $this->assertTrue($this->module->bootstrap);
        $this->assertSame('1.7.0.0', $this->module->ps_versions_compliancy['min']);
        $this->assertSame('1.7.8.0', $this->module->ps_versions_compliancy['max']);
    }

    public function testImplementsWidgetInterfaceWithPublicMethods()
    {
        $reflection = new ReflectionClass('Ps_NewProducts');
        $this->assertTrue($reflection->implementsInterface('PrestaShop\\PrestaShop\\Core\\Module\\WidgetInterface'));
        $render = $reflection->getMethod('renderWidget');
        $vars = $reflection->getMethod('getWidgetVariables');
        $this->assertTrue($render->isPublic());
        $this->assertTrue($vars->isPublic());
        $this->assertSame(2, $render->getNumberOfParameters());
        $this->assertSame(2, $vars->getNumberOfParameters());
    }

    public function testTemplateFileMatchesHookTemplateOnDisk()
    {
        $reflection = new ReflectionClass($this->module);
        $property = $reflection->getProperty('templateFile');
        $property->setAccessible(true);
        $templateFile = $property->getValue($this->module);
        $this->assertSame('module:ps_newproducts/views/templates/hook/ps_newproducts.tpl', $templateFile);
        $this->assertFileExists(dirname(dirname(__DIR__)) . '/views/templates/hook/ps_newproducts.tpl');
    }
}
