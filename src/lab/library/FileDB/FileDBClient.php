<?php
namespace FileDB;

use Closure;
/**
 * @package FileDBClient
 * @author Amadi Ifeanyi <amadiify.com>
 */
class FileDBClient
{
    private static $baseDir = '/fdb/';
    private static $extension = '.fdb';
    private static $loadedData = null;
    private static $errorMessage = '';
    public  static $runTimeErrors = [];


    // load function wrapper
    public static function initFunctions()
    {
        // include FileDBFunctions.php
        include_once 'FileDBFunctions.php';
    }

    // load json data
    public static function load($data)
    {
        // instance
        static $instance;

        // reset data
        self::$loadedData = null;

        // create instance 
        if ($instance == null) $instance = new self;

        // load data
        if (is_string($data)) :

            // load json data
            $data = json_decode(trim($data));

            // is object
            if (is_object($data)) :
                // load data
                self::$loadedData = $data;
            else:
                // error encountered
                self::$errorMessage = 'Document not properly formatted.';
            endif;
            
        // load object
        elseif (is_object($data)) :

            // load data
            self::$loadedData = $data;
        
        // load array
        elseif (is_array($data)) :
        
            // load data
            self::$loadedData = json_decode(json_encode($data));

        else:

            // invalid document 
            self::$errorMessage = 'Invalid Document.';

        endif;

        // return instance
        return $instance;
    }

    // fetch from storage
    public static function get(string $target, $closure=null)
    {
        // load document;
        $isLoaded = self::loadDocument($target, $document);

        // extract document
        extract($document);

        // does table exists
        if ($isLoaded) :

            // we good ?
            if (is_object($tableData)) :

                // shift cursor forward
                $targetArray = array_splice($targetArray, $pointer);

                // are we loading all ?
                if (count($targetArray) == 0) :

                    // load closure
                    if ($closure !== null && is_callable($closure)) self::loadClosure($closure, $tableData);

                    // all done.
                    return self::success($table, $tableData);

                endif;

                // load some
                $data = $tableData;

                // run loop
                foreach ($targetArray as $child) :
                    $data = isset($data->{$child}) ? $data->{$child} : [];
                endforeach;

                // load closure
                if ($closure !== null && is_callable($closure)) self::loadClosure($closure, $data);

                // all done
                return self::success($table, $data);

            endif;

        endif;

        // failed
        return self::error($table, $errorMessage);
    }

    // load errors
    private static function error(string $table, string $message)
    {
        return new class ($table, $message)
        {
            public $status = 'error';
            public $code = 0;
            public $message = '';
            public $rows = 0;
            public $ok = false;
            public $timestamp = null;
            public $table = '';

            // constructor
            public function __construct(string $table, string $message)
            {
                $this->table = $table;
                $this->message = $message;
                $this->timestamp = time();
            }

            // getter 
            public function __get(string $name)
            {

            }

            // function call
            public function results()
            {
                return [];
            }

            // function call
            public function fetch()
            {
                return false;
            }
        };
    }

    // load success
    private static function success(string $table, $data)
    {
        if (!is_array($data) && !is_object($data)) return $data;

        return new class($data, $table)
        {
            use FileDBMethods;

            // properties
            public $status = 'success';
            public $table;
            public $code = 200;
            public $ok = true;
            public $timestamp;
            public $rows;
            public $errors = [];

            // constructor
            public function __construct($data, $table)
            {
                // set table
                $this->table = $table;

                // set data
                $this->data = $data;

                // set timestamp
                $this->timestamp = time();

                // set rows
                $this->rows = (is_array($data) ? count($data) : count(((array) $data)));

                // set runtime error
                $this->errors = FileDBClient::$runTimeErrors;
            }
        };
    }

    // load closure
    private static function loadClosure(Closure $closure, &$data)
    {
        // create class
        $class = new class($data){
            use FileDBMethods;
        };

        // load closure function
        call_user_func($closure, $class);

        // get data
        $data = $class->data;
    }

    // load document
    private static function loadDocument(string $target, &$document = [])
    {
        // @var string $errorMessage
        $errorMessage = self::$errorMessage;

        // @var object $tableData
        $tableData = null;

        // @var int $pointer
        $pointer = 0;

        // fetch table
        $targetArray = explode('.', $target);

        // reset runtime errors
        self::$runTimeErrors = [];

        // get json data if loaded
        if (is_object(self::$loadedData)) :

            // create temp tmp name
            $table = md5($target . time());

            // load table data
            $tableData = self::$loadedData;

            // clear
            self::$loadedData = null;

        else:

            // get the table
            $table = $targetArray[0];

            // get table file
            $tableFile = __DIR__ . self::$baseDir . $table . self::$extension;

            // load from config
            if (isset($_ENV['filedb'])) :

                // check for basedir if set
                if (isset($_ENV['filedb']['basedir'])) :

                    // get the base directory
                    $baseDir = get_path_from_constant($_ENV['filedb']['basedir']);

                    // @var string $extension
                    $extension = self::$extension;

                    // load extension
                    if (isset($_ENV['filedb']['extension']) && strlen($_ENV['filedb']['extension']) > 0) :

                        // get the extension
                        $extension = $_ENV['filedb']['extension'];
                        
                    endif;

                    // add . 
                    $extension = '.' . ltrim($extension, '.');

                    // replace table file
                    if (strlen($baseDir) > 3) :

                        // load table file
                        $tableFile = $baseDir . '/' . $table . $extension;

                    endif;

                endif;

            endif;

            // error message
            $errorMessage = 'Table does not exists.';

            // does table exists
            if (file_exists($tableFile)) :

                // read table
                $tableData = json_decode(file_get_contents($tableFile));

                // set pointer
                $pointer = 1;

                // not formatted ?
                if ($tableData == null) :

                    // update error message
                    $errorMessage = 'Document not properly formatted.';

                endif;

            endif;

        endif;

        // push to refrence
        $document = [
            'tableData' => $tableData, 
            'table' => $table, 
            'errorMessage' => $errorMessage, 
            'pointer' => $pointer,
            'targetArray' => $targetArray
        ];

        // all good ?
        return ($tableData === null) ? false : true;
    }
}