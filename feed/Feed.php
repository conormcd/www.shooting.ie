<?php

require_once __DIR__ . '/../lib/global.php';

/**
 * A superclass and entry point for running a feed of some data.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
abstract class Feed {
    /**
     * Output the contents of a feed.
     *
     * @param string $data_dir Where the feed data is located.
     *
     * @return void
     */
    public static function output($data_dir) {
        $feed_class = get_called_class();
        $feed_obj = new $feed_class($data_dir);
        if (array_key_exists('function_name', $_GET)) {
            $func = preg_replace('/[^A-Za-z0-9_]/', '', $_GET['function_name']);
            return $feed_obj->jsonp($func);
        } else {
            return $feed_obj->json();
        }
    }

    /**
     * Initialise with a directory which will contain .json files which contain 
     * the data to be fed.
     *
     * @param string $data_dir The path to the directory.
     */
    public function __construct($data_dir) {
        $this->data_dir = $data_dir;
    }

    /**
     * Return the data for the feed, ready to be encoded in JSON.
     *
     * @return array A data structure to be JSON encoded and returned.
     */
    public abstract function data();

    /**
     * Get the files which should be read.
     *
     * @return array The full paths to the data files.
     */
    public function dataFiles() {
        $files = array();
        if ($dir = opendir($this->data_dir)) {
            while (($entry = readdir($dir)) !== false) {
                if (preg_match('/\.json$/', $entry)) {
                    $files[] = realpath($this->data_dir . '/' . $entry);
                }
            }
            closedir($dir);
        }
        return $files;
    }

    /**
     * Get the JSON form of the data.
     *
     * @return string The output of getData, JSON encoded.
     */
    public function json() {
        return json_encode($this->data(), JSON_FORCE_OBJECT);
    }

    /**
     * Get the JSON-P form of the data.
     *
     * @param string $function_name The name of the wrapping function.
     *
     * @return string The output of getData, JSON-P encoded.
     */
    public function jsonp($function_name) {
        return "$function_name(" . $this->json() . ")";
    }
}

?>
