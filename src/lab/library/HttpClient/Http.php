<?php
namespace HttpClient;

use GuzzleHttp\Psr7\Request;
use function Lightroom\Requests\Functions\{get, post, files};
/**
 * @package A Simple HTTP Request Manager for our php applicqtion
 * @author Amadi ifeanyi <amadiify.com>
 */

class Http
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
    private $attachment = ['multipart'=>[],'query'=>[]];
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
        if (is_null(self::$client))
        {
            // @var array $config
            $config = [];
            
            // load envConfig
            self::loadEnvConfig($config);

            // load class, guzzle instance 
            self::$instance = (is_null(self::$instance)) ? new self : self::$instance; // create instance
            self::$client = new \GuzzleHttp\Client($config); // set client
        }
    }

    // load env configuration
    private static function loadEnvConfig(array &$config) : void 
    {
        // do we have httpClient config in env?
        if (isset($_ENV['httpClient'])) :

            // get client config
            $clientConfig = $_ENV['httpClient'];

            // push guzzle config if it exists
            $config = isset($clientConfig['guzzleConfig']) ? $clientConfig['guzzleConfig'] : $config;

            // load endpoint
            self::$endpoint = isset($clientConfig['endpoint']) ? $clientConfig['endpoint'] : self::$endpoint;

            // load default headers
            self::$headers = array_merge(self::$headers, (isset($clientConfig['headers']) ? $clientConfig['headers'] : []));

        endif;
    }

    // load constructor
    public function __construct()
    {
        self::$instance = $this;
        self::createInstance();
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
                'contents' => post()->has($data[0]) ? post()->get($data[0]) : $data[0],
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
                if (post()->has($key))
                {
                    self::$instance->attachment['multipart'][] = [
                        'name' => $key,
                        'contents' => post()->get($key)
                    ];
                }
            }
        }
        else
        {
            if (!post()->empty())
            {
                // get all post data
                $data = post()->input();

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
        if (count($data) == 1 && is_string($data[0])) :
        
            self::$instance->attachment['query'] = get()->has($data[0]) ? get()->get($data[0]) : $data[0];
        
        elseif (count($data) == 1 && is_array($data[0])) :
        
            self::$instance->attachment['query'] = http_build_query($data[0]);
        
        elseif (count($data) > 1) :
        
            $get = [];

            foreach ($data as $index => $key) if (get()->has($key)) $get[$key] = get()->get($key);

            if (count($get) > 0) : self::$instance->attachment['query'] = http_build_query($get); endif;
        
        else:
        
            self::$instance->attachment['query'] = http_build_query(get()->all());

        endif;
    }

    // add file
    private function addFileToRequest($data)
    {
        self::setHeader([
            'X-File-Agent' => 'Dropexpress Gateway GuzzleHttp'
        ]);

        // attach file
        call_user_func_array([self::$instance, 'attachFile'], $data);
    }

    // manage switch
    private static function manageSwitch($method, $data)
    {
        // get method
        switch (strtolower($method)) :
        
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
                call_user_func_array(static::class.'::setHeader', $data);
            break;

            default:
                return self::$instance->sendRequest($method, $data[0]);

        endswitch;

        // return instance
        return self::$instance;
    }

    // attach a file
    public function attachFile()
    {
        $files = func_get_args();

        if (count($files) == 0 && !files()->empty()) $files = array_keys(files()->all());

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

                    if (isset($_FILES[$file]))
                    {
                        $files = $_FILES[$file];

                        if (!is_array($files['name']))
                        {
                            // check tmp loc
                            if (strlen($files['tmp_name']) > 5) :

                                // get handle
                                $handle = fopen($files['tmp_name'], 'r');

                                // attach file
                                $this->attachment['multipart'][] = [
                                    'name'      => $file,
                                    'contents'  => $handle,
                                    'filename'  => $files['name']
                                ];
                            
                            endif;
                        }
                        else
                        {
                            foreach ($files['name'] as $index => $name)
                            {
                                // get handle
                                $handle = fopen($files['tmp_name'][$index], 'r');

                                // attach file
                                $this->attachment['multipart'][] = [
                                    'name'      => $file . '[]',
                                    'contents'  => $handle,
                                    'filename'  => $name
                                ];
                            }
                        }
                    }
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

        if (is_array($header)) :
        
            $current = array_merge($current, $header);
            self::$headers = $current;
        
        else:
        
            $args = func_get_args();
            $headers = [];

            foreach ($args as $index => $header) :
            
                $toArray = explode(':', $header);
                $key = trim($toArray[0]);
                $val = trim($toArray[1]);
                $headers[$key] = $val;
            
            endforeach;

            $current = array_merge($current, $headers);
            self::$headers = $current;
        
        endif;

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
        $http = new self;
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