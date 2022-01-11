<?php
namespace Engine\Interfaces;
/**
 * @package Request Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface RequestInterface
{
    /**
     * @method RequestInterface get
     * @param string $key
     * @return mixed
     * 
     * This return data cached from the request body
     */
    public function get(string $key);

    /**
     * @method RequestInterface getSchema
     * This returns the current schema of the request service method.
     */
    public function getSchema();

    /**
     * @method RequestInterface getData
     * @return array
     * 
     * This returns all the data cached from the request body
     */
    public function getData() : array;

    /**
     * @method RequestInterface getOnly
     * @param mixed $args
     * @return array
     * 
     * This returns selected data from the cached request body
     */
    public function getOnly(...$args) : array;

    /**
     * @method RequestInterface query
     * @param string $key
     * 
     * This returns the current query data
     */
    public function query(string $key);

    /**
     * @method RequestInterface getParam
     * @param int $index
     */
    public function getParam(int $index);

    /**
     * @method RequestInterface useModel
     * @param string $model
     * 
     * This loads a model to handle the request data sent
     */
    public function useModel(string $model);

    /**
     * @method RequestInterface getHeader
     * @param string $key
     * @return mixed
     * 
     * This return data cached from the request header
     */
    public function getHeader(string $key);
}