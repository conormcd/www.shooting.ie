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
        $first = Cache::exec('rand', $key, 5);
        $this->assertEquals($first, Cache::exec('rand', $key, 5));
    }
}

?>
