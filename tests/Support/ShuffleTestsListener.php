<?php

class ShuffleTestsListener implements PHPUnit_Framework_TestListener
{
    private static $shuffled = false;

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (self::$shuffled) {
            return;
        }

        $seed = getenv('TEST_SHUFFLE_SEED');
        if ($seed === false || $seed === '') {
            return;
        }

        self::$shuffled = true;
        mt_srand((int) $seed);
        $this->shuffleSuite($suite);
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
    }

    private function shuffleSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $reflection = new ReflectionClass($suite);
        $property = $reflection->getProperty('tests');
        $property->setAccessible(true);
        $tests = $property->getValue($suite);

        for ($i = count($tests) - 1; $i > 0; --$i) {
            $j = mt_rand(0, $i);
            $tmp = $tests[$i];
            $tests[$i] = $tests[$j];
            $tests[$j] = $tmp;
        }

        foreach ($tests as $test) {
            if ($test instanceof PHPUnit_Framework_TestSuite) {
                $this->shuffleSuite($test);
            }
        }

        $property->setValue($suite, $tests);
    }
}
