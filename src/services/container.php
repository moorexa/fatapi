<?php
use Lightroom\Adapter\Container;

/**
 * @package Container bulk registry
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * Register one more class here for ease of use. Instead of importing a class, with the helper function app() you
 * can access the instance of that class, static methods and properties, public methods and properties
 */
try {
    Container::register([
        /**
         * '<reference>' => <class>
         *
         * Let's try this example
         * 'mysql' => Lightroom\Database\Drivers\Mysql\Driver::class
         *
         * Now, you can access this class via app('mysql'). There are several possibilities here,
         * Please see Lightroom\Adapter\Container for available methods
         */

        // Extending LightQuery support for models
        'LightQuery' => Lightroom\Database\LightQuery::class,

        // add template handler
        'screen' => Lightroom\Templates\TemplateHandler::class,

        // add assets class
        'assets' => Lightroom\Packager\Moorexa\Helpers\Assets::class,

        // task handler class
        'tasks' => Lightroom\Queues\QueueHandler::class,

    ])
        // inject a container processor
        // class must extends Lightroom\Adapter\Interfaces\ContainerInterface
        ->inject([
            Lightroom\Packager\Moorexa\Helpers\ContainerProcessor::class
        ]);

} catch (\Lightroom\Exceptions\ClassNotFound $e) {
} catch (\Lightroom\Exceptions\InterfaceNotFound $e) {
} catch (ReflectionException $e) {
}