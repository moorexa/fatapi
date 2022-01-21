<?php
namespace Engine;

use Lightroom\Adapter\ClassManager;
/**
 * @package StructHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait StructHelper
{
    /**
     * @method StructHelper load
     * @param string $method
     * @return class
     */
    final public static function load(string $method)
    {
        // get instance
        $class = ClassManager::singleton(static::class);

        // create data
        $data = [];

        // check if method exists
        if (method_exists($class, $method)) $data = $class->{$method}();

        // return class
        return new StructData($data);
    }
}