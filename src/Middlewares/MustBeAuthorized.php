<?php
namespace Middlewares;

use Closure;
use Engine\Response;
use Lightroom\Adapter\ClassManager;
use function Lightroom\Security\Functions\{md5s};
use Lightroom\Router\Interfaces\MiddlewareInterface;
use function Lightroom\Requests\Functions\{headers};
/**
 * @package MustBeAuthorized
 * @author Amadi Ifeanyi <amadiify.com>
 */
class MustBeAuthorized implements MiddlewareInterface
{
    /**
     * @var string $authorizationToken
     * 
     * You should generate a new token from the CLI and update authorizationToken with it
     * By default, the system would auto generate one and load the request with it which is not the best
     * use 'php fatapi make:token {unique name}' to generate and update $authorizationToken
     */
    public $authorizationToken = '';

    /**
     * @method MiddlewareInterface request
     * @param Closure $render
     * @return void
     * 
     * This method holds the waiting request, call render to push view to the top stack.
     **/
    public function request(Closure $render) : void
    {
        // set the authorization token
        $this->generateAuthorizationToken();

        /**
         * call $render() to continue with request
         */
        if (headers()->has('authorization') || headers()->has('Authorization')) :

            // get bearer
            $bearer = headers()->has('authorization') ? headers()->authorization : headers()->Authorization;

            // check for bearer
            if (stripos($bearer, 'bearer') === false) :

                // failed
                ClassManager::singleton(Response::class)
                ->warning('Your authorization header is missing the keyword "Bearer". Please use this format (Authorization : Bearer <token here>)');

            else:

                // remove bearer
                $token = trim(str_ireplace('bearer', '', $bearer));

                // compare token
                if ($token == $this->authorizationToken) :

                    // proceed
                    call_user_func($render);
                
                elseif ($token == '') :

                    // failed
                    ClassManager::singleton(Response::class)
                    ->warning('Authorization failed! Your have not provided any authorization token for your request!');

                else:

                    // failed
                    ClassManager::singleton(Response::class)
                    ->warning('Authorization failed! Your authorization code is not valid!');

                endif;

            endif;

        else:

            // failed
            ClassManager::singleton(Response::class)
           ->warning('Your HTTP request header is missing authorization code. Please add (Authorization : Bearer <token here>)');

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

    /**
     * @method MustBeAuthorized generateAuthorizationToken
     * @return void
     */
    public function generateAuthorizationToken()
    {
        if ($this->authorizationToken == '') :

            // this is basic
            $this->authorizationToken = sha1(md5s(file_get_contents(FATAPI_BASE . '/info.json')));

        endif;
    }
}