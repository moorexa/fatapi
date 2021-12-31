<?php
namespace Lightroom\Packager\Moorexa;

use Lightroom\{
    Exceptions\ClassNotFound,
    Database\DatabaseHandler,
    Common\Requirements,
    Requests\RequestManager,
    Security\SecurityGroup
};
use Lightroom\Core\{
    Payload, FrameworkRequirements, FrameworkConfiguration,
    BootCoreEngine, SystemPaths, Interfaces\PrivateAutoloaderInterface
};
use Lightroom\Adapter\{
    ClassManager, ProgramFaults, GlobalVariables
};
use Lightroom\Common\Interfaces\{
    PackageManagerInterface, LogbookLoggerInterface,
    ExceptionHandlerInterface
};
use Lightroom\Packager\Moorexa\{
    MoorexaRequestManager, MoorexaSecurityGroup, Helpers\ScriptManager
};

/**
 * @package Moorexa Framework Cli Packager
 * @author  Fregatelab <fregatelab.com>
 * @author  Amadi ifeanyi <amadiify.com>
 * @method next()
 * @method useDevelopmentServer()
 * @method configureStarterPack(string $string, array $array)
 */
class MoorexaCliPackager implements PackageManagerInterface, LogbookLoggerInterface, ExceptionHandlerInterface
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
         * use function Lightroom\Database\Functions\{db, db_with, map, query, schema, table}
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

    }

    /**
     * @method MoorexaCliPackager registerAliases
     * Registers Aliases with file path
     * @param array $aliases
     * @return mixed
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

