<?php
use Lightroom\Packager\Moorexa\Helpers\ScriptManager;
/**
 * @package Script Manager
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * Here you register several scripts that would be executed from top to bottom before controller gets loaded. You can also listen for 
 * classes initialized with classManager.
 */
ScriptManager::execute([
    /**
     * @example 
     * '<method>' => ExampleNamespace\MyClass::class
     * 
     * Method should be a static public method.
     */
    'initFunctions'             => FileDB\FileDBClient::class,
]);