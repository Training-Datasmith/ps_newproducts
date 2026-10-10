<?php

class SuiteManifestTest extends ModuleTestCase
{
    private static $expectedFiles = array(
        'ConfigXmlTest.php',
        'DirectoryGuardTest.php',
        'GetConfigFieldsValuesTest.php',
        'GetContentValidationTest.php',
        'InstallUninstallTest.php',
        'ModuleGuardTest.php',
        'ModuleMetadataTest.php',
        'RenderFormTest.php',
        'SuiteManifestTest.php',
        'TemplateContractTest.php',
        'WidgetCacheTest.php',
        'WidgetRenderTest.php',
        'WidgetVersionTest.php',
    );

    private static $expectedTestCount = 52;

    public function testUnitFileListMatchesManifest()
    {
        $dir = dirname(__FILE__);
        $files = array();
        foreach (glob($dir . '/*Test.php') as $path) {
            $files[] = basename($path);
        }
        sort($files);
        $this->assertSame(self::$expectedFiles, $files);
    }

    public function testExecutableTestCountMatchesManifest()
    {
        $count = 0;
        foreach (self::$expectedFiles as $file) {
            $class = str_replace('.php', '', $file);
            $reflection = new ReflectionClass($class);
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (strpos($method->getName(), 'test') !== 0) {
                    continue;
                }
                if ($method->getDeclaringClass()->getName() !== $class) {
                    continue;
                }
                ++$count;
            }
        }
        $this->assertSame(self::$expectedTestCount, $count);
    }
}
