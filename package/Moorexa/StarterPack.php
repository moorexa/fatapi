<?php
namespace Lightroom\Packager\Moorexa;

use Lightroom\Adapter\ClassManager;
use Lightroom\Exceptions\{ClassNotFound, InterfaceNotFound};
use Lightroom\Packager\Moorexa\Helpers\URL;
use ReflectionException;
use function Lightroom\Functions\GlobalVariables\var_set;
use Lightroom\Packager\Moorexa\Interfaces\PageNotFoundInterface;
/**
 * @package Moorexa Starter Pack
 * @author Amadi Ifeanyi <amadiify.com>
 */
class StarterPack
{
    /**
     * @method StarterPack starterPage
     * @return void
     *
     * This method loads the default starter page
     * @throws ClassNotFound
     */
    public static function starterPage() : void
    {
        // load assets 
        $assets = ClassManager::singleton(Helpers\Assets::class);

        // load url helper
        $urlHelper = ClassManager::singleton(Helpers\URL::class);

        // get base path
        $basePath = $urlHelper->getPathUrl() . '/' . get_path(func()->const('system'), '/Starter/');

        // get main directory
        $directory = get_path(func()->const('system'), '/Starter/');

        // include starter file
        include_once get_path(func()->const('system'), '/Starter/index.html');
    }

    /**
     * @method StarterPack comingSoon
     * @return void
     *
     * This loads the coming soon template
     * @throws ClassNotFound
     */
    public static function comingSoon() : void 
    {
        // load assets 
        $assets = ClassManager::singleton(Helpers\Assets::class);

        // include coming soon file
        include_once get_path(func()->const('system'), '/Starter/coming-soon.html');
    }

    /**
     * @method StarterPack maintenance
     * @return void
     *
     * This loads the maintenance template
     * @throws ClassNotFound
     */
    public static function maintenance() : void 
    {
        // load assets 
        $assets = ClassManager::singleton(Helpers\Assets::class);

        // include coming soon file
        include_once get_path(func()->const('system'), '/Starter/maintenance-mode.html');
    }

    /**
     * @method StarterPack pageNotFound
     * @return void
     *
     * This loads the default template for 404 errors
     * @throws InterfaceNotFound
     * @throws ClassNotFound
     * @throws ReflectionException
     */
    public static function pageNotFound() : void 
    {
        // use the default mode.
        if (self::canLoadDefaultErrorHandler()) :

            // @var string $url, $assets, $errorCode
            list($url, $assets, $errorCode) = self::loadPageNotFoundHelper();

            // @var string $requestType
            $requestType = preg_match('/[.]([a-zA-Z0-9]+)$/', $url) != false ? 'file' : 'route';

            // @var string $title
            $title = $requestType == 'route' ? 'Page Not Found' : 'File Not Found';

            // @var string $message
            $message = 'The requested '.$requestType.' : <span style="padding: 5px; background: #fcfcfc; color: #f20;">'.$url.'</span> was not found. You should try returning to the home page.';

            // include coming soon file
            include_once get_path(func()->const('system'), '/Starter/http-error.html');

        endif;
    }

    /**
     * @method StarterPack invalidController
     * @param string $controller
     * @return void
     *
     * This loads an invalid controller template
     * @throws ClassNotFound
     * @throws InterfaceNotFound
     * @throws ReflectionException
     */
    public static function invalidController(string $controller)
    {
        // use the default mode.
        if (self::canLoadDefaultErrorHandler($controller)) :

            // @var string $url, $assets, $errorCode
            list($url, $assets, $errorCode) = self::loadPageNotFoundHelper();

            // @var string $title
            $title = 'Invalid Controller';

            // @var string $message
            $message = 'The requested route : <span style="padding: 5px; background: #fcfcfc; color: #f20;">'.$url.'</span> was not found. It\'s also possible that we couldn\'t load this controller from this namesapce "'.$controller.'".';

            // include coming soon file
            include_once get_path(func()->const('system'), '/Starter/http-error.html');

        endif;
    }

    /**
     * @method StarterPack loadPageNotFoundHelper
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

    /**
     * @method StarterPack canLoadDefaultErrorHandler
     * @param string $controller
     * @return bool
     * @throws InterfaceNotFound
     * @throws ReflectionException
     */
    private static function canLoadDefaultErrorHandler(string $controller = '') : bool 
    {
        // @var bool $continue
        $continue = true;

        // load page not found class from env config
        $errorClass = isset($_ENV['pageNotFound']) ? $_ENV['pageNotFound'] : null;

        // load error class
        if (is_string($errorClass) && class_exists($errorClass)) :

            // create a reflection class 
            $reflection = new \ReflectionClass($errorClass);

            // check for interface implementation 
            if (!$reflection->implementsInterface(PageNotFoundInterface::class)) throw new InterfaceNotFound($errorClass, PageNotFoundInterface::class);

            // load method
            $continue = call_user_func([$errorClass, 'pageNotFound'], URL::getIncomingUri(), $controller);

        endif;

        // return bool
        return $continue;
    }
}