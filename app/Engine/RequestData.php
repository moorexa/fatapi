<?php
namespace Engine;

use ArrayAccess;
/**
 * @package RequestData Handler
 * @author Amadi Ifeanyi <amadiify.com> 
 */
class RequestData implements ArrayAccess
{
    /**
     * @var array $data
     */
    private $data = [];

    // load constructor
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // load getter
    public function __get(string $name)
    {
        if (isset($this->data[$name])) return $this->data[$name];
    }

    // load from data or load default
    public function get(string $name, $default = null)
    {
        if (isset($this->data[$name])) return $this->data[$name];

        // return default
        return $default;
    }

    // get all data
    public function getData() : array
    {
        return $this->data;
    }

    // array access 
    // starts here
    // @link: https://www.php.net/manual/en/class.arrayaccess.php
    public function offsetSet($offset, $value)
    {
        if (!empty($offset)) $this->data[$offset] = $value;
    }

    // check if offset exists
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    // remove offset
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    // get an offset
    public function offsetGet($offset) {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    // ends here

}