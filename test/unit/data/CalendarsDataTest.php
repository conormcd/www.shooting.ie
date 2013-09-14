<?php

/**
 * Test all the data files for the Calendars feed.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class CalendarsDataTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Make sure the data directory exists.
     *
     * @return void
     */
    public function testDataDirExists() {
        $this->assertTrue(file_exists($this->dataDir()));
        $this->assertTrue(is_dir($this->dataDir()));
    }

    /**
     * Make sure there's at least one JSON file in the data directory.
     *
     * @return void
     */
    public function testJSONFilesExist() {
        $json_count = 0;
        foreach ($this->dataDirFiles() as $path) {
            if (preg_match('/\.json$/', $path)) {
                $json_count++;
            }
        }
        $this->assertGreaterThan(0, $json_count);
    }

    /**
     * Ensure that all of the files in the data directory are valid JSON.
     *
     * @return void
     */
    public function testValidJSON() {
        foreach ($this->dataDirFiles() as $path) {
            if (preg_match('/\.json$/', $path)) {
                $this->assertTrue(file_exists($path));
                $this->assertTrue(is_readable($path));
                $json = json_decode(file_get_contents($path));
                if ($json === null) {
                    $this->assertEqual(json_last_error(), JSON_ERROR_NONE);
                }
            }
        }
    }

    /**
     * Calculate the path to the data directory.
     *
     * @return string The path to the data directory.
     */
    private function dataDir() {
        return dirname(dirname(dirname(__DIR__))) . '/data/calendars';
    }

    /**
     * Get all the entries in the data directory that are not hidden files.
     *
     * @return array An array of absolute paths to files.
     */
    private function dataDirFiles() {
        $files = array();
        if ($dir = opendir($this->dataDir())) {
            while (($entry = readdir($dir)) !== false) {
                if ($entry[0] !== '.') {
                    $files[] = realpath($this->dataDir() . '/' . $entry);
                }
            }
            closedir($dir);
        }
        return $files;
    }
}

?>
