<?php
namespace Verb\POST;

use Engine\{MetaDataService};
use Lightroom\Packager\Moorexa\RouterMethods;
use Lightroom\Packager\Moorexa\Interfaces\ResourceInterface;

/**
 * @package POST Handler
 * @author FregateLab <fregatelab.com>
 */
class Handler implements ResourceInterface
{
    /**
     * @method ResourceInterface onRequest
     * @param RouterMethods $method
     * @return void
     * 
     * Here is a basic example of how this works.
     * $method->post('hello/{name}', 'methodName');
     * 
     * Where "methodName" is a public method within class.
     * Hope it's simple enough?
     */
    public function onRequest(RouterMethods $method) : void
    {
        self::ReprogramOtherRequestMethods();

        // handle post requests
        $method->post('/help', 'HelpDocumentation');
        $method->post('/api', 'ChannelWithDefaultVersion');
        $method->post('/api/(v|V){version}', 'ChannelWithVersion');
    }

    /**
     * @method Handler ChannelWithDefaultVersion
     * @return void
     */
    public function ChannelWithDefaultVersion()
    {
        // load metadata service 
        MetaDataService::create();
    }

    /**
     * @method Handler ChannelWithVersion
     * @return void
     */
    public function ChannelWithVersion(string $version)
    {
        // load metadata service with version number
        MetaDataService::CreateWithVersion('v'.$version);      
    }

    /**
     * @method Handler HelpDocumentation
     * @return void
     * 
     * This method loads the general documentation for GET requests.
     */
    public function HelpDocumentation()
    {
        MetaDataService::loadHelpDocumentation('Introduction', 'Post');
    }

    /**
     * @method Handler ReprogramOtherRequestMethods
     * @return void
     */
    public static function ReprogramOtherRequestMethods()
    {
        // get the request method
        $method = strtolower($_SERVER['REQUEST_METHOD']);

        // not get or post
        if ($method != 'get' && $method != 'post') :

            // Change the request method
            $_SERVER['REQUEST_METHOD'] = 'POST';

        endif;

        // Store request method
        $_SERVER['X-REQUEST-METHOD'] = strtoupper($method);
    }
}