<?php

require_once __DIR__ . '/../../../feed/Feed.php';

/**
 * Test the generic feed builder.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class FeedTest
extends PHPUnit_Framework_TestCase
{
    /**
     * We need some setup to create a dummy feed data directory.
     *
     * @return void
     */
    public function setUp() {
        $this->test_data = array(
            'foo.json' => 'bar',
            'baz.json' => 'quux',
        );

        // Create a random directory - why is this not a PHP built-in?
        while (true) {
            $this->data_dir = sys_get_temp_dir() . '/FeedTest' . md5(rand());
            if (!is_dir($this->data_dir)) {
                mkdir($this->data_dir);
                break;
            }
        }

        // Write the test data into the directory
        foreach ($this->test_data as $filename => $contents) {
            $path = $this->data_dir . '/' . $filename;
            $fp = fopen($path, 'w');
            fwrite($fp, $contents);
            fclose($fp);
        }
    }

    /**
     * Clean up after the tests.
     *
     * @return void
     */
    public function tearDown() {
        foreach (array_keys($this->test_data) as $filename) {
            unlink($this->data_dir . '/' . $filename);
        }
        rmdir($this->data_dir);
    }

    /**
     * Test the output static method with JSON.
     *
     * @return void
     */
    public function testOutputWithJSON() {
        $this->assertEquals(
            json_decode(TestFeed::output($this->data_dir), true),
            $this->test_data
        );
    }

    /**
     * Test the output static method with JSONP.
     *
     * @return void
     */
    public function testOutputWithJSONP() {
        $_GET['function_name'] = 'foo';
        $output = TestFeed::output($this->data_dir);
        $this->assertEquals(
            json_decode(
                preg_replace('/^foo\((.*)\)$/', '$1', $output),
                true
            ),
            $this->test_data
        );
    }

    /**
     * Check that the data method returns the test data we put into it in the 
     * setUp method.
     *
     * @return void
     */
    public function testDataReturnsTestData() {
        $this->assertEquals($this->sampleFeed()->data(), $this->test_data);
    }

    /**
     * Ensure that multiple calls to data() return the same result.
     *
     * @return void
     */
    public function testDataDoesntChange() {
        $feed = $this->sampleFeed();
        $this->assertEquals($feed->data(), $feed->data());
    }

    /**
     * Check that the dataFiles method returns some files.
     *
     * @return void
     */
    public function testDataFilesReturnsFiles() {
        $file_count = 0;
        foreach ($this->sampleFeed()->dataFiles() as $path) {
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
        $this->assertNotNull(json_decode($this->sampleFeed()->json()));
    }

    /**
     * Ensure that the JSON-P method returns wrapped JSON.
     *
     * @return void
     */
    public function testJsonPReturnsWrappedJSON() {
        $feed = $this->sampleFeed();
        $this->assertEquals($feed->jsonp('foo'), 'foo(' . $feed->json() . ')');
    }

    /**
     * Create a test Feed object to operate on.
     *
     * @return Feed A test Feed object.
     */
    private function sampleFeed() {
        return new TestFeed($this->data_dir);
    }
}

/**
 * A dummy implementation of Feed to allow us to test the behaviour of Feed.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class TestFeed
extends Feed
{
    /**
     * An ultra-simple implementation of Feed#data to allow us to test the
     * other behaviours of Feed.
     *
     * @return array The keys are the basenames of the data files, the values
     *               are the contents of those files.
     */
    public function data() {
        $data = array();
        foreach ($this->dataFiles() as $file_path) {
            $data[basename($file_path)] = file_get_contents($file_path);
        }
        return $data;
    }
}

?>
