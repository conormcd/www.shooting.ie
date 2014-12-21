<?php

require_once __DIR__ . '/global.php';

/**
 * A wrapper around the caching code to allow us to deal with more complex
 * caching behaviours than a simple key-value store cache.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class Cache {
    /** The connection to Memcached */
    private static $memcached = null;

    /**
     * Run some function proxied via the cache.
     *
     * @param callable $func      The function to run when the cache is empty 
     *                            or stale.
     * @param string   $key       The cache key where the result should be 
     *                            stored.
     * @param int      $ttl       The number of seconds from now when the item 
     *                            should be removed from the cache.
     *
     * @return mixed              Either the result of the function or null if 
     *                            there's no cached value AND the function 
     *                            fails.
     */
    public static function exec($func, $key, $ttl) {
        // Add some randomness to the TTL, to avoid cache stampedes.
        $ttl_rand_bound = (int)($ttl * 0.05);
        $ttl += mt_rand($ttl_rand_bound * -1, $ttl_rand_bound);

        // The Memcached extension considers anyting over 60*60*24*30 to be a
        // UNIX timestamp, so we have to adjust for that here.
        if ($ttl >= (60*60*24*30)) {
            $ttl = $ttl + time();
        }

        // Connect to memcached if necessary
        if (self::$memcached === null) {
            self::$memcached = new Memcached('www.shooting.ie');
            self::$memcached->addServer('127.0.0.1', 11211);
        }

        // Attempt to fetch from the cache
        $result = self::$memcached->get($key);
        if (self::$memcached->getResultCode() === Memcached::RES_SUCCESS) {
            return $result;
        }

        // Read through and store the value
        $result = call_user_func($func);
        self::$memcached->set($key, $result, $ttl);
        if (self::$memcached->getResultCode() !== Memcached::RES_SUCCESS) {
            throw new Exception("Failed to set a value in the cache for $key");
        }

        return $result;
    }
}

?>
