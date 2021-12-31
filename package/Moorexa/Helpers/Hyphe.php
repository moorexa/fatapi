<?php /** @noinspection ALL */

namespace Lightroom\Packager\Moorexa\Helpers;

use Hyphe\Compile as HypheCompile;
use Lightroom\Core\FrameworkAutoloader;
use Lightroom\Templates\Happy\Web\Engines\Interfaces\EngineInterface;
use Lightroom\Templates\Happy\Web\Interpreter;

/**
 * @package Hyphe Engine
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Hyphe implements EngineInterface
{
    /**
     * @method EngineInterface setInterpreter
     * @param Interpreter $instance
     * @return void 
     */
    public static function setInterpreter($instance) : void
    {

    }

    /**
     * @method EngineInterface initEngine
     * @param string $content
     * @return string 
     */
    public static function initEngine(string $content) : string
    {
        FrameworkAutoloader::registerNamespace([ 
            'Hyphe\\'       => func()->const('utility') . '/Classes/Hyphe/',
            'Masterminds\\' => func()->const('utility') . '/Classes/Hyphe/masterminds/html5/src/'
        ]);

        // parse document
        HypheCompile::ParseDoc($content);

        // return string
        return $content;
    }
}