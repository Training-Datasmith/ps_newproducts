<?php

class ModuleGuardTest extends ModuleTestCase
{
    public function testModuleFileExitsWhenPsVersionIsMissing()
    {
        $result = $this->runChild('module-guard');
        $this->assertSame(0, $result['exit']);
        $this->assertSame('CLASS=0', trim($result['stdout']));
        $this->assertSame('', $result['stderr']);
        $this->assertNotContains('AFTER_INCLUDE', $result['stdout']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
    }
}
