<?php

namespace Lightroom\Packager\Moorexa;

use Lightroom\Common\Directories;
use Lightroom\Core\Interfaces\PrivateAutoloaderInterface;
use Lightroom\Exceptions\JsonHandlerException;

/**
 * @package DirectoryAutoloader for Moorexa
 * @author amadi ifeanyi <amadiify.com>
 * 
 * This registers a directory for quick namespaces.
 * example.
 * 
 * -Lab
 *  - Account
 *     - Account.php
 * 
 * We can register lab directory and access Account.php via Account\Account.php
 */
trait DirectoryAutoloader
{
    // list of directories
    public $directories = [];

    /**
     * @method PrivateAutoloaderInterface autoloaderRequested
     * This method would be called by the FrameworkAutoloader during runtime.
     * You can implement your logic in this method and must return a boolean (true | false)
     * @param string $class
     * @return bool
     * @throws JsonHandlerException
     */
    public function autoloaderRequested(string $class) : bool
    {
        // class found
        /** @var bool $classFound */
        $classFound = false;

        // file exists in cache
        if (self::fileAutoloadPathPreviouslyCached($class)) :

            // get path from cache
            $path = self::getAutoloadPathFromCache();

            if (file_exists($path)) :

                include_once $path;

                // class found
                $classFound = true;

            endif;

        endif;
        

        if ($classFound === false) :

            /**@var string $filepath*/
            $filepath = '';

            // check directories
            foreach ($this->directories as $index => $directory) :
            
                // get base directory and file
                /** @var string $baseDirectory */
                list($baseDirectory, $file) = $this->getBaseDirectoryAndFile($directory, $class);

                // get file path
                $filepath = Directories::findFileFrom($baseDirectory, $file);

                // check for file existence
                if (strlen($filepath) > 2 && file_exists($filepath)) : 
                
                    // include path
                    include_once $filepath;

                    $classFound = true;

                    break;

                endif;

            endforeach;

            // directory autoload was a success, fire event if $filepath is not empty
            if ($filepath != '') :

                self::autoloaderCachingEvent('success', [
                    'path' => $filepath,
                    'class' => $class
                ]);

            endif;

        endif;

        // return bool
        return $classFound;
    }

    /**
     * @method DirectoryAutoloader getBaseDirectoryAndFile
     * @param string $directory
     * @param string $class
     * @return array
     */
    private function getBaseDirectoryAndFile(string $directory, string $class) : array 
    {
        // home keeping
        $directory = rtrim($directory, '/');
        $directory = rtrim($directory, '/*');

        // extract namespace form class
        $namespace = str_replace('\\','/',$class);
        $namespace = explode('/', $namespace);

        // get file
        $file = array_pop($namespace) . '.php';

        // get sub directory
        $subdirectory = Directories::findDirectory(HOME . $directory . '/', $namespace);

        // base directory
        $baseDirectory = $subdirectory !== '' ? $subdirectory : HOME . $directory . '/';

        return [$baseDirectory, $file];
    }
}