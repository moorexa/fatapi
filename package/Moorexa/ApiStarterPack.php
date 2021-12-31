<?php
namespace Lightroom\Packager\Moorexa;

use Exception;
use Lightroom\Adapter\ClassManager;
use Lightroom\Exceptions\ClassNotFound;
use Lightroom\Templates\TemplateHandler;
use Lightroom\Packager\Moorexa\Helpers\URL;
/**
 * @package Moorexa API Starter Pack
 * @author Amadi Ifeanyi <amadiify.com>
 */
class ApiStarterPack
{
    /**
     * @method ApiStarterPack starterPage
     * @return void
     *
     * This method loads the default starter page
     * @throws Exception
     */
    public static function starterPage() : void
    {
        // maintenance mode
        TemplateHandler::render([
            'message' => 'You have reached the default starter route. So what\'s next?',
            'status' => 'success',
            'code' => 200
        ]);
    }

    /**
     * @method ApiStarterPack comingSoon
     * @return void
     *
     * This loads the coming soon template
     * @throws Exception
     */
    public static function comingSoon() : void 
    {
        // maintenance mode
        TemplateHandler::render([
            'message' => 'Our Application Server would be available soon. Please check back later',
            'status' => 'success',
            'code' => 200
        ]);
    }

    /**
     * @method ApiStarterPack maintenance
     * @return void
     *
     * This loads the maintenance template
     * @throws Exception
     */
    public static function maintenance() : void 
    {
        // maintenance mode
        TemplateHandler::render([
            'message' => 'Our Application Server is currently on maintenance. Please try again later',
            'status' => 'error',
            'code' => 200
        ]);
    }

    /**
     * @method ApiStarterPack pageNotFound
     * @return void
     *
     * This loads the default template for 404 errors
     * @throws ClassNotFound
     */
    public static function pageNotFound() : void 
    {
        // @var string $url, $assets, $errorCode
        list($url, $assets, $errorCode) = self::loadPageNotFoundHelper();

        // page not found
        TemplateHandler::render([
            'message' => 'Route not found',
            'status' => 'error',
            'code' => $errorCode,
            'route' => $url
        ]);
    }

    /**
     * @method ApiStarterPack invalidController
     * @param string $controller
     * @return void
     *
     * This loads an invalid controller template
     * @throws ClassNotFound
     */
    public static function invalidController(string $controller)
    {
        // @var string $url, $assets, $errorCode
        list($url, $assets, $errorCode) = self::loadPageNotFoundHelper();

        // page not found
        TemplateHandler::render([
            'message' => 'Controller not found',
            'status' => 'error',
            'code' => $errorCode,
            'route' => $url
        ]);
    }

    /**
     * @method ApiStarterPack loadPageNotFoundHelper
     * @return array
     * @throws ClassNotFound
     */
    private static function loadPageNotFoundHelper() : array
    {
        // load assets 
        $assets = ClassManager::singleton(Helpers\Assets::class);

        // @var string $errorCode (type of error)
        $errorCode = 404;

        // set response code
        http_response_code($errorCode);

        // return string
        return [implode('/', URL::getIncomingUri()), $assets, $errorCode];
    }
}