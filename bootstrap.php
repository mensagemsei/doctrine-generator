<?php

/**
 * Bootstrap 
 * 
 * @author José Carlos <josecarlos@globtec.com.br>
 */

// Define path to application directory
defined('APPLICATION_PATH') 
    || define('APPLICATION_PATH', realpath(__DIR__ . '/../'));

// The auto loader of the composer
require_once APPLICATION_PATH . '/vendor/autoload.php';

// The auto loader to the generator
require_once __DIR__ . '/src/Autoloader.php';

$loader = new Generator\Autoloader();
$loader->register();