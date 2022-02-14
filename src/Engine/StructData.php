<?php
namespace Engine;

/**
 * @package StructData
 * @author Amadi Ifeanyi <amadiify.com>
 */
class StructData
{
    // this would help rename struct data
    const RENAME = '#999#_r';

    // this would help replace struct data
    const REPLACE = '#929#_rE';

    // this would help remove struct data
    const REMOVE = '#901#_rM';

    // Struct flag used
    const STRUCT_FLAG_USED = '8744B6D6DSSHDYEEO0';
 
    // extract data
    public function __construct($data)
    {
        $this->extract($data);
    }

    /**
     * @method StructData extract
     * @param mixed $data
     * @return void
     */
    public function extract($data) : void
    {
        if (is_array($data) || is_object($data)) :

            // convert object to array
            $data = is_object($data) ? (array) $data : $data;

            // unpack data
            if (count($data) > 0) foreach ($data as $key => $value) :

                if (is_string($key)) $this->{$key} = $value;
                elseif (is_int($key) && is_string($value)) $this->{$value} = '';

            endforeach;

        endif;
    }

    // unpack data
    public function unpack($data) : StructData
    {
        // get clone
        $object = new StructData($data);

        if (is_array($data) || is_object($data)) :

            // access props
            foreach ($object as $key => $value) :

                // set data
                if (isset($this->{$key})) $object->{$key} = $this->{$key};

            endforeach;

        endif;

        // return instance
        return $object;
    }

    // read data
    public function __get(string $name)
    {
        if (property_exists($this, $name)) return $this->{$name};
    }

    // set data
    public function __set(string $name, $data)
    {
        $this->{$name} = $data;
    }

    // rename data
    public function renameData(array $options, string $flag = '')
    {
        if ($flag == StructData::STRUCT_FLAG_USED && !isset($options[StructData::RENAME])) return false;
        else if ($flag == StructData::STRUCT_FLAG_USED && isset($options[StructData::RENAME])) $options = $options[StructData::RENAME];

        // load array
        array_walk($options, function($new, $old){

            // check old data
            if (isset($this->{$old})) :

                // set new data
                $this->{$new} = $this->{$old};

                // remove old
                unset($this->{$old});

            endif;

        });
    }

    // replace data
    public function replaceData(array $options, string $flag = '')
    {
        if ($flag == StructData::STRUCT_FLAG_USED && !isset($options[StructData::REPLACE])) return false;
        else if ($flag == StructData::STRUCT_FLAG_USED && isset($options[StructData::REPLACE])) $options = $options[StructData::REPLACE];

        // load array
        array_walk($options, function($value, $key){

            // check key and set
            if (isset($this->{$key})) $this->{$key} = $value;

        });
    }

    // remove data
    public function removeData(array $options, string $flag = '')
    {

        if ($flag == StructData::STRUCT_FLAG_USED && !isset($options[StructData::REMOVE])) return false;
        else if ($flag == StructData::STRUCT_FLAG_USED && isset($options[StructData::REMOVE])) $options = $options[StructData::REMOVE];

        // load array
        array_walk($options, function($key, $index){

            // check key and set
            if (isset($this->{$key})) unset($this->{$key});

        });
    }

    // get all
    public function getData(array $options = []) : array
    {
        $object = $this;

        // get clone
        if (count($options) > 0) $object = clone $this;

        // rename data
        $object->renameData($options, StructData::STRUCT_FLAG_USED);

        // replace data
        $object->replaceData($options, StructData::STRUCT_FLAG_USED);

        // remove data
        $object->removeData($options, StructData::STRUCT_FLAG_USED);

        // loa data
        return ((array) $object);
    }
}