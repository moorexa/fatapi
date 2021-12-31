<?php
namespace Lightroom\Adapter\Interfaces;

use Exception;

/**
 * @package SilentError Listener Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface SilentErrorListenerInterface
{
    /**
     * @method SilentErrorListenerInterface exceptionOccurred
     * @param Exception $exception
     */
    public function exceptionOccurred($exception) : void;
}