<?php
/**
 * Set up the environment so that the Framework can be loaded and executed. This file is required in by Core::_init.
 */

use WigeDev\JasperCore\Core;

// FUTURE: Enable the session handler
//require_once('SessionHandler.php');
//session_set_save_handler(new SessionHandler(), true);
session_start();
date_default_timezone_set('UTC');

// If this is being run as a command line application, parse the first argument as a GET request string
if ('cli' == php_sapi_name()) {
    if (isset($argv)) {
        parse_str(implode('&', array_slice($argv, 1)), $_GET);
        $_REQUEST = $_GET;
    }
}
// Set up some shortcuts, makes sure others have been set
if (!defined('DS')) {
    /** Platform appropriate directory seperator, a shortcut for DIRECTORY_SEPARATOR */
    define('DS', DIRECTORY_SEPARATOR);
};
//if (!defined('_SITE_PATH_') || !defined('_CONFIG_PATH_') || !defined('_ROOT_PATH_')) {
//    /** @noinspection PhpUnhandledExceptionInspection */
//    throw new Exception('One or more paths were not defined.');
//}
// This is here for debugging and unit testing reasons - SET IN index.php, NOT HERE!
if (!defined('_ROOT_PATH_')) {
    /** The path to the root of the installation. */
    define('_ROOT_PATH_', __DIR__);
}
if (!defined('_SITE_PATH_')) {
    /** The path to the site files (generally <root>/public) */
    define('_SITE_PATH_', '_ROOT_PATH_' . DS . 'public');
}
if (!defined('_CONFIG_PATH_')) {
    /** The path to the config folder or file (by default <root>/config/config.php) */
    define('_CONFIG_PATH_', _ROOT_PATH_ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
}
// Try to figure out the environment. If you are unit testing, the bootstrapper should set this to "test".
if (!defined('ENVIRONMENT')) {
    if (isset($_SERVER['HTTP_HOST'])) {
        /** Environment - test, cli or production */
        define('ENVIRONMENT', 'production');
    } else {
        /** Environment - test, cli or production */
        define('ENVIRONMENT', 'cli');
    }
}
// If the environment is test or CLI, display errors
if (ENVIRONMENT === 'test' || ENVIRONMENT == 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('xdebug.var_display_max_depth', 5);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);
}
// Register a custom error handler
set_error_handler(
    function ($errno, $errstr, $errfile = null, $errline = null) {
        if (false === Core::isInitialized()) {
            echo "<h1>Error 500</h1><p>Error $errno - $errstr in $errfile on $errline</p>";
        }
        // Process the error based on type
        switch ($errno) {
            case E_USER_ERROR:
            case E_ERROR:
                Core::i()->log->error("$errstr -  on line $errline in file $errfile");
                break;
            case E_USER_WARNING:
            case E_WARNING:
                Core::i()->log->warning("$errstr -  on line $errline in file $errfile");
                break;
            case E_RECOVERABLE_ERROR:
            case E_USER_NOTICE:
            case E_NOTICE:
                Core::i()->log->notice("$errstr -  on line $errline in file $errfile");
                break;
            default:
                echo "Unknown error type: [$errno] $errstr -  on line $errline in file $errfile<br />\n";
                break;
        }
        return true; // Prevent the built in error handler from being called
    }
);