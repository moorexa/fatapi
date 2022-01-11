<?php
namespace Verb\GET;

use Engine\{MetaDataService};
use Lightroom\Packager\Moorexa\RouterMethods;
use Lightroom\Packager\Moorexa\Interfaces\ResourceInterface;

/**
 * @package GET Handler
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
     * $method->get('hello/{name}', 'methodName');
     * 
     * Where "methodName" is a public method within class.
     * Hope it's simple enough?
     */
    public function onRequest(RouterMethods $method) : void
    {
        $method->get('/help', 'HelpDocumentation');
        $method->get('/api', 'ChannelWithDefaultVersion');
        $method->get('/api/(v|V){version}', 'ChannelWithVersion');
        $method->get('/api/(v|V){version}/{id}', 'ChannelWithVersionAndId');
        $method->get('/api/{id}', 'ChannelWithDefaultVersionAndId');
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
     * @method Handler ChannelWithDefaultVersionAndId
     * @param string $id
     * @return void
     */
    public function ChannelWithDefaultVersionAndId(string $id)
    {
        // set the id
        MetaDataService::setParam($id);

        // load metadata service 
        self::ChannelWithDefaultVersion();
    }

    /**
     * @method Handler ChannelWithVersion
     * @param string $version
     * @return void
     */
    public function ChannelWithVersion(string $version)
    {
        // load metadata service with version number
        MetaDataService::CreateWithVersion('v'.$version);      
    }

    /**
     * @method Handler ChannelWithVersionAndId
     * @param string $version
     * @param string $id
     * @return void
     */
    public function ChannelWithVersionAndId(string $version, string $id)
    {
        // set param 
        MetaDataService::setParam($id);
        
        // load metadata service with version number
        self::ChannelWithVersion($version);      
    }

    /**
     * @method Handler HelpDocumentation
     * @return void
     * 
     * This method loads the general documentation for GET requests.
     */
    public function HelpDocumentation()
    {
        MetaDataService::loadHelpDocumentation('Introduction', 'Get');
    }
}