<?php

use Classes\Cli\Assist;
use Classes\Cli\CliInterface;
use Symfony\Component\Yaml\Yaml;
use Lightroom\Exceptions\{
    ClassNotFound, InterfaceNotFound
};

// define GLOB_BRACE
if(!defined('GLOB_BRACE')) define('GLOB_BRACE', 128);

/**
 * @method Assist encryptAssist
 * @param string $data
 * @return string
 */
function encryptAssist(string $data) : string
{
    // secret key
    $key = 'Your encryption secret key';

    // encryption method
    $method = "AES-256-CBC";

    // encrypt level
    $level = 2;

    // moorexa key
    $secret_iv = 'c0033768e0b8968fc58b50cdca6852c46e4eda39f9153643726b3a120cfb7b09';

    // get key
    $key = hash('sha256', $key);

    // iv
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    // encrypt data;
    $encrypt = openssl_encrypt($data, $method, $key, 0, $iv);

    $encrypt = base64_encode(__seAssist($encrypt, $level));

    return $encrypt;
}

/**
 * @method Assist __seAssist
 * @param string $e
 * @param int $level
 * @return string
 */
function __seAssist(string $e, int $level) : string
{
    $d = serialize(strrev($e));

    if ($level != 0)
    {
        $level -= 1;
        $d = __seAssist($d, $level);
    }

    return $d;
}

/**
 * @method Assist decryptAssist
 * @param string $data
 * @return string
 */
function decryptAssist(string $data) : string
{
    // secret key
    $key = 'Your encryption secret key';

    // encryption method
    $method = "AES-256-CBC";

    // encrypt level
    $level = 2;

    // moorexa key
    $secret_iv = 'c0033768e0b8968fc58b50cdca6852c46e4eda39f9153643726b3a120cfb7b09';

    // get key
    $key = hash('sha256', $key);

    // iv
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    $dec = __deAssist(base64_decode($data), $level);

    $decrypt = openssl_decrypt($dec, $method, $key, 0, $iv);

    // call all listeners
    Assist::emitDecrypt($decrypt);

    return $decrypt;

}

/**
 * @method Assist __deAssist
 * @param string $e
 * @param int $level
 * @return string
 */
function __deAssist(string $e, int $level) : string 
{
    $d = strrev(unserialize($e));

    if ($level != 0)
    {
        $level -= 1;
        $d = __deAssist($d, $level);
    }

    return $d;
}

/**
 * @method Assist getAllFiles
 * @param string $directory
 * @return array
 */
function getAllFiles(string $directory) : array
{
    return ___allfiles($directory);
}

/**
 * @method Assist ___allfiles
 * @param string $dir
 * @return array
 */
function ___allfiles(string $dir) : array
{
    $file = [];

    $glob = glob(rtrim($dir, '/') .'/{,.}*', GLOB_BRACE);

    if (count($glob) > 0) :
    
        foreach ($glob as $p) :
        
            if (basename($p) != '.' && basename($p) != '..') :
            
                $p = preg_replace("/[\/]{2}/", '/', $p);

                if (is_file($p))
                {
                    $file[] = $p;
                }
                elseif (is_dir($p))
                {
                    $file[] = ___allfiles($p);
                }

            endif;

        endforeach;

    endif;

    $glob = null;

    return $file;
}

/**
 * @method Assist reduce_array
 * @param array $array
 * @return array
 */
function reduce_array(array $array) : array 
{
    $arr = [];
    return __reduceArray($array, $arr);
}

/**
 * @method Assist __reduceArray
 * @param array $array
 * @param array $arr
 * @return array
 */
function __reduceArray(array $array, array $arr) : array
{

    if (is_array($array))
    {
        foreach ($array as $a => $val)
        {
            if (!is_array($val))
            {
                $arr[] = $val;
            }
            else
            {
                foreach($val as $v => $vf)
                {
                    if (!is_array($vf))
                    {
                        $arr[] = $vf;
                    }
                    else
                    {
                        $arr = __reduceArray($vf, $arr);
                    }
                }
            }
        }
    }

    return $arr;
}

/**
 * @method Assist xucwords
 * @param string $text
 * @return string
 */
function xucwords(string $text) : string
{
    $textArray = explode(" ", $text);
    // remove first
    $firstElem = $textArray[0];
    $textArray = array_splice($textArray, 1);
    // convert textarray back to string
    $textArrayString = implode(" ", $textArray);
    // make text array string
    $textArrayString = ucwords($textArrayString);
    $textArray = explode(" ", $textArrayString);

    array_unshift($textArray, $firstElem);

    return implode(' ', $textArray);
}

/**
 * @method Assist get_methods
 * @param string $class
 * @return array
 */
function get_methods(string $class) : array
{
    // @var array $classMethod
    $classMethod = [];

    if (is_string($class) && class_exists($class)) :
    
        $ref = new \ReflectionClass($class);
        $methods = $ref->getMethods();

        $class = $ref->name;

        foreach ($methods as $Obj) :
        
            if ($Obj->class == $class) $classMethod[] = $Obj->name;
        
        endforeach;
    
    endif;

    // return array
    return $classMethod;
}


/**
 * @method Assist get_mime
 * @param string $filepath
 * @return string
 */
function get_mime(string $filepath) : string
{
    static $mimeTypes;

    // get types
    if (is_null($mimeTypes)) $mimeTypes = include_once 'MimeTypes.php';

    // remove query
    if (strpos($filepath, '?') !== false) :

        $filepath = substr($filepath, 0, strpos($filepath, '?'));
        
    endif;

    // get extension
    $fileArray = explode('.', $filepath);

    // extension
    $extension = strtolower(end($fileArray));

    // check for types
    return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : '';
}

/**
 * @method Assist bash
 * @param array $extenal
 * @param array $bash
 * @return array
 */
function bash(array $extenal, array $bash) : array 
{
    foreach ($extenal as $class) :

        // check if class exists
        if (!class_exists($class)) throw new ClassNotFound($class);

        // create reflection
        $reflection = new \ReflectionClass($class);

        // check interface
        if (!$reflection->implementsInterface(CliInterface::class)) throw new InterfaceNotFound($class, CliInterface::class);

        // external
        $extenal = call_user_func([$class, 'loadBash']);

        // merge with bash
        $bash = array_merge($bash, $extenal);

    endforeach;

    // load bash_script
    $bashScript = get_path(PATH_TO_KONSOLE, '/bash_scripts.yaml');

    // check if script exists
    if (file_exists($bashScript)) :

        // load yaml 
        $bashScript = class_exists(Yaml::class) ? Yaml::parseFile($bashScript) : null;

        // do we have an array
        if (is_array($bashScript)) :

            // @var array $scripts
            $scripts = [];

            // load all
            foreach ($bashScript as $alaise => $path) :

                // push now
                if (is_string($path)) $scripts[$alaise] = ['start' => function($next, $argv) use ($path){

                    $path = get_path_from_constant($path);

                    // include file if it exists
                    if (file_exists($path)) include_once $path;

                }];

            endforeach;

            // merge now
            $bash = array_merge($bash, $scripts);
            
        endif;

    endif;

    // return array
    return $bash;
}

function convertToReadableSize($size, &$sbase=null){
	$base = log($size) / log(1024);
	$suffix = array("Byte", "KB", "MB", "GB", "TB");
	$f_base = floor($base);
	$convert = round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];

	$sbase = strtolower($suffix[$f_base]);

	if ($convert > 0)
	{
		return $convert;
	}

	return 0 . 'KB';
}

/**
 * @method Assist command_helper
 * @param array $options
 * @return void 
 */
function command_helper(array $options) : void
{
    Assist::commandHelper($options);
}