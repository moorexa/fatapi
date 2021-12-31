<?php

/**
 * *************************
 * We would begin application boot process by extablishing all the required paths.
 * For this, we would use the GlobalConstants class
 */
use Lightroom\Core\{
    GlobalConstants, Setters\Constants
};
use Lightroom\Adapter\GlobalVariables as GlobalVars;

/**
 * ****************
 * First, we create an instance of global constants 
 * We would be needing this prefix for all our application path
 */
$global = GlobalVars::var_set('global-constant', new GlobalConstants());
$constant = new Constants();

/**
 * *******
 * Create base path
 */
$base = $global->newConstant($constant->name('Home')->value(APPLICATION_ROOT));

/**
 * *******
 * Add path prefix for constant
 */
$base->createPrefix('PATH_TO_');

/**
 * *******
 * Add suffix for path constants
 * we add a trailing forward slash to keep our path valid.
 */
$base->createSuffix('/');

/**
 * ********
 * Create controller base path
 */
$base->fromConstant($constant->name('web_platform')->value(CONTROLLER_ROOT));

/**
 * ********
 * Create system path
 */
$system = $base->fromConstant($constant->name('system')->value(FRAMEWORK_BASE_PATH));

/**
 * ********
 * Create kernel path for source
 */
$kernel = $base->fromConstant($constant->name('kernel')->value(SOURCE_BASE_PATH));

/**
 * *******
 * Create distribution path
 */
$dist = $base->newConstant($constant->name('dist')->value(DISTRIBUTION_BASE_PATH));


// add extra directory
$dist->fromConstant($constant->name('extra')->value('extra'));


/**
 * ********
 * Create kernel sub directories path
 */
$kernel->createConstantFromArray(
    $constant->name('config')->value('config'),
    $constant->name('services')->value('services'),
    $constant->name('konsole')->value('console'),
    $constant->name('extension')->value('extensions')
);

/**
 * ********
 * Create lab path
 */
$lab = $kernel->fromConstant($constant->name('lab')->value('lab'));

/**
 * ********
 * Create database config base path
 */
$kernel->fromConstant($constant->name('database')->value('database'));

/**
 * ********
 * Create public directory base path
 */
$public = $dist->fromConstant($constant->name('public')->value('public'));

/**
 * ********
 * Create public sub directories path
 */
$public->createConstantFromArray(
    $constant->name('helper')->value('helper'),
    $constant->name('errors')->value('errors'),
    $constant->name('assets')->value('assets'),
    $constant->name('css')->value('assets/css'),
    $constant->name('js')->value('assets/js'),
    $constant->name('media')->value('assets/media'),
    $constant->name('image')->value('assets/images')
    
);

/**
 * ********
 * Create components path
 */
$components = $dist->fromConstant($constant->name('components')->value('components'));

/**
 * ********
 * Create components sub directories path
 */
$components->createConstantFromArray(
    $constant->name('partial')->value('Partials'),
    $constant->name('directives')->value('Directives'),
    $constant->name('templates')->value('Templates')
);

/**
 * ********
 * Create utility path
 */
$utility = $kernel->fromConstant($constant->name('utility')->value('utility'));

/**
 * ********
 * Create utility sub directories path
 */
$utility->createConstantFromArray(
    $constant->name('plugin')->value('Plugins'),
    $constant->name('provider')->value('Providers'),
    $constant->name('middleware')->value('Middlewares'),
    $constant->name('console')->value('Console'),
    $constant->name('guards')->value('Guards'),
    $constant->name('storage')->value('Storage'),
    $constant->name('event')->value('Events')
);

// clean up
$prefix = null;
$constant = null;