<?php
namespace Engine\Interfaces;

use Engine\{Request, Response};
/**
 * @package ResourceInterface
 * @author FregateLab <fregatelab.com>
 */
interface ResourceInterface
{
    /**
     * @method ResourceInterface Init
     * @param Request $request
     * @param Response $response
     * @return void
     * 
     * This method would load the default method for this interface
     */
    public function Init(Request $request, Response $response) : void;
}