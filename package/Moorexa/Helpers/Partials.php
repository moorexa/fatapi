<?php /** @noinspection ALL */

namespace Lightroom\Packager\Moorexa\Helpers;

use Exception;
use Lightroom\Common\File;
use Lightroom\Adapter\ClassManager;
use Lightroom\Exceptions\ClassNotFound;
use Lightroom\Templates\Happy\Web\Caching;
use Lightroom\Templates\Happy\Web\Interpreter;
use Lightroom\Packager\Moorexa\MVC\Helpers\ControllerLoader;
/**
 * @package Partials
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Partials
{
    /**
     * @var string outputOriginal
     */
    public $outputOriginal = '';

    /**
     * @var string InterpolateContent
     */
    public $InterpolateContent = '';

    /**
     * @var array partialArray
     */
    private $partialArray = [];

    /**
     * @var array $exportedVariables
     */
    private static $exportedVariables = [];

    /**
     * @method Partials partial
     * @param string $name
     * @param array $___data
     * @return mixed
     *
     * Load partial not as a directive
     * @throws Exception
     */
    public static function partial(string $name, array $___data=[])
    {
        // @var array $incomingUrl
        $incomingUrl = URL::getIncomingUri();

        // check from another controller
        $path = null;

        // partial directory
        $partialDirectory = null;

        // @var array $options
        $options = [];

        // get file name
        $filename = basename($name);
        $filename = preg_replace('/([^a-zA-Z\-\0-9|.])/', '', $filename);
        $namePath = $name;

        // get extension if sent.
        if (strpos($filename, '.') !== false) :
        
            // get extension
            $options = $filename;
        
        else:
        
            // load options
            $options = $filename.'.html';
            $namePath .= '.html';

        endif;

        // @var string $path
        if (is_file($namePath) && file_exists($namePath)) $path = $namePath;


        if ($path == null) :
        
            // check for an absolute path
            if (strlen($name) > 1 && $name[0] == '/') :
            
                $name = substr($name, 1);
                $path = $name;
            
            else:
            
                if ($name != '' && strlen($name) > 1) :
                
                    // check
                    if (strpos($name, '@') !== false) :
                    
                        $controller = substr($name, 0, strpos($name, '@'));

                        // remove dir from string
                        $name = strstr($name, '@');
                        $name = ltrim($name, '@');

                        if (is_dir($controller)) :
                        
                            // load path
                            $path = func()->deepscan($controller, $options);

                            // save directory
                            $partialDirectory = $controller;
                        
                        else:

                            $path = func()->deepscan(ControllerLoader::basePath() . '/'. ucfirst($controller) . ControllerLoader::config('directory', 'partial', $controller), $options);

                        endif;
                    
                    else:
                    
                        // update path
                        $path = $name;

                        if (!file_exists($name)) :

                            // get current controller
                            $controller = $incomingUrl[0];
                            
                            // get path
                            $path = func()->deepscan(ControllerLoader::basePath() . '/'. ucfirst($controller) . '/'.ControllerLoader::config('directory', 'partial', ucfirst($controller)).'/', $options);

                            // check main partials
                            if (is_null($path) || strlen($path) < 4) $path = func()->deepscan(func()->const('partial'), $options);

                        endif;

                    endif;

                endif;

            endif;

        endif;
        
        
        if ($path != '' && strlen($path) > 1 && file_exists($path))
        {
            // @var Partials $instance
            $instance = self::getInstance();

            // @var string $output
            $output = file_get_contents($path);

            // load partial event
            if (event()->canEmit('ev.partial.ready')) event()->emit('ev', 'partial.ready', [
                'name' => &$name,
                'path' => &$path,
                'data' => &$___data
            ]);

            // read markdown
            if (strpos(basename($path), '.md') !== false) self::readMarkDown($output);

            $useName = md5($path).'_'.basename($path);

            $other = null;

            // hash path
            if ($instance->cachePartial($output, $getPath, $useName) === false) :
            
                $instance->outputOriginal = $output;
                $instance->interpolate($output, $content, $useName
            );
                $instance->InterpolateContent = $content;

                // cache now
                $instance->cachePartial(null, $other, $useName);
                $instance->outputOriginal = null;
                $instance->InterpolateContent;

            endif;

            // load partial class if found.
            $__base = basename($path);
            $className = substr($__base, 0, strrpos($__base, '.'));
            $originalName = $className;
            $partialClassFile = rtrim($path, $__base) . $className . '.php';
            $className = ucwords(str_replace('-',' ',$className));
            $className = str_replace(' ','', $className);

            // partial class
            $partialClass = (object)['name' => $className, 'file' => $partialClassFile, 'originalName' => $originalName];

            if (file_exists($partialClass->file))
            {
                $_output = function() use ($___data, $getPath, $partialClass, $originalName)
                {
                    // extract vars

                    if (is_string($___data)) :
                    
                        // try convert to object
                        $__data = preg_replace('/[\']/','"',$___data);
                        $__obj = json_decode($__data);

                        if (is_object($__obj)) $___data = func()->toArray($__obj);
                        
                    endif;


                    include_once $partialClass->file;

                    // check for class
                    if (class_exists($partialClass->name)) :
                    
                        $__classname = strtolower($partialClass->name);
                        $__classname2 = $partialClass->name;
                        $ref = new \ReflectionClass($partialClass->name);

                        $ref = $ref->newInstanceArgs($___data);

                        // create instance
                        $$__classname = $ref;
                        $$__classname2 = $$__classname;
                        $ref = null;

                    endif;

                    // load exported variables
                    extract(self::getExportedVars($originalName));

                    // load from self passed variables
                    if (is_array($___data)) extract($___data);

                    // load cached file.
                    if ($getPath != null && file_exists($getPath)) include($getPath);
                };

                $output = call_user_func($_output);
            }
            else
            {

                $_output = function() use ($___data, $instance, $getPath, $partialClass, $originalName)
                {
                    // extract vars

                        if (is_string($___data)) :
                        
                            // try convert to object
                            $__data = preg_replace('/[\']/','"',$___data);
                            $__obj = json_decode($__data);

                            if (is_object($__obj)):
                            
                                $___data = func()->toArray($__obj);

                            endif;

                        endif;

                        // load exported variables
                        extract(self::getExportedVars($originalName));

                        // load from self passed variables
                        if (is_array($___data)) extract($___data);

                        $output = null;

                        // load cached file.
                        if ($getPath != null && file_exists($getPath)) include($getPath);

                        return $output;
                };

                $output = call_user_func($_output);
            }

            return $output;
        }
        else
        {
            if (strlen($name) > 1) :
            
                // throw exception
                $throw = true;

                if (env('bootstrap', 'autogenerate.partials') === true) :
                
                    $nameCopy = $name;

                    // get extension
                    $extension = explode('.', basename($name));
                    $extension = end($extension);

                    if ($extension == basename($name))  $nameCopy .= '.html';

                    // create if it doesn't exists
                    if ($nameCopy[0] != '/') :
                    
                        // get current controller
                        $controller = ucfirst($incomingUrl[0]);

                        // current dir
                        $directory = $partialDirectory != null ? $partialDirectory : ControllerLoader::basePath() . '/' . $controller . '/'. ControllerLoader::config('directory', 'partial', $controller) .'/';

                        // switch directory to main if not found
                        if (!is_dir($directory)) $directory = func()->const('partial') . '/';

                        // get basename
                        $nameBaseName = basename($nameCopy);

                        // remove base name from @var $nameCopy
                        $nameCopyClean = rtrim($nameCopy, $nameBaseName);

                        // check if @var $nameCopyClean is a directory
                        if (is_dir($nameCopyClean)) { $directory = $nameCopyClean; $nameCopy = $nameBaseName; }

                        // is @var $directory a directory ?
                        if (is_dir($directory)) :
                        
                            $throw = false;

                            // create partial.
                            if ($name[0] != '$') :
                            
                                File::write('#Partial Created', $directory.$nameCopy);

                                // load partial
                                return self::partial($name, $___data);

                            endif;
                        
                        endif;

                    endif;

                endif;

                // throw exception
                if ($throw) throw new Exception('Partial '.$name.' doesn\'t exists.');

            endif;
        }
        

        return null;
    }

    /**
     * @method Partials loadPartial
     * @param string $partialName
     * @return mixed
     *
     * Load partial as a directive
     * @throws Exception
     */
    public static function loadPartial(string $partialName)
    {
        // @var array arguments
        $arguments = func_get_args();

        // @var array partialArguments
        $partialArguments = array_splice($arguments, 1);

        // @var array newArguments
        $newArguments = $partialArguments;

        if (count($partialArguments) > 0) :

            if (is_array($partialArguments[0])) $newArguments = $partialArguments[0];

        endif;

        return self::partial($partialName, $newArguments);
    }

    /**
     * @method Partials cachePartial
     * @param string $content
     * @param string $cache_path
     * @param string $cache_name
     * @return bool
     * 
     * Check if partial has been cached previously, if not then cache.
     */
    public function cachePartial($content = null, &$cache_path = null, string $cache_name = '') : bool
    {
        $path = get_path(func()->const('storage'), '/Caches/Partials/partial.cache.php');

        $cache = include_once($path);

        // @var bool $cached
        $cached = false;

        $savePath = function(string $name)
        {
            return get_path(func()->const('storage'), '') . '/Caches/Partials/' . $name . '.cache';
        };

        if (is_array($cache)) :
        
            $this->partialArray = $cache;
        
        else:
        
            $cache = $this->partialArray;

        endif;

        $saveCache = function($name, $content) use ($savePath, $path)
        {
            // @var string $hash
            $hash = md5($this->outputOriginal);

            if (is_null($this->InterpolateContent)) $this->interpolate($content);

            $this->partialArray[$name] = $hash;

            ob_start();
            var_export($this->partialArray);
            $arr = '<?php'."\n";
            $arr .= 'return '. ob_get_contents() . ';'."\n";
            $arr .= '?>';
            ob_end_clean();

            File::write($arr, $path);

            File::write($content, $savePath(str_replace('/', '', $name)));
        };

        $cache_path = $savePath(str_replace('/','',$cache_name));

        if (!is_null($content)) :
        
            // @var string $hash
            $hash = md5($content);

            if (isset($cache[$cache_name])) :
            
                $hash2 = $cache[$cache_name];

                if ($hash2 == $hash) :
                
                    // cached previously
                    $cached = true;

                    // update cache path
                    $cache_path = $savePath(str_replace('/','',$cache_name));
                
                else:
                
                    // save cache.
                    if (strlen($content) > 0) $saveCache($cache_name, $content);

                endif;

            endif;
        
        else:
        
            // save cache
            if (strlen($this->InterpolateContent) > 0) $saveCache($cache_name, $this->InterpolateContent);

        endif;

        // return bool
        return $cached;
    }

    /**
     * @method Partials interpolate
     * @param string $data (reference)
     * @param string $content (reference)
     * @return string
     * @throws ClassNotFound
     */
    public function interpolate(string &$data, &$content = null, $cache_name = '') : string 
    {
        // decode data
        $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');

        // @var Interpreter $interpreter
        $interpreter = ClassManager::singleton(Interpreter::class);

        // update interpreter
        $interpreter->interpolateString = false;

        // @var string $interpolated
        $interpolated = '';

        if (count(Caching::$externalCachingEngines) == 0) :

            // update data
            $data = $interpreter->interpolateExternal($data, $interpolated);

        else:

            $cache_name = get_path(func()->const('storage'), '/Caches/Partials/' . $cache_name . '.cache');

            // parse file
            Caching::parseFromExternalEngine($cache_name, $data);
            
            // update interpolated
            $interpolated = $data;

        endif;

        // update content
        $content = $interpolated;

        // return string
        return $data;
    }

    /**
     * @method Partials readMarkDown
     * @param string $content (reference)
     * @return void
     */
    public static function readMarkDown(string &$content) : void
    {
        if (class_exists('Parsedown')) $content = \Parsedown::instance()->text($content);
    }

    /**
     * @method Partials exportVars
     * @param string $partialName
     * @param array $args
     */
    public static function exportVars(string $partialName, array $args) : void 
    {
        // can we create
        self::$exportedVariables = is_array(self::$exportedVariables) ? self::$exportedVariables : [];

        // merge with existing.
        self::$exportedVariables[$partialName] = array_merge($args, ((isset(self::$exportedVariables[$partialName]) && is_array(self::$exportedVariables[$partialName])) ? self::$exportedVariables[$partialName] : []));
    }

    /**
     * @method Partials getExportedVars
     * @param string $partialName
     * @return array
     */
    private static function getExportedVars(string $partialName) : array 
    {
        return isset(self::$exportedVariables[$partialName]) ? self::$exportedVariables[$partialName] : [];
    }

    /**
     * @method Partials getInstance
     * @return Partials
     * @throws ClassNotFound
     */
    private static function getInstance() : Partials
    {
        return ClassManager::singleton(static::class);
    }

}