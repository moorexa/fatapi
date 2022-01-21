<?php
namespace Engine;

use Closure;
use Engine\Request;
use Lightroom\Requests\Filter;
use Lightroom\Router\Middlewares;
use Lightroom\Adapter\ClassManager;
use Engine\Interfaces\ResourceInterface;
use function Lightroom\Requests\Functions\{post, headers};
/**
 * @package MetaDataService
 * @author Amadi Ifeanyi <amadiify.com>
 */

class MetaDataService
{
    /**
     * @var string $version
     */
    public static $version = '';

    /**
     * @var string $defaultMethod
     */
    public static $defaultMethod = 'Init';

    /**
     * @var string $filter
     */
    public static $filter = 'required|string|notag|min:2';

    /**
     * @var string $param
     */
    private static $param = '';

    /**
     * @var string $baseApiFolder
     */
    private static $baseApiFolder = FATAPI_BASE . '/Resources/';

    /**
     * @method MetaDataService Create
     * @return mixed
     */
    public static function Create()
    {
        // Set the default version
        if (self::$version == '') self::$version = func()->finder('version');

        // load middleware for verb
        self::VerbHasMiddleware(function(){

            // load service if meta data does exists.
            self::loadMetaData();

        });
    }

    /**
     * @method MetaDataService CreateWithVersion
     * @param string $version
     * @return mixed
     */
    public static function CreateWithVersion(string $version)
    {
        // Set the version
        self::$version = '-'.$version;

        // run create
        self::Create();
    }

    /**
     * @method MetaDataService loadDocumentation
     * @param string $service
     * @param string $requestMethod
     * @return mixed
     */
    public static function loadDocumentation(string $service, string $requestMethod, string $method)
    {
        // service class
        $serviceClass = ucfirst($service);

        // load path
        $path = self::$baseApiFolder . $serviceClass . '/' . self::$version . '/Documentation/' . $requestMethod . $service . '.md';

        // check if version exists
        if (!is_dir(self::$baseApiFolder . $serviceClass . '/' . self::$version)) return self::versionDoesNotExistsForDoc();

        // update method
        $method = self::cleanServiceMethod($method);

        // check for method
        if ($method != '') $path = self::$baseApiFolder . $serviceClass . '/' . self::$version . '/Documentation/' . $serviceClass . '/' . $requestMethod . $method . '.md';

        // check in docs
        // if (!file_exists($path)) return self::noDocumentationFound($serviceClass, $method, $requestMethod);

        // parse now
        self::parseMarkdownToHTML($path, $service, $requestMethod, $method);
    }

    /**
     * @method MetaDataService loadHelpDocumentation
     * @param string $service
     * @param string $requestMethod
     * @return mixed
     */
    public static function loadHelpDocumentation(string $service, string $requestMethod)
    {
        // service class
        $serviceClass = ucfirst($service);

        // load path
        $path = FATAPI_BASE . '/Verb/' . strtoupper($requestMethod) . '/' . $requestMethod . $service . '.md';

        // check in docs
        // if (!file_exists($path)) return self::noDocumentationFound($serviceClass, '', $requestMethod);

        // parse now
        self::parseMarkdownToHTML($path, $service, $requestMethod);
    }

    /**
     * @method MetaDataService setParamv
     * @param string $param
     * @return void
     */
    public static function setParam(string $param) : void
    {
        // make an array
        if (strpos($param, '/') !== false) $param = explode('/', $param);

        // set param
        self::$param = $param;
    }

    /**
     * @method MetaDataService loadMetaData
     * @param array $data 
     * @return mixed
     */
    private static function loadMetaData()
    {
        $filter = filter('post', [
            'service'   => self::$filter,
            'method'    => [self::$filter, self::$defaultMethod],
            'id'        => ['string|required|notag', headers()->has('x-meta-id') ? headers()->get('x-meta-id') : self::$param],
        ]);

        // service wasn't found
        if (self::doesntExistsInHeaderEither($filter)) return self::noServiceFound();

        // get the request method
        $requestMethod = ucfirst(strtolower($_SERVER['REQUEST_METHOD']));

        // update service
        $service = $filter->service = ucfirst($filter->service);

        // load service version 
        self::loadServiceVersion($requestMethod, $filter);

        // set the param
        if (self::$param == '') self::setParam($filter->id);

        // has documentation request?
        if (post()->has('doc') || headers()->has('x-meta-doc')) return self::loadDocumentation($filter->service, $requestMethod, $filter->method);

        // create class
        $className = $requestMethod . $service;

        // build version path
        $versionPath = self::$baseApiFolder . $service . '/' . self::$version;

        // check if version exists
        if (!is_dir($versionPath)) return self::versionDoesNotExists($service);

        // build path
        $path = $versionPath . '/' . $className . '.php';

        // check for config for external services
        $configPath = $versionPath . '/config.json';

        // check if file does exists
        if (file_exists($configPath)) :

            // load json data
            $configuration = json_decode(file_get_contents($configPath));

            // config set to default.
            if (is_object($configuration) && $configuration->default == true) 
                return self::loadFromConfiguration($configuration, $filter);
            
        endif;

        // flag invalid request method
        if (self::flagInvalidRequestMethod() == false) return false;

        // check if service exists
        if (!file_exists($path)) return self::serviceNotFound($service . '/' . $className);

        // load path
        include_once $path;

        // get namespace
        $classNameSpace = 'Resources\\' . $service . '\\' . self::$version . '\\' . $className;

        // check if class exists
        if (!class_exists($classNameSpace)) return self::serviceNotFound($className);

        // create instance
        $class = ClassManager::singleton($classNameSpace);

        // load method
        $filter->method = self::cleanServiceMethod($filter->method);

        // attach middleware for resource
        self::applyResourceMiddleware($class, $filter->method, function() use (&$filter, &$class, &$requestMethod){

            // get request
            if ($requestMethod == 'Get') $filter->method .= (self::$param != '' && is_string(self::$param) && filter(['id' => self::$param], [
                'id' => 'required|number|min:1'
            ])->isOk() ? 'ById' : '');

            // check if method exists
            if (!method_exists($class, $filter->method)) return self::serviceMethodNotFound($filter->method);

            // check for @middleare flag for this method
            self::hasMiddlewareFlagForMethod($class, $filter->method, function() use (&$filter, &$class){

                // load from request
                Request::loadResource($filter, $class, self::$param);

            });

        });
    }

    /**
     * @method MetaDataService noServiceFound
     * @return void
     */
    private static function noServiceFound() : void
    {
        app('screen')->render([
            'Status' => false,
            'Message' => 'Missing MetaData Service in request body/header.'
        ]);
    }

    /**
     * @method MetaDataService noDocumentationFound
     * @param string $service
     * @return void
     */
    private static function noDocumentationFound(string $service, string $method, string $requestMethod) : void
    {
        app('screen')->render([
            'Status' => false,
            'Message' => 'Missing Documentation for this service "'.$requestMethod . ($method != self::$defaultMethod && $method != '' ? (ucfirst($method)) : $service).'", Please contact admistrator for help'
        ]);

        die;
    }

    /**
     * @method MetaDataService serviceNotFound
     * @param string $path
     * @return void
     */
    private static function serviceNotFound(string $path) : void
    {
        app('screen')->render([
            'Status' => false,
            'Message' => 'Could not load service handler "'.$path.'", Please contact API provider or administrator'
        ]);
    }

    /**
     * @method MetaDataService serviceMethodNotFound
     * @param string $method
     * @return void
     */
    private static function serviceMethodNotFound(string $method) : void
    {
        app('screen')->render([
            'Status' => false,
            'Message' => 'Could not load service handler method "'.$method.'", Please contact API provider or administrator'
        ]);
    }

    /**
     * @method MetaDataService loadServiceVersion
     * @param string $method 
     * @param $filter
     * @return void
     */
    private static function loadServiceVersion(string $method, $filter) : void
    {
        // get version path
        $versionPath =  self::$baseApiFolder. '/versioning.json';

        // continue if version path does exists
        if (file_exists($versionPath) && self::$version[0] != '-') :

            // load version 
            $version = json_decode(file_get_contents($versionPath));

            // update method
            $method = strtoupper($method);

            // update service
            $service = strtolower($filter->service) . ($filter->method != self::$defaultMethod ? '.' . strtolower($filter->method) : '');

            // load verbs
            $verbs = $version->Verbs;

            // prev version
            $prevVersion = self::$version;

            // load method
            if (isset($verbs->{$method})) :

                // load service 
                if (isset($verbs->{$method}->{$service}) && isset($verbs->{$method}->{$service}->version)) :

                    // load version
                    $version = $verbs->{$method}->{$service}->version;

                    // update global version
                    self::$version = $version != '' ? $version : self::$version;

                endif;

            endif;

            // load resources
            $resources = $version->Resources;

            // can we check
            if ($prevVersion == self::$version) :

                foreach ($resources as $resourceName => $versioning) :

                    // check resource name
                    if (strtolower($resourceName) == strtolower($filter->service)) :
                        
                        // set version
                        self::$version = $versioning->version;

                        // break
                        break;
                        
                    endif;

                endforeach;

            endif;

        endif;

        // clean up version
        if (self::$version[0] == '-') self::$version = substr(self::$version, 1);

    }

    /**
     * @method MetaDataService versionDoesNotExistsForDoc
     * @return void
     */
    private static function versionDoesNotExistsForDoc() : void
    {
        app('screen')->render([
            'Status'    => false,
            'Message'   => 'Could not load verison "'.self::$version.'" for documentation'
        ]);

        die;
    }

    /**
     * @method MetaDataService versionDoesNotExists
     * @param string $service
     * @return void
     */
    private static function versionDoesNotExists(string $service) : void
    {
        app('screen')->render([
            'Status'    => false,
            'Message'   => 'Could not load verison "'.self::$version.'" for service "'.$service.'"'
        ]);
    }

    /**
     * @method MetaDataService doesntExistsInHeaderEither
     * @param Filter $filter
     * @return bool
     */
    private static function doesntExistsInHeaderEither(&$filter=null) : bool
    {
        /**
         * @var string $service
         */
        $service = '';

        /**
         * @var string $method
         */
        $method = self::$defaultMethod;

        /**
         * @var string $id
         */
        $id = self::$param;

        /**
         * @var bool $cantContinue
         */
        $cantContinue = false;

        // get post
        $post = post();

        // get header
        $header = headers();

        // check for service
        if ($header->has('x-meta-service')) $service = $header->get('x-meta-service');

        // check for method
        if ($header->has('x-meta-method')) $method = $header->get('x-meta-method');

        // check for id
        if ($header->has('x-meta-id')) $id = $header->get('x-meta-id');

        // run filter
        if (!$filter->isOk()) :

            // update
            $cantContinue = true;

            // load filter
            $filter = filter(['service' => $service, 'method' => $method], [
                'service'   => [self::$filter, ($post->has('service') ? $post->service : $service)],
                'method'    => [self::$filter, ($post->has('method') ? $post->method : $method)],
                'id'        => ['string|required|notag', ($post->has('id') ? $post->id : $id)]
            ]);

            // are we good
            if ($filter->isOk()) $cantContinue = false;

        endif;

        // return bool
        return $cantContinue;
    }

    /**
     * @method MetaDataService parseMarkdownToHTML
     * @param string $path
     * @param string $service
     * @param string $requestMethod
     * @return void
     */
    private static function parseMarkdownToHTML(string $path, string $service, string $requestMethod, string $method = '') : void
    {
        // change content type
        header('Content-Type: text/html');

        // use github markdown
        $parser = new \cebe\markdown\GithubMarkdown();
        
        // Load template
        $template = file_get_contents(FATAPI_BASE . '/Static/style/template.html');
        
        // change title
        $template = str_replace('{TITLE}', $requestMethod . ucfirst($service) . '::service', $template);

        // add css link
        $template = str_replace('{LINK}', func()->url(FATAPI_BASE . '/Static/style/markdown.css'), $template);
        $template = str_replace('{LINK2}', func()->url(FATAPI_BASE . '/Static/style/highlight.css'), $template);

        // add javascript link
        $template = str_replace('{JAVASCRIPT}', func()->url(FATAPI_BASE . '/Static/style/highlight.js'), $template);

        // read markdown
        if ($method != '') $template = str_replace('{MARKDOWN}', $parser->parse(self::loadDocComment($service, $requestMethod, $method)) . "\n\n{MARKDOWN}", $template);

        // add markdown
        $template = file_exists($path) ?  str_replace('{MARKDOWN}', $parser->parse(file_get_contents($path)), $template) : $template;

        // remove markdown
        $template = str_replace('{MARKDOWN}', '', $template);

        // add highlight js
        $template = str_replace('code class="', 'code class="hljs ', $template);

        // view now
        echo $template;
    }

    /**
     * @method MetaDataService loadDocComment
     * @param string $service
     * @param string $requestMethod
     * @param string $method
     */
    private static function loadDocComment(string $service, string $requestMethod, string $method)
    {
        // load 
        $class = \Moorexa\Framework\Doc::class;

        // @var bool
        $docInstalled = false;

        // build path
        $path = defined('CONTROLLER_ROOT') ? CONTROLLER_ROOT . '/Doc/main.php' :  APPLICATION_ROOT . '/app/Doc/main.php';

        // check if path exists
        if (file_exists($path)) :

            // load now
            include_once $path;

            // class class
            if (class_exists($class)) $docInstalled = true;

        endif;

        // check if class exists
        if ($docInstalled) :

            $doc = ClassManager::singleton($class);

            // return doc
            return $doc->loadInlineDocumentation($service, $requestMethod, $method, self::$version);

        else:

            return '<pre><code>You can not see a complete documentation until you install <b>FatApi documentation plugin</b>. You need to visit our marketplace or click on the link to proceed <a href="https://fatapi.org/">fatapi.org</a></code></pre>';

        endif;
    }

    /**
     * @method MetaDataService loadFromConfiguration
     * @param object $configuration
     * @param Filter $filter
     * @return mixed
     */
    private static function loadFromConfiguration(object $configuration, Filter $filter)
    {
        // build service and method
        $route = $filter->service . ($filter->method != 'init' ? ('.' . $filter->method) : '');

        // can continue
        $continue = false;

        // get config data
        $configData = new class(){
            public $endpoint;
            public $responseType;
            public $method = 'GET';
            public $body = null;
        };  

        // process config
        config:
            if ($continue == true) :

                // get all headers
                $headers = headers()->all();

                // remove header_list
                if (isset($headers['header_list'])) unset($headers['header_list']);

                // set header
                headers()->set('x-Request-Url', $configData->endpoint);

                // get the method
                $requestMethod = isset($_SERVER['X-REQUEST-METHOD']) ? strtolower($_SERVER['X-REQUEST-METHOD']) : strtolower($_SERVER['REQUEST_METHOD']);

                // are we clear on the request method ?
                if ($requestMethod != strtolower($configData->method)) return app('screen')->render([
                    'Status'    => false,
                    'Message'   => 'Invalid request method "'.strtoupper($requestMethod).'" to external service "'.$configData->endpoint.'". Expecting request method "'.$configData->method.'"'
                ]);

                // are we clear on filter
                if (is_object($configData->body)) :

                    // run filter
                    $filter = filter($requestMethod, (array) $configData->body);

                    // did it pass ?
                    if (!$filter->isOk()) return app('screen')->render([
                        'Status'    => false,
                        'Message'   => 'We could not process your request after trying to validate the data sent. See what you missed in the request body below.',
                        'Body'      => $configData->body
                    ]);

                endif;
                
                // send request
                Http\Kernel::loadService($configData->endpoint, str_replace('.', '/', $route), $configData->responseType, $headers);

            endif;

        if ($continue === false && $configuration->type == 'api') :

            // has route?
            if (isset($configuration->routes) && isset($configuration->routes->{$route})) :

                // get route data
                $routeData = $configuration->routes->{$route};

                // is object
                if (is_object($routeData)) :

                    // update configuration url
                    if (isset($routeData->url)) $configuration->url = $routeData->url;

                    // has type
                    if (isset($routeData->responseType)) $configuration->response->type = $routeData->responseType;

                    // update route
                    $route = '';

                    // has method
                    if (isset($routeData->method)) $configData->method = strtoupper($routeData->method);

                    // has body to filter
                    if (isset($routeData->body)) $configData->body = $routeData->body;

                endif;

            endif;

            // load general configuration
            $configData->endpoint = $configuration->url;
            $configData->responseType = $configuration->response->type;

            // can continue
            $continue = true;

            // goto label
            goto config;

        endif;

    }

    /**
     * @method MetaDataService flagInvalidRequestMethod
     * @return void
     */
    private static function flagInvalidRequestMethod() : bool
    {
        /**
         * @var bool $passed
         * */ 
        $passed = true;

        // get the current request method
        $requestMethod = isset($_SERVER['X-REQUEST-METHOD']) ? strtolower($_SERVER['X-REQUEST-METHOD']) : strtolower($_SERVER['REQUEST_METHOD']);

        // check now
        if ($requestMethod != 'get' && $requestMethod != 'post') :

            // flag
            app('screen')->render([
                'Status'    => false,
                'Message'   => 'Thanks, we do not appricate a "'.strtoupper($requestMethod).'" request method on a non external service. We are using a REST API style that supports GET and POST request methods without dening you of every other cool stuffs REST has to offer. See documentation for GET and POST, or contact developer ['.func()->finder('developer').'] for more information'
            ]);

            // update passed
            $passed = false;

        endif;

        // return bool
        return $passed;
    }

    /**
     * @method MetaDataService cleanServiceMethod
     * @param string $method
     * @return string
     */
    private static function cleanServiceMethod(string $method) : string
    {
        // Remove '-'
        $method = str_replace('-', ' ', $method);

        // camelcase next
        $method = ucwords($method);

        // trim off spaces
        $method = preg_replace('/[\s]+/', '', $method);

        // return string
        return $method;
    }

    /**
     * @method MetaDataService VerbHasMiddleware
     * @param Closure $callback
     * @return void
     */
    private static function VerbHasMiddleware(Closure $callback)
    {
        // load the middleware json file
        $middleware = json_decode(file_get_contents(self::$baseApiFolder . '/middleware.json'));

        // is not an object ? then we just load callback
        if (!is_object($middleware)) return call_user_func($callback);

        // get verbs
        $verbs = $middleware->verbs;

        // get the request method
        $method = strtoupper($_SERVER['REQUEST_METHOD']);

        // does not have request method 
        if (!isset($verbs->{$method})) return call_user_func($callback);

        // load middleware array
        $middlewares = $verbs->{$method};

        // create array
        if (!is_array($middlewares)) $middlewares = [$middlewares];

        // middleware passed
        $middlewarePassed = 0;

        // run loop
        array_map(function($middleware) use (&$middlewarePassed){

            // apply middleware and update counter
            $middlewarePassed += (Middlewares::apply($middleware, [])) ? 1 : 0;

        }, $middlewares);

        // are we good ?
        if ($middlewarePassed == count($middlewares)) return call_user_func($callback);

        // request altered??
        if (ob_get_contents() == '') ClassManager::singleton(\Engine\Response::class)
        ->failed('At this point we can only tell that one or more middlewares failed for this request method.');
    }

    /**
     * @method MetaDataService applyResourceMiddleware
     * @param ResourceInterface $resource
     * @param string $method
     * @param Closure $callback
     * @return mixed
     */
    private static function applyResourceMiddleware(ResourceInterface $resource, string $method, Closure $callback)
    {
        // load the middleware json file
        $middleware = json_decode(file_get_contents(self::$baseApiFolder . '/middleware.json'));

        // is not an object ? then we just load callback
        if (!is_object($middleware)) return call_user_func($callback);

        // get resources
        $resources = $middleware->resources;

        // get the resource class 
        $resourceClass = get_class($resource);

        // get class name and append method
        $resourceName = $resourceClass . '::' . $method;

        // check if resource does exists in resources array
        $resourceList = isset($resources->{$resourceName}) ? $resources->{$resourceName}
                                                           : (isset($resources->{$resourceClass}) ? $resources->{$resourceClass} : null);

        // not null?
        if ($resourceList === null) return call_user_func($callback);

        // is array or string
        $resourceList = is_array($resourceList) ? $resourceList : [$resourceList]; 

        // middleware passed
        $middlewarePassed = 0;

        // run loop
        array_map(function($middleware) use (&$middlewarePassed){

            // apply middleware and update counter
            $middlewarePassed += (Middlewares::apply($middleware, [])) ? 1 : 0;

        }, $resourceList);

        // are we good ?
        if ($middlewarePassed == count($resourceList)) return call_user_func($callback);

        // request altered??
        if (ob_get_contents() == '') ClassManager::singleton(\Engine\Response::class)
        ->failed('At this point we can only tell that these "'.implode(', ', $resourceList).'" middlewares failed for the resource class or method.');

    }

    /**
     * @method MetaDataService hasMiddlewareFlagForMethod
     * @param ResourceInterface $resource
     * @param string $method
     * @param Closure $callback
     * @return mixed
     */
    private static function hasMiddlewareFlagForMethod(ResourceInterface $resource, string $method, Closure $callback)
    {
        // load reflection method
        $reflectionMethod = new \ReflectionMethod($resource, $method);

        // get comment
        $comment = $reflectionMethod->getDocComment();

        // free up
        $reflectionMethod = null;

        // has middleware
        if (strpos($comment, '@middleware') !== false) :

            // find all middlwares
            preg_match_all('/(@middleware)[\s]{1,}([^\n]+)/', $comment, $middlewares);

            if (isset($middlewares[2])) :

                // middleware passed
                $middlewarePassed = 0;

                // run loop
                array_map(function($middleware) use (&$middlewarePassed){

                    // apply middleware and update counter
                    $middlewarePassed += (Middlewares::apply(trim($middleware), [])) ? 1 : 0;

                }, $middlewares[2]);

                // are we good ?
                if ($middlewarePassed == count($middlewares[2])) return call_user_func($callback);

                // request altered??
                if (ob_get_contents() == '') : ClassManager::singleton(\Engine\Response::class)
                ->failed('At this point we can only tell that these "'.implode(', ', $middlewares[2]).'" middlewares failed for the resource method "'.$method.'".');
                endif;

            else:

                // program error, no middleware found
                call_user_func($callback);

            endif;

        else:

            // call closure
            call_user_func($callback);

        endif;
    }
}