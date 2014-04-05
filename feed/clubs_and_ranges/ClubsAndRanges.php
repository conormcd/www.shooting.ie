<?php

require_once __DIR__ . '/../../lib/global.php';

/**
 * A wrapper for reading club/range data files.
 *
 * @author Conor McDermottroe <conor@mcdermottroe.com>
 */
class ClubsAndRanges
extends Feed
{
    /**
     * Initialise with a directory which will contain .geojson files for each
     * club or range.
     *
     * @param string $data_dir The path to the directory.
     */
    public function __construct($data_dir) {
        parent::__construct($data_dir);
        $this->data = null;
        $this->club = null;
        $this->clubs = null;
    }

    /**
     * Get an associative array where the keys are club names and the values
     * are the full paths to the GeoJSON files containing the data for the
     * clubs.
     *
     * @return array An associative array as specified above.
     */
    public function clubs() {
        if (!$this->clubs) {
            $this->clubs = array();
            foreach ($this->dataFiles('geojson') as $file_path) {
                $club_name = preg_replace('/\.geojson$/', '', basename($file_path));
                $this->clubs[$club_name] = $file_path;
            }
        }
        return $this->clubs;
    }

    /**
     * If a club name is specified in the URL with ?club= then this will return
     * that name iff it's valid. If the name is not a valid name this method
     * will return null.
     *
     * @return string A valid club name as specified in the URL or null if not
     *                present or not valid.
     */
    public function club() {
        if (!$this->club) {
            if (array_key_exists('club', $_GET)) {
                if (in_array($_GET['club'], array_keys($this->clubs()))) {
                    $this->club = $_GET['club'];
                }
            }
        }
        return $this->club;
    }

    /**
     * Get the raw data as encoded in the JSON files.
     *
     * @return array An associative array where the keys are club names and the
     *               values are the bags of data from each of those data files.
     */
    public function data() {
        if ($this->data === null) {
            foreach ($this->clubs() as $club_name => $file_path) {
                if ((!$this->club()) || $this->club() == $club_name) {
                    $contents = json_decode(file_get_contents($file_path), true);
                    $this->data[$club_name] = $contents;
                }
            }
        }
        return $this->data;
    }

    /**
     * Return the feed data in GeoJSON format.
     *
     * @return string The feed data encoded as GeoJSON.
     */
    public function geojson() {
        $data = $this->data();
        foreach (array_keys($data) as $club) {
            $data[$club]['properties']['name'] = $club;
        }
        if (count($data) > 1) {
            $data = array(
                'type' => 'FeatureCollection',
                'features' => array_values($data),
            );
        } else if (count($data) == 1) {
            $data = array_values($data);
            $data = $data[0];
        }
        return json_encode($data);
    }

    /**
     * Return the feed data in GPX format.
     *
     * @return string The feed data in GPX format.
     */
    public function gpx() {
        $kml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<gpx version="1.1" creator="shooting.ie" xmlns="http://www.topografix.com/GPX/1/1">
XML;
        foreach ($this->data() as $club_name => $club) {
            $lat = $club['geometry']['coordinates'][1];
            $long = $club['geometry']['coordinates'][0];
            $kml .= <<<XML
        <wpt lat="$lat" lon="$long">
            <name><![CDATA[$club_name]]></name>
        </wpt>
XML;
        }
        $kml .= <<<XML
</gpx>
XML;
        return $kml;
    }

    /**
     * Return the feed data in KML format.
     *
     * @return string The feed data in KML format.
     */
    public function kml() {
        $kml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
XML;
        foreach ($this->data() as $club_name => $club) {
            $lat = $club['geometry']['coordinates'][1];
            $long = $club['geometry']['coordinates'][0];
            $kml .= <<<XML
        <Placemark>
            <name><![CDATA[$club_name]]></name>
            <Point>
                <coordinates>$long,$lat,0</coordinates>
            </Point>
        </Placemark>
XML;
        }
        $kml .= <<<XML
    </Document>
</kml>
XML;
        return $kml;
    }
}

?>
