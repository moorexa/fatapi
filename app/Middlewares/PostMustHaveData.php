<?php
namespace Middlewares;

use Closure;
use Engine\Response;
use Lightroom\Adapter\ClassManager;
use function Lightroom\Requests\Functions\{post};
use Lightroom\Router\Interfaces\MiddlewareInterface;
/**
 * @package PostMustHaveData
 * @author Amadi Ifeanyi <amadiify.com>
 */
class PostMustHaveData implements MiddlewareInterface
{
    /**
     * @method MiddlewareInterface request
     * @param Closure $render
     * @return void
     * 
     * This method holds the waiting request, call render to push view to browser.
     **/
    public function request(Closure $render) : void
    {
        if (count(post()->all()) == 0) :

            // failed
            ClassManager::singleton(Response::class)
            ->failed('Your HTTP request body must have at least one or more submitted data.');

        else:

            // continue with request
            call_user_func($render);

        endif;
    }

    /**
     * @method MiddlewareInterface requestClosed
     * @return void
     * 
     * This method would be called when request has been closed.
     **/
    public function requestClosed() : void
    {

    }
}