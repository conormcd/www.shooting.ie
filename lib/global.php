<?php

// Buffer all output to allow us to manipulate headers easily.
ob_start();

// Shooting.ie is an Irish site so all dates and times should be Irish ones.
date_default_timezone_set('Europe/Dublin');

/**
 * Recursive version of scandir with search capabilities.
 *
 * @param string $root     The directory root to start from.
 * @param string $filename The basename of the file we're searching for.
 *
 * @return string The full path to the target file.
 */
function searchdir($root, $filename) {
    foreach (scandir($root) as $file) {
        if (!($file == '.' || $file == '..')) {
            $path = realpath($root . '/' . $file);
            if ($file === $filename) {
                return $path;
            } else if (is_dir($path)) {
                $result = searchdir($path, $filename);
                if ($result !== null) {
                    return $result;
                }
            }
        }
    }
    return null;
}

/*
 * Add an autoloader which just searches the whole project for the files. The
 * source tree isn't so big that this actually matters.
 */
define('ROOT', realpath(dirname(__DIR__)));
spl_autoload_register(
    function($class) {
        $file = searchdir(ROOT, "$class.php");
        if ($file !== null) {
            include $file;
        }
    }
);

/*
 * Load any config values from the environment.
 */
$CONFIG_LOCATIONS = array(
    '/etc/www.shooting.ie.env',
);
foreach ($CONFIG_LOCATIONS as $env_file) {
    if (file_exists($env_file) && is_readable($env_file)) {
        $env = json_decode(file_get_contents($env_file), true);
        if ($env) {
            foreach ($env as $k => $v) {
                $_ENV[$k] = $v;
            }
        }
    }
}

?>
