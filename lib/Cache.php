<?php

require_once __DIR__ . '/ErrorHandler.php';

/**
 * A wrapper around the caching code to allow us to deal with more complex
 * caching behaviours than a simple key-value store cache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Cache {
    /**
     * Run some function proxied via the cache.
     *
     * @param callable $func      The function to run when the cache is empty 
     *                            or stale.
     * @param string   $key       The cache key where the result should be 
     *                            stored.
     * @param int      $ttl       The number of seconds from now when the item 
     *                            should be removed from the cache.
     * @param int      $ttl_stale The number of 
     *
     * @return mixed              Either the result of the function or null if 
     *                            there's no cached value AND the function 
     *                            fails.
     */
    public static function exec($func, $key, $ttl, $ttl_stale = null) {
        $now = time();
        $ttl_stale = $ttl_stale !== null ? $ttl_stale : $ttl;
        $result = array(
            'status' => 'init',
            'value' => null
        );

        // Attempt to fetch the value from the cache.
        if (function_exists('apc_fetch')) {
            $result = apc_fetch($key, $success);
            if ($success) {
                if ($now < $result['best_before']) {
                    $result = array(
                        'status' => 'valid',
                        'value' => $result['value']
                    );
                } else {
                    $result = array(
                        'status' => 'stale',
                        'value' => $result['value']
                    );
                }
            } else {
                $result = array(
                    'status' => 'not_in_cache',
                    'value' => null
                );
            }
        }

        // Call the fetcher if needed
        if ($result['status'] !== 'valid') {
            try {
                $value = call_user_func($func);
                if (function_exists('apc_store')) {
                    apc_store(
                        $key,
                        array(
                            'best_before' => $now + $ttl_stale,
                            'value' => $value
                        ),
                        $ttl
                    );
                }
                $result = array(
                    'status' => 'fresh',
                    'value' => $value
                );
            } catch (Exception $e) {
                ErrorHandler::handleException($e, false);
            }
        }

        return $result['value'];
    }
}

?>
