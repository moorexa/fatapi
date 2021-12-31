<?php
/**
 * @package SPL Autoloader
 * @author Amadi Ifeanyi <amadiify.com>
 */
spl_autoload_register(function($className){

    // make classname a valid path
    $className = HOME . 'app/' . str_replace('\\', '/', $className) . '.php';

    // path exists? then include
    if (file_exists($className)) include_once $className;
});