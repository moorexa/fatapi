<?php
namespace Lightroom\Packager\Moorexa;

use Exception;
use Lightroom\Router\RouterHandler;
use Lightroom\Exceptions\ClassNotFound;
use Lightroom\Exceptions\InterfaceNotFound;
use Lightroom\Exceptions\MethodNotFound;
use Lightroom\Router\Guards;
use Lightroom\Packager\Moorexa\MVC\{
    Helpers\ControllerLoader, Helpers\ControllerGuards
};
use Lightroom\Core\FrameworkAutoloader;
use ReflectionException;
use Lightroom\Packager\Moorexa\Helpers\{
    UrlControls, URL
};
use Lightroom\Templates\TemplateHandler;
use Lightroom\Router\Interfaces\RouterHandlerInterface;
use Lightroom\Packager\Moorexa\MVC\Helpers\ControllerViewHandler;

/**
 * @package MoorexaWebRouter Controller
 * @author Fregatelab <fregatelab.com>
 * 
 * This opens up the model - view and controller 
 */
class MoorexaWebRouterController implements RouterHandlerInterface
{
    /**
     * @method RouterHandlerInterface openController
     * @return void
     * @throws MethodNotFound
     * @throws ClassNotFound
     * @throws InterfaceNotFound
     * @throws ReflectionException
     * @throws Exception
     */
    public static function openController() : void
    {
        // prepare incoming url
        self::processIncomingUrl();

        //register framework namespace
        self::registerNamespaces();

        // register framework namespace
        self::registerControllerNamespace();

        // load template handler functions
        (new TemplateHandler(function(){
            // template handler body
        }));

        // get container file from services directory
        $containerFile = get_path(func()->const('services'), '/container.php');

        // load container file
        if (file_exists($containerFile)) include_once $containerFile;

        // include global functions for the framework
        include_once __DIR__ . '/Helpers/Functions.php';

        // load controller guard and update requestUri
        Router::$requestUri = Guards::loadGuard(ControllerGuards::class, Router::$requestUri);

        // include event registry file
        include_once get_path(func()->const('services'), '/events.php');

        // include route base file
        include_once get_path(func()->const('services'), '/routes.php');

        // get route matched
        $routeMatched = Router::getRouteMatched();

        // update incomingURI
        URL::setIncomingUri( count($routeMatched) > 0 ? $routeMatched : Router::$requestUri );

        // check controllers 
        // get route from checking controllers
        if (count($routeMatched) == 0) URL::setIncomingUri( ControllerLoader::useDefaultRoutingMechanism() );

        // serve controller
        ControllerLoader::serveController();
    }

    /**
     * @method MoorexaWebRouterController processIncomingUrl
     * @return void
     * @throws ClassNotFound
     */
    protected static function processIncomingUrl() : void
    {
        // register url with router
        Router::$requestUri = UrlControls::getUrl();

        // Register default url
        URL::registerUrlFromConfig();
    }

    /**
     * @method MoorexaWebRouterController registerNamespaces
     * @return void
     */
    protected static function registerNamespaces() : void 
    {
        FrameworkAutoloader::registerNamespace([

            // register namespace for middleware
            'Moorexa\Middlewares\\' => func()->const('utility') . '/Middlewares',

            // register namespace for guards
            'Moorexa\Guards\\' => func()->const('utility') . '/Guards'
        ]);
    }

    /**
     * @method ControllerViewHandler registerControllerNamespace
     * @return void
     */
    private static function registerControllerNamespace() : void 
    {
        // @var string $namespace
        $namespace = RouterHandler::getStarterPack('framework-namespace');

        if ($namespace !== null) :

            // register namespace
            FrameworkAutoloader::registerNamespace([
                $namespace . '\\' . ControllerLoader::getNamespacePrefix() => ControllerViewHandler::basePath()
            ]);

        endif;
    }
}