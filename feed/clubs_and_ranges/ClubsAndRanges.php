<?php

require_once dirname(__DIR__) . '/Feed.php';

/**
 * A wrapper for reading club/range data files.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRanges
extends Feed
{
    /**
     * Initialise with a directory which will contain .json files for each club 
     * or range.
     *
     * @param string $data_dir The path to the directory.
     */
    public function __construct($data_dir) {
        parent::__construct($data_dir);
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
}

?>
