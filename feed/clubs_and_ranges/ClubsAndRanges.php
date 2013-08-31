<?php

/**
 * A wrapper for reading club/range data files.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRanges {
    /**
     * Initialise with a directory which will contain .json files for each club 
     * or range.
     *
     * @param string $data_dir The path to the directory.
     */
    public function __construct($data_dir) {
        $this->data_dir = $data_dir;
        $this->data = null;
    }

    /**
     * Get the raw data as encoded in the JSON files.
     *
     * @return array An associative array where the keys are club names and the 
     *               values are the bags of data from each of those data files.
     */
    public function data() {
        if ($this->data === null) {
            foreach ($this->dataFiles() as $file_path) {
                $club_name = preg_replace('/\.json$/', '', basename($file_path));
                $contents = json_decode(file_get_contents($file_path), true);
                $this->data[$club_name] = $contents;
            }
        }
        return $this->data;
    }

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
