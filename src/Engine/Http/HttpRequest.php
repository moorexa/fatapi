<?php
namespace Engine\Http;

use GuzzleHttp\Psr7\Request;

/**
 * @package Simple HTTP Request Handler for our Gateway
 * @author Amadi ifeanyi <amadiify.com>
 */

class HttpRequest
{
    // url
    public static $endpoint;
    // instance
    private static $instance;
    // client
    private static $client;
    // headers
    private static $headers = [];
    // trash
    private $trash = [];
    // files
    private $attachment = ['multipart'=>[], 'query'=>[]];
    // ready state
    private static $readyState = [];
    // using same origin
    private $usingSameOrigin = false;
    // same origin url
    private $sameOriginUrl = null;
    // same origin data
    public $sameOriginData = [];
    // same origin response
    private $sameOrginResponse = null;

    // create instance
    public static function createInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self; // create instance
            self::$client = new \GuzzleHttp\Client(['verify' => false ]); // set client
        }
    }
    
    // create request
    public static function __callStatic($method, $data)
    {
        // create instance
        self::createInstance();

        // switch
        return self::manageSwitch($method, $data);
    }

    // add body
    private function addBodyToRequest($data)
    {
        if (count($data) == 1 && is_string($data[0]))
        {
            self::$instance->attachment['multipart'][] = [
                'contents' => isset($_POST[$data[0]]) ? $_POST[$data[0]] : $data[0],
                'name' => $data[0]
            ];
        }
        elseif (count($data) == 1 && is_array($data[0]))
        {
            foreach ($data[0] as $key => $val)
            {
                self::$instance->attachment['multipart'][] = [
                    'name' => $key,
                    'contents' => $val
                ];
            }
        }
        elseif (count($data) > 1)
        {
            foreach ($data as $index => $key)
            {
                if (isset($_POST[$key]))
                {
                    self::$instance->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $_POST[$key]
                    ];
                }
            }
        }
        else
        {
            if (count($_POST) > 0)
            {
                $data = $_POST; 

                foreach ($data as $key => $val)
                {
                    self::$instance->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $val
                    ];
                }
            }
        }
    }

    // add params
    private function addQueryToRequest($data)
    {
        if (count($data) == 1 && is_string($data[0]))
        {
            self::$instance->attachment['query'] = isset($_GET[$data[0]]) ? $_GET[$data[0]] : $data[0];
        }
        elseif (count($data) == 1 && is_array($data[0]))
        {
            self::$instance->attachment['query'] = http_build_query($data[0]);
        }
        elseif (count($data) > 1)
        {
            $get = [];

            foreach ($data as $index => $key)
            {
                if (isset($_GET[$key]))
                {
                    $get[$key] = $_GET[$key];
                }
            }

            if (count($get) > 0)
            {
                self::$instance->attachment['query'] = http_build_query($get);
            }
        }
        else
        {
            self::$instance->attachment['query'] = http_build_query($_GET);
        }
    }

    // add file
    private function addFileToRequest($data)
    {
        self::setHeader([
            'X-File-Agent' => 'FATAPI Gateway GuzzleHttp'
        ]);

        // attach file
        call_user_func_array([self::$instance, 'attachFile'], $data);
    }

    // manage switch
    private static function manageSwitch($method, $data)
    {
        // get method
        switch (strtolower($method))
        {
            case 'attach':
            case 'attachment':
                self::$instance->addFileToRequest($data);
            break;

            case 'body':
                self::$instance->addBodyToRequest($data);
            break;

            case 'query':
                self::$instance->addQueryToRequest($data);
            break;

            case 'multipart':
                self::$instance->addFileToRequest($data);
                self::$instance->addBodyToRequest($data);
                self::$instance->addQueryToRequest($data);
            break;

            case 'header':
                // set header
                call_user_func_array('\Engine\Http\HttpRequest::setHeader', $data);
            break;

            default:
                return self::$instance->sendRequest($method, $data[0]);
        }

        // return instance
        return self::$instance;
    }

    // attach a file
    public function attachFile()
    {
        $files = func_get_args();

        if (count($files) == 0 && count($_FILES) > 0)
        {
            $files = array_keys($_FILES);
        }

        // check if file exists.
        array_walk($files, function($file, $key){
            if (is_string($file))
            {
                $key = 'file';

                if (file_exists($file))
                {
                    // create resource
                    $handle = fopen($file, 'r');
                    // get base 
                    $base = basename($file);
                    $key = substr($base, 0, strpos($base,'.'));

                    // add to attachment
                    $this->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => $handle,
                        'filename' => $base
                    ];
                }
                else
                {
                    // upload file
                    $uploadFile = function($file, $files, $filename='upload')
                    {
                        // create dir
                        $tmpdir = PATH_TO_STORAGE . 'Tmp/';

                        $key = $file;
                        // create destination and upload to a tmp directory
                        $name = $files['name'];
                        $tmpdir .= $name;
                        // move file
                        if (move_uploaded_file($files['tmp_name'], $tmpdir))
                        {
                            // get handle
                            $handle = fopen($tmpdir, 'r');

                            // attach file
                            $this->attachment['multipart'][] = [
                                'name' => $filename,
                                'contents' => $handle,
                                'filename' => $name
                            ];

                            // add to trash
                            $this->trash[] = $tmpdir;
                        }
                    };

                    if (isset($_FILES[$file]))
                    {
                        $files = $_FILES[$file];

                        if (!is_array($files['name']))
                        {
                            // upload file.
                            $uploadFile($file, $files, $file);
                        }
                        else
                        {
                            foreach ($files['name'] as $index => $name)
                            {
                                $build = ['name' => $name, 'tmp_name' => $files['tmp_name'][$index]];

                                // upload file
                                $uploadFile($file,$build,$file);
                            }
                        }
                    }
                }
                
            }
            elseif (is_array($file))
            {
                if ($this->usingSameOrigin === false)
                {
                    // upload file
                    $uploadFile = function($file, $files, $filename='upload')
                    {
                        // create dir
                        $tmpdir = PATH_TO_STORAGE . 'Tmp/';

                        $key = $file;
                        // create destination and upload to a tmp directory
                        $name = $files['name'];
                        $tmpdir .= $name;
                        // move file
                        if (move_uploaded_file($files['tmp_name'], $tmpdir))
                        {
                            // get handle
                            $handle = fopen($tmpdir, 'r');

                            // attach file
                            $this->attachment['multipart'][] = [
                                'name' => $filename,
                                'contents' => $handle,
                                'filename' => $key
                            ];

                            // add to trash
                            $this->trash[] = $tmpdir;
                        }
                    };

                    foreach ($file as $key => $v) :
                    
                        if (is_array($file[$key]['name'])) :
                        
                            foreach ($file[$key]['name'] as $i => $name) :
                            
                                $files = ['name' => $name, 'tmp_name' => $file[$key]['tmp_name'][$i]];
                                $uploadFile($name, $files, $key);

                            endforeach;
                        
                        elseif (is_string($file[$key]['name'])) :
                        
                            $files = ['name' => $file[$key]['name'], 'tmp_name' => $file[$key]['tmp_name']];
                            $uploadFile($file[$key]['name'], $files, $key);

                        endif;
                    
                    endforeach;
                }
            }
        });
    }

    // caller method
    public function __call($method, $data)
    {
        if (is_null(self::$instance))
        {
            self::$instance = $this;
        }

        // switch
        return self::manageSwitch($method, $data);
    }

    // check ready state
    public static function onReadyStateChange($callback)
    {
        self::$readyState[] = $callback;
    }

    // headers
    public function sendRequest($method, $path)
    {
        // inspect path
        $inspect = parse_url($path);

        // add path to url
        self::$headers['Request-URL'] = $path;

        // endpoint
        $endpoint = self::$endpoint;

        if ($this->sameOriginUrl === null)
        {
            if ($endpoint == '/')
            {
                if (!isset($inspect['scheme']))
                {
                    $path = url($path);
                }
            }
            else
            {
                if (!isset($inspect['scheme']))
                {
                    $path = rtrim($endpoint, '/') . '/' . $path;
                }
            }
        }
        else
        {
            $path = $this->sameOriginUrl . '/' . $path;
        }

        $client = self::$client;
        $headers = self::$headers;

        // set the content type
        $headers['Accept'] = isset($headers['Content-Type']) ? $headers['Content-Type'] : (isset($headers['Accept']) ? $headers['Accept'] : '*');
        unset($headers['content-type']);
        if (isset($headers['Content-Type'])) unset($headers['Content-Type']);

        // cookie jar
        $jar = new \GuzzleHttp\Cookie\CookieJar();

        // add request body
        $requestBody = [
            'headers' => $headers,
            'debug' => false,
            'jar' => $jar
        ];

        // merge 
        $requestBody = array_merge($requestBody, $this->attachment);

        // reset
        $this->attachment = [ 'multipart'=> [], 'query'=> [] ];
        

        // default response
        $response = null;

        try 
        {
            // send request
            $send = $client->request(strtoupper($method), $path, $requestBody);


            // response
            $response = new class ($send)
            {
                public $guzzle; // guzzle response
                public $status; // response status
                public $statusText; // response status
                public $responseHeaders; // response headers
                public $text; // response body text
                public $json; // response body json

                // constructor
                public function __construct($response)
                {
                    $this->guzzle = $response;
                    $this->status = $response->getStatusCode();
                    $this->responseHeaders = $response->getHeaders(); 
                    $this->statusText = $response->getReasonPhrase();

                    // get body
                    $body = $response->getBody()->getContents();
                    $this->text = $body;

                    // get json 
                    $json = is_string($body) ? json_decode($body) : null;

                    if (!is_null($json) && is_object($json))
                    {
                        $this->json = $json;
                    }
                }
            };
        }
        catch(\Throwable $exception)
        {
            $response = $exception->getMessage();
        }

        return $response;
    }

    // set header
    public static function setHeader($header)
    {
        $current = self::$headers;

        if (is_array($header))
        {
            $current = array_merge($current, $header);
            self::$headers = $current;
        }
        else
        {
            $args = func_get_args();
            $headers = [];

            foreach ($args as $index => $header)
            {
                $toArray = explode(':', $header);
                $key = trim($toArray[0]);
                $val = trim($toArray[1]);
                $headers[$key] = $val;
            }

            $current = array_merge($current, $headers);
            self::$headers = $current;
        }

        // clean up
        $current = null;
    }

    // get all headers
    public static function getHeaders()
    {
        $headers = getallheaders();
        $newHeader = [];

        $headers = array_merge($headers, self::$headers);

        foreach ($headers as $header => $value)
        {
            $newHeader[strtolower($header)] = $value;
        }   

        return $newHeader;
    }

    // has header
    public static function hasHeader(string $header, &$value=null) :bool
    {
        $headers = self::getHeaders();

        if (isset($headers[strtolower($header)]))
        {
            $value = $headers[strtolower($header)];

            return true;
        }

        return false;
    }

    // create same origin
    public static function sameOrigin($callback = null)
    {
        // create object
        $http = new Http;
        $http->sameOriginUrl = false; // app url
        $http->usingSameOrigin = true;

        $sameOrginResponse = function(&$http)
        {
            return new class($http){
                public $status = 0;
                public $json = null;
                public $text = null;
    
                public function __construct($http)
                {
                    $sameOriginData = $http->sameOriginData;
    
                    if (isset($sameOriginData['status']))
                    {
                        // set status
                        $this->status = $sameOriginData['status'];
                        // set text response
                        $this->text = $sameOriginData['text'];
                        // set json
                        $this->json = $sameOriginData['json'];
                    }
                }
            };
        };

        if (is_callable($callback) && !is_null($callback))
        {
            // call closure function
            call_user_func_array($callback, [&$http]);

            return call_user_func_array($sameOrginResponse, [&$http]);
        }
        else
        {
            $http->sameOrginResponse = $sameOrginResponse;
        }

        return $http;
    }
}