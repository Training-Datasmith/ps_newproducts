<?php

class ConfigXmlTest extends ModuleTestCase
{
    public function testXmlIdentityMatchesModuleClass()
    {
        $path = dirname(dirname(__DIR__)) . '/config.xml';
        $this->assertFileExists($path);
        $doc = new DOMDocument();
        $this->assertTrue($doc->load($path));
        $this->assertSame('ps_newproducts', $doc->getElementsByTagName('name')->item(0)->textContent);
        $this->assertSame('1.0.5', $doc->getElementsByTagName('version')->item(0)->textContent);
        $this->assertSame('1.0.5', $this->module->version);
        $this->assertSame('0', $doc->getElementsByTagName('need_instance')->item(0)->textContent);
        $this->assertSame('1', $doc->getElementsByTagName('is_configurable')->item(0)->textContent);
    }
}
