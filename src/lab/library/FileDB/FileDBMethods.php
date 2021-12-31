<?php 
namespace FileDB;
/**
 * @package FileDBMethods
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait FileDBMethods 
{
    /**
     * @var mixed $data
     */
    private $data = [];

    /**
     * @method FileDBMethods __construct
     * lOAD Data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @method FileDBMethods limit
     * Limit records
     */
    public function limit(int $start, int $end)
    {
        // @var array $newData
        $newData = [];

        // load for array
        if (is_array($this->data)) $newData = array_splice($this->data, $start, $end);

        // load for objext
        if (is_object($this->data)) :

            // @var int $index 
            $index = 0;

            // run loop
            foreach ($this->data as $key => $val) :

                if ($index >= $start) $newData[$key] = $val;

                // end now
                if ($index == $end) break;

                // increment index
                $index++;

            endforeach;

            // back to obj
            $newData = json_decode(json_encode($newData));

        endif;

        // update data
        $this->data = $newData;

        // return self 
        return $this;
    }

    /**
     * @method FileDBMethods first
     * Get the first record
     * @return mixed
     */
    public function first()
    {
        // @var mixed $data 
        $data = null;

        // loop through
        foreach ($this->data as $key => $val) :

            // get val
            $data = $val;
            
            // set the id
            if (is_object($data)) $data->id = 1;

            // break out
            break;

        endforeach;

        // return data 
        return $data;
    }

    /**
     * @method FileDBMethods last
     * Get the last record
     * @return mixed
     */
    public function last()
    {
        // @var mixed $data 
        $data = is_array($this->data) ? array_reverse($this->data) : $this->data;

        // get the array size
        $size = is_array($data) ? count($data) : 1;

        // loop through
        foreach ($data as $key => $val) :

            // get val
            $data = $val;
            
            // set the id
            if (is_object($data)) $data->id = $size;

            // increment size
            $size++;

        endforeach;

        // return data 
        return $data;
    }

    /**
     * @method FileDBMethods index
     * Fetch from an index
     * @param int $index
     * @return mixed
     */
    public function index(int $index)
    {
        // @var mixed $data 
        $data = $this->data;

        // get the array size
        $size = 1;

        // loop through
        foreach ($data as $key => $val) :

            // get val
            $data = $val;
            
            // set the id
            if (is_object($data)) $data->id = $size;

            // can we break out
            if ($size == $index) break;

            // increment size
            $size++;

        endforeach;

        // return data 
        return $data;
    }

    /**
     * @method FileDBMethods fetch
     * @param string $fetchMethod
     */
    public function fetch(string $fetchMethod = '')
    {
        // @var array $cache
        static $cache;

        // @var int $index 
        static $index;

        // push to cache
        if ($cache === null) $cache = $this->data;

        // run loop
        if (is_array($cache)) :

            // load cache
            if (count($cache) > 0) :
                // assign an id
                if ($index == null) $index = 1;
                // get data
                $data = array_shift($cache);
                // try set id
                if (is_object($data)) $data->id = $index;
                // increment id
                $index += 1;
                // return data
                return (is_object($data) && $fetchMethod == 'fetch-array') ? ((array) $data) : $data;
            endif;  

        endif;

        //  reset data
        $cache = null;
        $index = 1;

        return false;
    }

    /**
     * @method FileDBMethods where
     * @param string $statement
     * @param array $args 
     */
    public function where(string $statement, ...$args)
    {
        // replace and 
        $statement = str_ireplace(' and ', '&', $statement);

        // replace or
        $statement = str_ireplace(' or ', '&|', $statement);

        // remove spaces
        $statement = preg_replace('/[\s]+/', '', $statement);

        // parse statement
        $parsed = $this->parse_query($statement);

        // new data
        $newData = [];

        // run where statement
        foreach ($this->data as $key => $val) :

            if (is_object($val) && is_int($key)) :

                // check next 
                $checkNext = true;

                // parsed index
                $parsedIndex = 0;

                foreach ($parsed as $keyIndex => $keyVal) :

                    // look for AND
                    if ($keyIndex[0] == ':') :

                        // remove ':'
                        $keyIndexAnd = substr($keyIndex, 1);

                        // check next
                        $this->canCheckNext($val, $args, $keyIndexAnd, $parsedIndex, $keyVal, $checkNext, 'and');

                    endif;

                    // look for OR
                    if ($keyIndex[0] == '|') :

                        // remove val
                        $keyIndexOr = substr($keyIndex, 2);

                        // check next
                        $this->canCheckNext($val, $args, $keyIndexOr, $parsedIndex, $keyVal, $checkNext, 'or');

                    endif;

                    // add to new data
                    if ($checkNext) $newData[$key] = $val;

                    // increment index
                    $parsedIndex++;

                endforeach;

                // reset check next
                $checkNext = true;

                // reset passed index
                $parsedIndex = 0;

            endif;

        endforeach;

        // push data
        $this->data = $newData;

        // return instance
        return $this;
    }

    /**
     * @method FileDBMethods result
     * @return mixed
     */
    public function results()
    {
        return $this->data;
    }

    /**
     * @method FileDBMethods __get
     * @param string $key 
     */
    public function __get(string $key)
    {
        if (isset($this->data->{$key})) return $this->data->{$key};
    }

    /**
     * @method FileDBMethods value
     * @param string $key 
     * @return mixed
     */
    public function value(string $key)
    {
        // make array
        $data = (array) $this->data;

        if (isset($data[$key])) return $data[$key];
    }

    /**
     * @method FileDBMethods __call
     * @param string $key 
     * @param array $arguments
     * 
     * Returns an objext or mixed value if key exists
     */
    public function __call(string $key, array $arguments)
    {
        // @var mixed $firstArgument
        $firstArgument = isset($arguments[0]) ? $arguments[0] : null;

        // helper function
        $getData = function(string $argument) use (&$key)
        {   
            foreach ($this->data as $object) :

                // check for key
                if (isset($object->{$key})) :
                    
                    // compare value
                    if (strtoupper($argument) == strtoupper($object->{$key})) return $object;

                endif;

            endforeach;

            // return an empty object
            return (object) [];
        };

        // check if key exists in result
        if (is_array($this->data) && $firstArgument !== null) :

            // is first args an array
            if (is_array($firstArgument)) :

                // @var mixed $data
                $data = [];

                // run loop
                foreach ($firstArgument as $objectKey) $data[] = $getData($objectKey);

                // return data
                return $data;

            else:

                return $getData($firstArgument);

            endif;

        elseif (is_object($this->data) && $firstArgument !== null) :

            // compare value
            if (isset($this->data->{$key})) :

                // compare value
                if (strtoupper($this->data->{$key}) == strtoupper($firstArgument)) return $this->data->{$key};

            endif;

        endif;
    }

    // parse query
    private function parse_query(string $str)
    {
        // get individual request
        $str = ltrim($str, '?');
        $ind = explode("&", $str);

        $query = [];

        if (count($ind) > 0)
        {
            foreach ($ind as $i => $d)
            {
                $dd = explode("=", $d);
                $query[$dd[0]] = isset($dd[1]) ? $dd[1] : "";
                $dd = null;
            }
        }

        $ind = null;
        $str = null;

        return $query;
    }

    // can check next
    private function canCheckNext($val, array $args, string $keyIndex, $parsedIndex, $keyVal, &$checkNext, $seperator = 'and')
    {
        // do we have keyIndex
        if (isset($val->{$keyIndex})) :

            // get the value and compare
            $keyIndexValue = $val->{$keyIndex};

            // check if it matches with arguments at this index
            $bind = isset($args[$parsedIndex]) ? $args[$parsedIndex] : null;

            if (is_null($bind)) :
                // failed
                $checkNext = false;
                // set the error message
                FileDBClient::$runTimeErrors[$keyIndex] = 'Missing Bind for ' . $keyIndex;
            else:

                // is array
                if (is_array($bind)) :

                    // @var int $completed
                    $completed = 0;

                    // check array
                    foreach ($bind as $bindVal) :
                        // compare both
                        if (strtoupper($bindVal) == strtoupper($keyIndexValue)) :
                            // good
                            $completed++;
                            // break out
                            break;
                        endif;
                    endforeach;

                    // all failed ?
                    if ($completed == 0 && $seperator == 'and') : $checkNext = false; endif;
                    if ($completed > 0 && $seperator == 'or') : $checkNext = true; endif;

                else:
                    // compare both sides
                    if (strtoupper($keyIndexValue) != strtoupper($bind)) :
                        // failed
                        if ($seperator == 'and') $checkNext = false;
                    endif;

                    // handle for or
                    if ($seperator == 'or') :

                        if (strtoupper($keyIndexValue) == strtoupper($bind)) :
                            // add
                            $checkNext = true;
                        endif;

                    endif;  
                endif;
                
            endif;

        else:
            // stop
            if ($seperator == 'and') $checkNext = false;
        endif;
    }
}