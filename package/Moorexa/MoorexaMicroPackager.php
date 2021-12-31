<?php
namespace Lightroom\Packager\Moorexa;

// start session for our application
session_start();

use Lightroom\{
    Exceptions\ClassNotFound,
    Security\SecurityGroup,
    Requests\RequestManager,
    Database\DatabaseHandler,
    Common\Requirements,
    Router\RouterHandler
};
use Lightroom\Core\{
    Payload, FrameworkRequirements, FrameworkConfiguration, 
    BootCoreEngine, SystemPaths, Interfaces\PrivateAutoloaderInterface
};
use Moorexa\Framework;
use Lightroom\Adapter\{
    DependencyChecker, GlobalVariables, ClassManager, ProgramFaults
};
use Lightroom\Packager\Moorexa\{
    MoorexaGlobalVariables, MoorexaSecurityGroup, 
    MoorexaRequestManager, MoorexaDatabaseHandler, 
    MoorexaMicroRouterController, MicroStarterPack
};
use Lightroom\Common\Interfaces\{
    PackageManagerInterface, LogbookLoggerInterface, 
    ExceptionHandlerInterface
};
use Lightroom\Packager\Moorexa\Helpers\ScriptManager;
/**
 * @package Moorexa Framework Micro Packager
 * @author  Fregatelab <fregatelab.com>
 * @author  Amadi ifeanyi <amadiify.com>
 * @method next()
 * @method useDevelopmentServer()
 * @method configureStarterPack(string $string, array $array)
 */
class MoorexaMicroPackager implements PackageManagerInterface, LogbookLoggerInterface, ExceptionHandlerInterface
{
    use ProgramFaults, Configuration\DefaultPackagerConfiguration;

    /**
     * @method MoorexaWebPackager registerPayload for package manager
     * @param Payload $payload
     * @param BootCoreEngine $engine
     * @throws ClassNotFound
     */
    public function registerPayload(Payload &$payload, BootCoreEngine $engine)
    {
        /**
         * @method payload register program logger and exception handlers
         * 
         * register a default logger and exception handler. fallback exception would trigger default exception handler.
         * try error()->silent(); if you want to hide exception during runtime for debugging
         */ 
        $this->programFaultGroup($this->defaultPackagerHandlers());

        /**
         * @method payload register system paths
         * 
         * register the default system path. The FunctionLibrary provides a fast abstraction to system paths,
         * just use func()->const(string constant name)
         */
        $engine->registerSystemPaths(ClassManager::singleton(SystemPaths::class), $this->defaultPackagerPaths());

        /**
         * @method payload application requirements
         * 
         * you should comment this line out during production
         * !improved performance. But be sure that your server meets the requirements
         * feel free to write your custom requirement to replace the default framework requirements,
         */
        $payload->register('load-requirement', $payload->handler(Requirements::class)->arguments(ClassManager::singleton(FrameworkRequirements::class)));

        /**
         * @method payload register dependency checker
         * 
         * register dependency checks for our program classes.
         * just add a source which is an actual php class file and register a group of interface, trait, classes required for
         * the successful operation of that class.
         * see the register format below
         * 'interface | trait | classes' => 'purpose of this file. exception handler would use this.'
         */
        $payload->register('load-dependency-checker', $payload->handler(DependencyChecker::class, 'forClasses')->arguments($this->defaultPackagerDependencies()));

        /**
         * @method payload register global variables
         * 
         * when registered, this would enable us use var_get() to access global variables
         * this can only be registered once, and provides a quick abstraction through Lightroom\Functions\GlobalVariables
         * To make a variable global use var_set('key', 'value')
         * in your php file, you can grab this two functions with the use keyword
         * 
         * example:
         * use function Lightroom\Functions\GlobalVariables\{var_get, var_set};
         * 
         * registry class must implement GlobalVariablesInterface
         */
        $payload->register('load-global-variables', $payload->handler(GlobalVariables::class)->arguments(ClassManager::singleton(MoorexaGlobalVariables::class)));

        /**
         * @method payload application configuration
         * 
         * load application configuration. You can use your custom configuration handler.
         * This configuration handler provides us with the env(), error(), logger(), func(), env_set(), global functions for our application
         */
        $payload->register('load-config', $payload->handler(FrameworkConfiguration::class)->arguments($this->defaultPackagerConfiguration()));

        /**
         * @method payload application security group
         * 
         * load application security group. which includes encryption, decryption, password hashing, password verification,
         * certificate signing for openssl and much more.
         * 
         * example:
         * use function Lightroom\Security\Functions\{encrypt, decrypt, md5s, sha1s, hash_password, verify_password};
         */
        $payload->register('load-security-group', $payload->handler(SecurityGroup::class)->arguments(MoorexaSecurityGroup::class, $this->defaultPackagerSecurityGroup()));

        /**
         * @method payload application request manager
         * 
         * load application request manager. which includes headers, server, cookie, session, file, post and get.
         * It's bundled with some helper functions. See example below;
         * 
         * example:
         * use function Lightroom\Requests\Functions\{cookie, session, header, file, get, post, server};
         */
        $payload->register('load-request-manager', $payload->handler(RequestManager::class)->arguments(MoorexaRequestManager::class, function()
        {
            // allow headers
            $this->header->allow([
                'Content-Type',
                'X-Api-Token',
                // you can add more here
            ]);

            // this would call the next request on payload.
            $this->next();
        }));

        /**
         * @method payload database handler
         * 
         * load application database handler. This registers the default database handler for our application.
         * This handler includes a query builder and it's bundled with some helper functions. See example below;
         * 
         * example:
         * use function Lightroom\Database\Functions\{db, db_with, map, query, schema, table, rows, driver}
         */
        $payload->register('load-database-handler', $payload->handler(DatabaseHandler::class)->arguments(MoorexaDatabaseHandler::class));
        
        /**
         * @method payload script processor
         * 
         * load application script processor. This triggers static methods before starting the router. 
         * This handler simply loads a config file, execute static methods assigned to it.
         * 
         */
        $payload->register('script-processor', $payload->handler(ScriptManager::class));

        /**
         * @method payload router handler
         * 
         * load application default router. This registers the default route handler for our application.
         * This handler includes an MVC architecture for your convenience. It's bundled with some helper functions from
         * the template engine, csrf manager and the view engine. With the assist manager, you can generate a controller that works well for this router without any line of code. 
         * See example below;
         * 
         * example
         * use function Lightroom\Templates\Functions\{render, redirect, json, view, controller} etc..
         * use function Lightroom\Common\Functions\{csrf, csrf_error, csrf_verified}
         */ 
        $payload->register('load-router', $payload->handler(RouterHandler::class)->arguments(MoorexaMicroRouterController::class, function()
        {
            // configure starter pack
            $this->configureStarterPack('@starter', [
                'main'                =>  [MicroStarterPack::class, 'starterPage'],
                'coming-soon'         =>  [MicroStarterPack::class, 'comingSoon'],
                'maintenance'         =>  [MicroStarterPack::class, 'maintenance'],
                'page-not-found'      =>  [MicroStarterPack::class, 'pageNotFound'],
                'framework-namespace' =>  Framework::class,
                'invalid-controller'  =>  [MicroStarterPack::class, 'invalidController']
            ]);
        }));
        
    }

    /**
     * @method MoorexaWebPackager registerAliases
     * Registers Aliases with file path
     * @param array $aliases
     * @return PrivateAutoloaderInterface|__anonymous@8944
     */
    public function registerAliases(array $aliases)
    {
        // create an anonymous
        return new class($aliases) implements PrivateAutoloaderInterface 
        {
            use \Lightroom\Packager\Moorexa\AliaseAutoloader;
        };
    }
}
