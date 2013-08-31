<?php

require_once __DIR__ . '/../../../feed/clubs_and_ranges/ClubsAndRanges.php';

/**
 * Test the feed builder object for the clubs and ranges.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRangesTest
extends PHPUnit_Framework_TestCase
{
    /**
     * Check that the data method returns some data in roughly the right form.
     *
     * @return void
     */
    public function testDataReturnsSomething() {
        $clubs = new ClubsAndRanges($this->dataDir());
        $data = $clubs->data();
        $this->assertNotEmpty($data);
        foreach ($data as $club_name => $club_data) {
            $this->assertNotEmpty($club_name);
            $this->assertTrue(is_array($club_data));
        }
    }

    /**
     * Ensure that multiple calls to data() return the same result.
     *
     * @return void
     */
    public function testDataDoesntChange() {
        $clubs = new ClubsAndRanges($this->dataDir());
        $first = $clubs->data();
        $second = $clubs->data();
        $this->assertEquals($first, $second);
    }

    /**
     * Check that the dataFiles method returns some files.
     *
     * @return void
     */
    public function testDataFilesReturnsFiles() {
        $clubs = new ClubsAndRanges($this->dataDir());
        $file_count = 0;
        foreach ($clubs->dataFiles() as $path) {
            $this->assertTrue(file_exists($path));
            $this->assertTrue(is_file($path));
            $this->assertTrue(is_readable($path));
            $file_count++;
        }
        $this->assertGreaterThan(0, $file_count);
    }

    /**
     * Ensure that the JSON method returns valid JSON.
     *
     * @return void
     */
    public function testJsonReturnsJSON() {
        $clubs = new ClubsAndRanges($this->dataDir());
        $this->assertNotNull(json_decode($clubs->json()));
    }

    /**
     * Ensure that the JSON-P method returns wrapped JSON.
     *
     * @return void
     */
    public function testJsonPReturnsWrappedJSON() {
        $clubs = new ClubsAndRanges($this->dataDir());
        $this->assertEquals($clubs->jsonp('foo'), 'foo(' . $clubs->json() . ')');
    }

    /**
     * Calculate the path to the data directory.
     *
     * @return string The path to the data directory.
     */
    private function dataDir() {
        return dirname(dirname(dirname(__DIR__))) . '/data/clubs_and_ranges';
    }
}

?>
