<?php

require_once __DIR__ . '/../../lib/ErrorHandler.php';

/**
 * A generic calendar type.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Calendar {
    /**
     * Initialise with a URL/file path pointing to the calendar data source.
     *
     * @param array $calendar_spec The details of the calendar as specified in
     *                             the JSON file referencing the calendar. This
     *                             will be pre-decoded into an associative
     *                             array.
     */
    public function __construct($calendar_spec) {
        $this->name = $calendar_spec['name'];
        $this->url = $calendar_spec['url'];
        $this->priority = $calendar_spec['priority'];
    }

    /**
     * Return the events from the calendar.
     *
     * @return array An array of events. Events are just associative arrays.
     */
    public abstract function events();

    /**
     * Return all the days in a given date range.
     *
     * @param int $start The inclusive start of the date range.
     * @param int $end   The exclusive end of the date range.
     *
     * @return array An array of UNIX epoch timestamps, one for each of the
     *               days in the range.
     */
    public function daysInRange($start, $end) {
        $timestamps = array();
        for ($t = $start; $t < $end; $t += 86400) {
            $timestamps[] = $t;
        }
        return $timestamps;
    }

    /**
     * Handle a generic cached fetch of a resource from somewhere.
     *
     * @param callable $fetcher   The fetching function. This should raise an
     *                            exception if the fetch fails.
     * @param string   $cache_key The key to use to refer to the fetched
     *                            resource in the cache.
     * @param int      $ttl       The time-to-live, in seconds, of the item in
     *                            the cache.
     *
     * @return mixed The result of the fetcher function possibly from cache or
     *               null if the fetch fails.
     */
    public function cachedFetch($fetcher, $cache_key, $ttl) {
        $result = null;
        if (function_exists('apc_fetch')) {
            $result = apc_fetch($cache_key, $success);
            if (!$success) {
                $result = null;
            }
        }
        if ($result === null) {
            try {
                $result = call_user_func($fetcher);
                if (function_exists('apc_store')) {
                    apc_store($cache_key, $result, $ttl);
                }
            } catch (Exception $e) {
                ErrorHandler::handleException($e, false);
                return null;
            }
        }
        return $result;
    }

    /**
     * Fetch a URL and cache the result if possible.
     *
     * @param string $url The URL to fetch.
     *
     * @return array The contents found at the URL.
     */
    public function cachedURLFetch($url) {
        return $this->cachedFetch(
            function () use ($url) {
                return file_get_contents($url);
            },
            'SHOOTING_IE_URL_FETCH_' . md5($url),
            3600
        );
    }

    /**
     * Fetch a JSON URL and cache the result if possible.
     *
     * @param string $url The URL to fetch.
     *
     * @return array The decoded form of the JSON fetched from the URL.
     */
    public function cachedJSONFetch($url) {
        return $this->cachedFetch(
            function () use ($url) {
                $contents = file_get_contents($url);
                $contents = json_decode($contents, true);
                if ($contents === null) {
                    throw new Exception("Invalid JSON at $url");
                }
                return $contents;
            },
            'SHOOTING_IE_JSON_FETCH_' . md5($url),
            3600
        );
    }
}

?>
