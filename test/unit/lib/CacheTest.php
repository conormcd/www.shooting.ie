<?php

require_once __DIR__ . '/../../../lib/Cache.php';

/**
 * Test the Cache class.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CacheTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Ensure that APC is enabled. If it's not, then the code *should* manage 
     * gracefully but for testing we want to use the production paths through 
     * the code.
     *
     * @return void
     */
    public function testAPCEnabled() {
        $this->assertTrue(function_exists('apc_fetch'));
        $this->assertTrue(function_exists('apc_store'));
    }

    /**
     * Test that APC is caching things properly. Otherwise we can't trust the 
     * results of the tests.
     *
     * @return void
     */
    public function testAPCFunctioning() {
        $key = 'key_test';
        $value = 'value_test';
        apc_store($key, $value, 10);
        $cached = apc_fetch($key, $success);
        $this->assertTrue($success);
        $this->assertEquals($value, $cached);
    }

    /**
     * Test that a call to exec calls the passed-in function.
     *
     * @return void
     */
    public function testExecCallsFunction() {
        $key = 'key1';
        $value = 'value1';
        $result = Cache::exec(
            function () use ($value) {
                return $value;
            },
            $key,
            1,
            1
        );
        $this->assertEquals($value, $result);
    }

    /**
     * Test that exec caches results.
     *
     * @return void
     */
    public function testExecCaches() {
        $key = 'key2';
        $first = Cache::exec('rand', $key, 1, 1);
        $this->assertEquals($first, Cache::exec('rand', $key, 1, 1));
    }

    /**
     * Make sure that exec serves stale content on failure if it can.
     *
     * @return void
     */
    public function testExecServesStaleContentOnFailure() {
        $key = 'key3';
        $first = Cache::exec('rand', $key, 1, 0);
        $this->assertEquals($first, Cache::exec('rand2', $key, 1, 0));
    }
}

?>
