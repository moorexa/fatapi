<?php

/**
 *
 * @package Moorexa Validator
 * @author Ifeanyi Amadi <www.amadiify.com>
 * @version 0.0.1
 **/

class Validator
{
    // failed
    private $failed = [];

    // success
    private $success = [];

    // private post data
    private $data = [];

    // private type
    private $type;

    // error messages
    private $errors = [
        'min' => 'Invalid character length',
        'max' => 'Character too long. Maximum length is {:length}',
        'alpha' => 'Must contain alphabets and numbers only',
        'number' => 'Must contain numbers only',
        'text' => 'Cannot contain anything other than alphabets only',
        'notag' => 'Cannot contain tags.',
        'symbols' => 'Must contain one or more symbols',
        'has' => 'Must contain {:flag} "{:contain}"',
        'email' => 'Invalid email address',
        'url'  => 'Invalid URL address',
        'date' => 'Invalid Date format',
        'match' => 'Does not match {:match}',
        'regxp' => 'Pattern failed.',
        'required' => 'Field is required. Value must contain at least one character, number or symbol',
        'file' => 'Must be a file',
        '_filetype' => 'Invalid file type. Must be of type {:contain}'
    ];

    // custom errors
    public $customErrors = [];

    // skip required
    public $skipRequired = false;

    public function __construct($data)
    {
        $this->type = is_object($data) ? 'object' : 'array';
        $this->data = is_object($data) ? func()->toArray($data) : $data;
    }

    // regxp
    private function regxp(string $str, $regxp)
    {
        if (preg_match($regxp, $str))
        {
            return true;
        }

        return false;
    }

    // min
    private function min(string $str, $length = 2)
    {
        $len = mb_strlen($str);
        if ($len >= $length)
        {
            return true;
        }

        return false;
    }

    // file
    private function file(string $str)
    {
        if (isset($_FILES[$str]))
        {
            return true;
        }

        return false;
    }

    // file type
    private function _filetype(string $str, string $rule)
    {
        if ($this->file($str))
        {
            $type = isset($_FILES[$str]['type']) ? $_FILES[$str]['type'] : null;

            if (!is_null($type))
            {
                // get type from rule
                $rule = explode(',', $rule);

                // has length
                if (count($rule) > 0)
                {
                    $found = false;

                    // lets run a loop
                    foreach ($rule as $index => $tp)
                    {
                        $tp = strpos($tp, '/') === false ? 'image/'.$tp : $tp;
                        
                        if (strtoupper($tp) == strtoupper($type))
                        {
                            $found = true;
                            break;
                        }
                    }

                    return $found;
                }

                // no rule defined.
                // return true
                return true;
            }
        }

        return false;
    }

    // max
    private function max(string $str, $length = 200)
    {
        $len = mb_strlen($str);
        if ($len > $length)
        {
            return false;
        }

        return true;
    }

    // alpha numeric characters only
    private function alpha(string $str)
    {
        if (preg_match("/([^a-zA-Z0-9\s]+)/", $str))
        {
            return false;
        }
        else
        {
            if (preg_match('/([a-zA-Z\s])/', $str) == true || preg_match('/([0-9])/', $str) == true)
            {
                return true;
            }
        }

        return false;
    }

    // number 
    private function number(string $str)
    {
        if (preg_match('/([^0-9]+)/', $str))
        {
            return false;
        }

        return true;
    }

    // text => only letters
    private function text(string $str)
    {
        if (preg_match('/([^a-zA-Z\s]+)/', $str))
        {
            return false;
        }
        else
        {
            if (preg_match('/([a-zA-Z\s])/', $str) == true)
            {
                return true;
            }
        }

        return false;
    }

    // notag
    private function notag(string $str)
    {
        $decode = html_entity_decode($str);

        if (preg_match("/[<]([a-zA-Z]+\s?)([^>]+|)[>|]/m", $decode))
        {
            return false;
        }

        return true;
    }

    // required
    private function required(string $str)
    {
        if (!$this->skipRequired)
        {
            if (strlen($str) > 0)
            {
                return true;
            }

            return false;
        }

        return true;
    }

    // symbols
    private function symbols(string $str)
    {
        if (preg_match('/([^0-9a-zA-Z\s]+)/', $str))
        {
            return true;
        }

        return false;
    }

    // date 
    private function _date(string $val, string $format)
    {
        $format = trim(preg_replace("/\s*[(]/", '', $format));
        $format = rtrim($format, ')');
        $datetime = new \DateTime($val);
        $onFormat = $datetime->format($format);
        if (strcmp($val, $onFormat) === 0)
        {
            return true;
        }

        return false;
    }

    // has 
    private function has(string $str, $condition)
    {
        $condition = trim($condition);
        $as = null;
        
        switch($condition[0])
        {
            case '[':
                $as = 'or';
            break;

            case '{':
                $as = 'and';
            break;
        }
            
        $condition = substr($condition, 1, strlen($condition)-2);
        $split = str_split($condition);
        
        if ($as == 'or')
        {
            $total = count($split);
            $success = 0;

            foreach($split as $i => $r)
            {
                $reg = preg_quote("$r", '/');
                if (preg_match("/([$reg])/", $str))
                {
                    $success ++;
                }
            }

            if ($success > 0)
            {
                return true;
            }
        }
        else
        {
            $total = count($split);
            $success = 0;

            foreach($split as $i => $r)
            {
                $reg = preg_quote("$r", '/');
                if (preg_match("/([$reg])/", $str))
                {
                    $success ++;
                }
            }

            if ($total == $success)
            {
                return true;
            }

        }

        $contain = 'all of these ';

        if ($as == 'or')
        {
            $contain = 'any of these ';
        }

        $this->errors['has'] = str_replace('{:flag}', $contain, $this->errors['has']);

        return false;
    }

    // url
    private function url(string $str)
    {
        $url = filter_var($str, FILTER_VALIDATE_URL);

        if ($url !== false)
        {
            return true;
        }

        return false;
        
    }

    // email
    private function email(string $str)
    {
        if (preg_match("/[\s]/", $str))
        {
            return false;
        }
        else
        {
            if (preg_match("/[^a-zA-Z0-9\_\-\.\@]/", $str))
            {
                return false;
            }
            else
            {
                if (preg_match("/([^@]+)[@]([^.|@]+)[.]\w+/", $str))
                {
                    return true;
                }
            }
        }

        return false;
    }

    // match
    private function match(string $str, string $column)
    {
        if ($this->data[$column] != $str)
        {
            return false;
        }

        return true;
    }

    public function validate(array $array, &$errors = [], &$post = [])
    {
        if (is_array($this->data))
        {
            $private = ['validate', 'promise', '__construct'];

            foreach ($array as $string => $options)
            {
                $options = explode("|", $options);
                
                foreach($options as $i => $option)
                {
                    if (strpos($option, ':') !== false)
                    {
                        // get first position
                        $pos = strpos($option, ':');
                        $meth = substr($option, 0, $pos);
                        $other = substr($option, $pos+1);
                    }
                    else
                    {
                        $meth = $option;
                        $other = null;
                    }

                    // switch method
                    switch ($meth)
                    {
                        case 'filetype':
                            $meth = '_filetype';
                        break;
                    }

                    if (method_exists($this, $meth) && !in_array($meth, $private))
                    {
                        if (array_key_exists($string, $this->data) || isset($_FILES[$string]))
                        {
                            $data = isset($this->data[$string]) ? $this->data[$string] : $string;

                            if (!is_null($data))
                            {
                                $track = $this->{$meth}($data, $other);
                                
                                if ($track === false)
                                {
                                    if (isset($this->customErrors[$string.':'.$meth]))
                                    {
                                        $error = $this->customErrors[$string.':'.$meth];
                                    }
                                    else
                                    {
                                        $error = $this->errors[$meth];
                                    }

                                    $error = str_replace('{:length}', $other, $error);
                                    $error = str_replace('{:contain}', $other, $error);
                                    $error = str_replace('{:match}', $other, $error);

                                    $errors[$string][] = $error;
                                }
                                else
                                {
                                    $this->success[$string] = isset($this->data[$string]) ? $this->data[$string] : $_FILES[$string];
                                }
                            }
                            else
                            {
                                $errors[$string][] = 'Null value encountered. Rule ('.$meth.') didn\'t apply, validation failed';
                            }
                        }
                        else
                        {
                            $errors[$string][] = $string .' wasn\'t found. Rule ('.$meth.') didn\'t apply, validation failed';
                        }
                        
                    }
                }
            }

            if (count($errors) > 0)
            {
                if ($this->type == 'object')
                {
                    $this->data = func()->toObject($this->data);
                    $post = $this->data; 
                }

                return false;
            }

            if ($this->type == 'object')
            {
                $this->success = func()->toObject($this->success);
                $post = $this->success; 
            }

            return true;
        }

        return false;
        
    }
    
    public function __call($meth, $props)
    {
        if ($meth == 'date')
        {
            return call_user_func_array([$this, '_date'], $props);
        }
        elseif ($meth == 'filetype')
        {
            return call_user_func_array([$this, '_filetype'], $props);
        }

        return null;
    }
}