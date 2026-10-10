<?php

class WidgetVersionTest extends ModuleTestCase
{
    private function parseChildOutput(array $result)
    {
        $this->assertSame(0, $result['exit']);
        $this->assertNoPhpDiagnostics($result['stdout'], $result['stderr']);
        $lines = array();
        foreach (explode("\n", trim($result['stdout'])) as $line) {
            if ($line === '') {
                continue;
            }
            $parts = explode('=', $line, 2);
            $this->assertCount(2, $parts);
            $lines[$parts[0]] = $parts[1];
        }

        return $lines;
    }

    public function testLegacyPresenterOn1744()
    {
        $result = $this->runChild('widget-version', array(
            'CHILD_PS_VERSION' => '1.7.4.4',
            'CHILD_BULK' => '1',
        ));
        $lines = $this->parseChildOutput($result);
        $this->assertSame('core', $lines['PRESENTER']);
        $this->assertSame('bulk', $lines['ASSEMBLE']);
        $this->assertNotSame('', $lines['IDS']);
        $this->assertSame('https://shop.test/new-products', $lines['LINK']);
    }

    public function testAdapterPresenterOn175()
    {
        $result = $this->runChild('widget-version', array(
            'CHILD_PS_VERSION' => '1.7.5',
            'CHILD_BULK' => '1',
        ));
        $lines = $this->parseChildOutput($result);
        $this->assertSame('adapter', $lines['PRESENTER']);
    }

    public function testPerProductAssemblyWhenBulkMethodIsMissing()
    {
        $result = $this->runChild('widget-version', array(
            'CHILD_PS_VERSION' => '1.7.8.0',
            'CHILD_BULK' => '0',
        ));
        $lines = $this->parseChildOutput($result);
        $this->assertSame('adapter', $lines['PRESENTER']);
        $this->assertSame('single', $lines['ASSEMBLE']);
        $this->assertSame('1,2', $lines['IDS']);
    }
}
