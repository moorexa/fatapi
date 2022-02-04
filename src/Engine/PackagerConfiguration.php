<?php /** @noinspection ALL */

namespace Engine;

use closure;
use Monolog\Logger;
use Lightroom\Vendor\{
    Monolog\MonologWrapper, 
    Whoops\WhoopsWrapper
};
use Lightroom\Common\File;
use Symfony\Component\Yaml\Yaml;
use Lightroom\Core\FrameworkAutoloader;
use Lightroom\Adapter\ClassManager;
use Lightroom\Packager\Moorexa\MoorexaSilentErrorListener;
use Lightroom\Packager\Moorexa\Configuration\DefaultPackagerConfiguration;
/**
 * @package Moorexa Packager framework configuration
 * @author amadi ifeanyi <amadiify.com>
 */
trait PackagerConfiguration
{
    use DefaultPackagerConfiguration;

    /**
     * @method DefaultPackagerConfiguration defaultPackagerHandlers
     * @return closure
     * 
     * This registers a default logger and exception handler.
     * We would be using monolog for logging and Whoops for exception handling.
     */
    public function defaultPackagerHandlers() : closure
    {
        return function()
        {
            // register error loggers
            $this->loggerHandlers([
                // Sends your logs to files, sockets, mail inbox, databases and various web services
                'monolog' => ClassManager::ifAvailable(Logger::class, MonologWrapper::class)
            ], 

            // register default logger
            $this->default('monolog'));

            // check if class exists
            if (!class_exists(Yaml::class)) FrameworkAutoloader::registerNamespace([
                'Symfony\Component\Yaml\\' => __DIR__ . '/../Helpers/Yaml/'
            ]);

            // exception handler
            $this->exceptionHandler($this->exceptionClass(ErrorHelper::class)->exceptionMethod('JsonResponseHandler'));

            // register a silent listener
            $this->silentListener(MoorexaSilentErrorListener::class);

        };
    }
}
