<?php
namespace Engine\Http;
/**
 * @package HTTP Kernel
 * @author Ifeanyi Amadi <amadiify.com>
 */
class Kernel
{
    /**
     * @var array $requestHeader
     */
    private static $requestHeader = [];

    /**
     * @var array $httpError
     */
    private static $httpError = [];

    /**
     * @var bool $resolved
     */
    private static $resolved = false;

    /**
     * @method Kernel resolve
     * @param int $responseCode
     * @param mixed $data
     * @return void
     */
    public static function resolve(int $responseCode, $data) : void 
    {
        // resolve response code
        http_response_code($responseCode);

        // call render
        app('screen')->render($data);
    }

    /**
     * @method Kernel systemHealthCheck
     * @param array $services
     * @return mixed
     */
    public static function systemHealthCheck(array $services)
    {
        // @var array $status
        $status = [];

        // create instance
        HttpRequest::createInstance();

        // base time
        $baseTime = time();

        // run array
        foreach ($services as $service => $endpoint) :

            // set a benchmark
            $rqtime = microtime(true);

            // send request.
            $request = HttpRequest::query()->sendRequest('get', $endpoint);

            // response time
            $rstime = microtime(true);

            // push status
            $status[$service] = [
                'request_time' => (string) round($rqtime - $baseTime, 2) . 'ms',
                'response_time' => (string) round(($rstime - $rqtime), 2) . 'ms',
                'status' => $request->status,
                'version' => isset($request->responseHeaders['x-service-version']) ? $request->responseHeaders['x-service-version'][0] : '0.0.1',
                'scheduled_maintenance' => isset($request->responseHeaders['x-service-maintenance']) ? $request->responseHeaders['x-service-maintenance'][0] : ''
            ];

            // update basetime
            $baseTime = time();

        endforeach;

        // send output
        return self::resolve(200, ['response' => json_encode(['services' => $status])]);
    }

    /**
     * @method Kernel loadService
     * @return mixed
     */
    public static function loadService(string $endpoint, string $service, string $contentType, array $headers = []) 
    {
        // load input
        self::input();

        // set the content type
        self::$requestHeader['Content-Type'] = $contentType;

        // pass services
        self::$requestHeader['x-referer'] = func()->url();

        // merge header
        self::$requestHeader = array_merge(self::$requestHeader, $headers);

        // set request header
        foreach (self::$requestHeader as $header => $value) HttpRequest::header([$header => $value]);

        // get the request method
        $requestMethod = isset($_SERVER['X-REQUEST-METHOD']) ? strtolower($_SERVER['X-REQUEST-METHOD']) : strtolower($_SERVER['REQUEST_METHOD']);

        // add service to endpoint
        $endpoint = rtrim($endpoint, '/') . '/' . $service;

        // unset __app_request__
        if (isset($_GET['__app_request__'])) unset($_GET['__app_request__']);

        // send request.
        $request = HttpRequest::multipart()->sendRequest($requestMethod, $endpoint);
            
        // exception throwned
        // we should prossibly log failed transactions.
        if (is_string($request)) return self::resolve(200, [
            'Status' => false,
            'Message' => strip_tags($request)
        ]);

        // get the response data
        if (is_object($request)) :

            // build response
            $response = [
                'Status' => $request->status != 200 ? false : true,
                'Data' => strtolower($contentType) == 'application/json' ? $request->json : $request->text
            ];

            // manage headers
            $headers = [];

            // convert keys to lower case
            foreach ($request->responseHeaders as $header => $value) $headers[strtolower($header)] = $value;

            // get the content type
            $contentType = isset($headers['content-type']) ? $headers['content-type'][0] : null;
            
            // no content type
            if ($contentType == null) return self::resolve(200, [
                'Status' => false,
                'Message' => 'Content Type missing in response header for service #{'.$service.'}'
            ]);

            // has seperator
            if (strpos($contentType, ';') !== false) :

                // fetch content type
                $contentType = substr($contentType, 0, strpos($contentType, ';'));

            endif;

            // does response content type match the requested one ?
            if ($contentType != self::$requestHeader['Content-Type']) return self::resolve(200, [
                'Status' => false,
                'Message' => 'Invalid Content Type in response header for service "'.$service.'". Expected #{'.self::$requestHeader['Content-Type'].'}, received #{'.$contentType.'}'
            ]);

            // check for gateway pipe from response header
            if (isset($headers['x-gateway-event-pipe'])) :

                // convert to an object
                $fh = fopen('pipes.txt', 'a+');
                fwrite($fh, $headers['x-gateway-event-pipe'][0] . ',');
                fclose($fh);

            endif;
            
            // send output
            self::resolve($request->status, $response);

        endif;
    }

    /**
     * @method Post input
     * @return array
     * 
     * Convert Content-Disposition to a post data
     */
	public static function input() : array
	{
        // @var string $input
        $input = file_get_contents('php://input');

        // continue if $_POST is empty
		if (strlen($input) > 0 && count($_POST) == 0 || count($_POST) > 0) :
		
			$postsize = "---".sha1(strlen($input))."---";

			preg_match_all('/([-]{2,})([^\s]+)[\n|\s]{0,}/', $input, $match);

            // update input
			if (count($match) > 0) $input = preg_replace('/([-]{2,})([^\s]+)[\n|\s]{0,}/', '', $input);

			// extract the content-disposition
			preg_match_all("/(Content-Disposition: form-data; name=)+(.*)/m", $input, $matches);

			// let's get the keys
			if (count($matches) > 0 && count($matches[0]) > 0)
			{
				$keys = $matches[2];
                
                foreach ($keys as $index => $key) :
                    $key = trim($key);
					$key = preg_replace('/^["]/','',$key);
					$key = preg_replace('/["]$/','',$key);
                    $key = preg_replace('/[\s]/','',$key);
                    $keys[$index] = $key;
                endforeach;

				$input = preg_replace("/(Content-Disposition: form-data; name=)+(.*)/m", $postsize, $input);

				$input = preg_replace("/(Content-Length: )+([^\n]+)/im", '', $input);

				// now let's get key value
				$inputArr = explode($postsize, $input);

                // @var array $values
                $values = [];
                
                foreach ($inputArr as $index => $val) :
                    $val = preg_replace('/[\n]/','',$val);
                    
                    if (preg_match('/[\S]/', $val)) $values[$index] = trim($val);

                endforeach;
                
				// now combine the key to the values
				$post = [];

                // @var array $value
				$value = [];

                // update value
				foreach ($values as $i => $val) $value[] = $val;

                // push to post
				foreach ($keys as $x => $key) $post[$key] = isset($value[$x]) ? $value[$x] : '';

				if (is_array($post)) :
				
					$newPost = [];

					foreach ($post as $key => $val) :
					
						if (preg_match('/[\[]/', $key)) :
						
							$k = substr($key, 0, strpos($key, '['));
							$child = substr($key, strpos($key, '['));
							$child = preg_replace('/[\[|\]]/','', $child);
							$newPost[$k][$child] = $val;
						
                        else:
						
                            $newPost[$key] = $val;
                            
						endif;
                    
                    endforeach;

                    $_POST = count($newPost) > 0 ? $newPost : $post;
                    
				endif;
			}
        
        endif;

        // return array
		return $_POST;
    }
}