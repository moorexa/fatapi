<?php
namespace Resources\User\v1;

use Messaging\EmailAlerts;
use Messaging\Emails\EmailSender;
use Engine\{Interfaces\ResourceInterface, Request, Response};
/**
 * @package PostUser
 * @author Amadi Ifeanyi <amadiify.com>
 */
class PostUser implements ResourceInterface
{
    use Providers\CreateProvider,
        Providers\DeleteProvider,
        Providers\UpdateProvider;

    /**
     * @method PostUser Init
     * @param Request $request
     * @param Response $response
     * @return void
     * 
     * @start.doc
     * 
     * .. Your documentation content goes in here.
     * 
     */
    public function Init(Request $request, Response $response) : void
    {
        $response->success('It works!');
    }

}