<?php
namespace Classes\Platforms;

use Lightroom\Core\{
    Payload, BootCoreEngine
};
/**
 * @package Platform Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface PlatformInterface
{
    /**
     * @method PlatformInterface loadPlatform
     * @param Payload $payload
     * @param BootCoreEngine $engine
     * @return bool
     */
    public function loadPlatform(Payload &$payload, BootCoreEngine $engine) : bool;
}