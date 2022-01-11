<?php
namespace Resources\User\v1;

use Engine\{Interfaces\ResourceInterface, Request, Response};
/**
 * @package GetUser
 * @author Amadi Ifeanyi <amadiify.com>
 */
class GetUser implements ResourceInterface
{
    /**
     * @method GetUser Init
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