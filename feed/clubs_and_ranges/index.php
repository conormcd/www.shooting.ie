<?php

require_once __DIR__ . '/ClubsAndRanges.php';

$data_dir = dirname(dirname(__DIR__)) . '/data/clubs_and_ranges';

$clubs_and_ranges = new ClubsAndRanges($data_dir);
if (array_key_exists('function_name', $_GET)) {
    $func = preg_replace('/[^A-Za-z0-9_]/', '', $_GET['function_name']);
    print $clubs_and_ranges->jsonp($func);
} else {
    print $clubs_and_ranges->json();
}

?>
