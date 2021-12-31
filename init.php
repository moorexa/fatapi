<?php

use Lightroom\Core\FrameworkAutoloader;

/**
 * @package Application base file
 * This file would contain some basic settings to boot the Moorexa framework
 */

// check for global config
if (file_exists('.global.config')) include_once '.global.config';

// define application root
if (!defined('APPLICATION_ROOT')) define('APPLICATION_ROOT', './');

// define base path for framework system files
define('FRAMEWORK_BASE_PATH', APPLICATION_ROOT . 'system');

//register global core
define('GLOBAL_CORE', (isset($GLOBALCORE) ? $GLOBALCORE : FRAMEWORK_BASE_PATH));

// define distribution base path.
// this folder contains configuration directory, components, database, extensions, public, services and much more
// it's advisable you change it, including the framework base path after obtaining a copy of moorexa.
// We are doing this just to add an extra layer of security, so you stay unique and invisible.
define('DISTRIBUTION_BASE_PATH',  APPLICATION_ROOT . 'src');

// add source path
define('SOURCE_BASE_PATH', (isset($SOURCE_BASE_PATH) ? $SOURCE_BASE_PATH : DISTRIBUTION_BASE_PATH));

// composer path
define('COMPOSER', APPLICATION_ROOT . 'vendor/autoload.php');

// require framework autoloader
require_once  GLOBAL_CORE . '/Core/FrameworkAutoloader.php';

// The default light room namespace for our application
// merge two folders
$lightRoomNamespace = [FRAMEWORK_BASE_PATH, (GLOBAL_CORE != FRAMEWORK_BASE_PATH ? GLOBAL_CORE : null)];

// check for PACKAGERS_DIRECTORY_ARRAY
// merge directories
$lightRoomNamespace = isset($PACKAGERS_DIRECTORY_ARRAY) ? array_merge($lightRoomNamespace, $PACKAGERS_DIRECTORY_ARRAY) : $lightRoomNamespace;

// register default namespace for application
FrameworkAutoloader::registerNamespace([

    // Light room namespace for the Moorexa framework
    'Lightroom\\' => $lightRoomNamespace,

    // load packager
    'Lightroom\Packager\\' => APPLICATION_ROOT . 'package/',

    // load platform class
    'Classes\Platforms\\'  => SOURCE_BASE_PATH . 'utility/Classes/Platforms/'
])
->registerAutoloader()->secondaryAutoloader(function(){
    
    // composer autoloader
    $this->autoloadRegister(APPLICATION_ROOT . 'vendor/autoload.php');
    
})
// register push event for autoloadFailed events.
->registerPusherEvent();

// define controller root directory
define('CONTROLLER_ROOT', APPLICATION_ROOT . 'app');

// sub directories for controllers.
// This defines the folder names for models, views, custom (for header and footer), packages, partials, static and more
define('CONTROLLER_MODEL',      'Models');
define('CONTROLLER_VIEW',       'Views');
define('CONTROLLER_CUSTOM',     'Custom');
define('CONTROLLER_PACKAGE',    'Packages');
define('CONTROLLER_PARTIAL',    'Partials');
define('CONTROLLER_STATIC',     'Static');
define('CONTROLLER_PROVIDER',   'Providers');

// default packager for the cli
$CLI_PACKAGER = Lightroom\Packager\Moorexa\MoorexaCliPackager::class;

// default packager
$MAIN_PACKAGER = Lightroom\Packager\Moorexa\MoorexaMicroPackager::class;

// default content type
define('DEFAULT_CONTENT_TYPE', 'application/json');

// default timezone
define('DEFAULT_TIME_ZONE', 'Africa/Lagos');

// system type
define('SYSTEM_TYPE', 'services');