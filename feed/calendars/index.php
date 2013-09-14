<?php

require_once __DIR__ . '/Calendars.php';

print Calendars::output(dirname(dirname(__DIR__)) . '/data/calendars');

?>
