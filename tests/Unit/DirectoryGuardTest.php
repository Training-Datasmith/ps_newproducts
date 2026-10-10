<?php

class DirectoryGuardTest extends ModuleTestCase
{
    public function testDirectAccessExitsCleanlyForExistingGuards()
    {
        $root = dirname(dirname(__DIR__));
        $guards = array(
            'index.php',
            'tests/index.php',
            'tests/phpstan/index.php',
            'translations/index.php',
            'views/index.php',
            'views/templates/hook/index.php',
            'views/templates/index.php',
        );

        foreach ($guards as $relative) {
            $target = $root . '/' . $relative;
            $result = $this->runChild('directory-guard', array(), array($target));
            $this->assertSame(0, $result['exit'], $relative);
            $this->assertSame('', $result['stdout'], $relative);
            $this->assertSame('', $result['stderr'], $relative);
            $this->assertNotContains('AFTER_INCLUDE', $result['stdout'] . $result['stderr'], $relative);
        }
    }
}
