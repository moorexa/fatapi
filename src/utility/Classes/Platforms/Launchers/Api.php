<?php
namespace Classes\Platforms\Launchers;

use Lightroom\Exceptions\PackageManagerException;
use Lightroom\Core\{
    Payload, BootCoreEngine
};
use Classes\Platforms\PlatformInterface;
use ReflectionException;

/**
 * @package Api Launcher
 * @author Amadi Ifeamyi <amadiify.com>
 */
class Api implements PlatformInterface
{
    use Helper;

    /**
     * @var array $headers_list
     */
    private $headers_list = [
        'Platform' => 'Api'
    ];

    /**
     * @method PlatformInterface loadPlatform
     * @param Payload $payload
     * @param BootCoreEngine $engine
     * @return bool
     * @throws PackageManagerException
     * @throws ReflectionException
     */
    public function loadPlatform(Payload &$payload, BootCoreEngine $engine) : bool
    {
        // @var bool $continue
        $continue = $this->hasHeaders();

        if ($continue) :

            // set content type
            $engine->setContentType($this->loadContentType('api'));

            // headers found
            $engine->defaultPackageManager($payload, \Lightroom\Packager\Moorexa\MoorexaApiPackager::class);

        endif;

        // return bool
        return $continue;
    }
}