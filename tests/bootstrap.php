<?php

/**
 * Maximum level error reporting
 */
error_reporting(E_ALL | E_STRICT);

/**
 * Ensure both the tests and library directories are in the include path
 */
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library'),
    get_include_path(),
    DOCTRINE_INCLUDE_PATH,
)));

/**
 * Register a trivial autoloader
 */
spl_autoload_register(function($class) {
    $filename = str_replace(array("\\", "_"), DIRECTORY_SEPARATOR, $class) . '.php';

    // Allow class autoloading to fail silently.
    @include $filename;

    return class_exists($class, false);
});
