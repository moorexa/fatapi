<?php
namespace Lightroom\Router\Guards;

use Lightroom\Router\Interfaces\RouteGuardInterface;
use function Lightroom\Requests\Functions\{session};
use function Lightroom\Functions\GlobalVariables\{var_get};

/**
 * @package Route Guard
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait RouteGuard
{
    /**
     * @var array $incomingUrl
     */
    private $incomingUrl = [];

    /**
     * @var RouteGuard FIRST_PARAM
     */
    private $FIRST_PARAM = 0;

    /**
     * @var RouteGuard SECOND_PARAM
     */
    private $SECOND_PARAM = 1;

    /**
     * @var RouteGuard THIRD_PARAM
     */
    private $THIRD_PARAM = 2;

    /**
     * @method RouteGuardInterface setIncomingUrl
     * @param array $incomingUrl
     * @return void
     */
    public function setIncomingUrl(array $incomingUrl) : void
    {
        $this->incomingUrl = $incomingUrl;
    }

    /**
     * @method RouteGuardInterface setView
     * @param string $view
     * @return void
     */
    public function setView(string $view) : void 
    {
        $this->incomingUrl[(int) $this->SECOND_PARAM] = $view;
    }

    /**
     * @method RouteGuardInterface setController
     * @param string $controller
     * @return void
     */
    public function setController(string $controller) : void 
    {
        $this->incomingUrl[(int) $this->FIRST_PARAM] = $controller;
    }

    /**
     * @method RouteGuardInterface getView
     * @return string
     */
    public function getView() : string 
    {
        return isset($this->incomingUrl[(int) $this->SECOND_PARAM]) ? $this->incomingUrl[(int) $this->SECOND_PARAM] : '';
    }

    /**
     * @method RouteGuardInterface getController
     * @return string
     */
    public function getController() : string 
    {
        return isset($this->incomingUrl[(int) $this->FIRST_PARAM]) ? $this->incomingUrl[(int) $this->FIRST_PARAM] : '';
    }

    /**
     * @method RouteGuardInterface getIncomingUrl
     * @return array
     */
    public function getIncomingUrl() : array 
    {
        return $this->incomingUrl;
    }

    /**
     * @method RouteGuard getArguments
     * @return array
     */
    public function getArguments() : array 
    {
        // @var array $arguments
        $arguments = $this->incomingUrl;

        // extract from index 2
        $arguments = array_splice($arguments, (int) $this->THIRD_PARAM);

        // return array
        return $arguments;
    }

    /**
     * @method RouteGuard setArguments
     * @param mixed $arguments
     * @return void
     */
    public function setArguments(...$arguments) : void 
    {
        // try update arguments
        if (isset($arguments[0])) : 

            // check if first argument is an array and update $arguments
            if (is_array($arguments[0])) $arguments = $arguments[0];

        endif;

        // @var array $incomingUrl
        $incomingUrl = $this->incomingUrl;

        // get the first 2 
        $incomingUrl = array_splice($incomingUrl, (int)$this->FIRST_PARAM, (int) $this->THIRD_PARAM);
        
        // merge arguments with incoming url
        $this->incomingUrl = array_merge($incomingUrl, $arguments);
    }

    /**
     * @method RouteGuard redirectPath
     * @return mixed
     */
    public function redirectPath()
    {
        $this->redirect($this->getRedirectPath());
    }

    /**
     * @method RouteGuard redirect
     * @param string $path
     * @param array $arguments
     * @param string $redirectDataName
     * @return mixed
     */
    public function redirect(string $path = '', array $arguments = [], string $redirectDataName = '') 
    {
        if (is_string($path) && $path != '') :

            // @var bool $sameOrigin
            $sameOrigin = false;

            // check for same origin
            if (strpos($path, '@') === 0) :

                // update same origin
                $sameOrigin = true;

                // update path
                $path = substr($path, 1);

            endif;

            // set the response code
            http_response_code(301);
            
            // not external link
            if (!preg_match("/(:\/\/)/", $path)) :

                // get query
                $query = isset($arguments['query']) && is_array($arguments['query']) ? '?' . http_build_query($arguments['query']) : '';

                // get redirect data
                $data = [];

                // check query
                if (strlen($query) > 3) :

                    // check for data in arguments
                    $data = isset($arguments['data']) && is_array($arguments['data']) ? $arguments['data'] : [];

                else:

                    // data would be arguments here
                    $data = $arguments;

                endif;


                // get current request
                $currentRequest = ltrim($_SERVER['REQUEST_URI'], '/');

                // trigger redirection 
                if (event()->canEmit('ev.redirection')) event()->emit('ev', 'redirection', [
                    'path'  => &$path,
                    'query' => &$query,
                    'data'  => &$data
                ]);

                // add query to path
                $pathWithQuery = $path . $query;

                // redirect if pathWithQuery is not equivalent to the current request
                if (($pathWithQuery != $currentRequest) || ($pathWithQuery == $currentRequest && $sameOrigin)) :

                    // export data
                    if (count($data) > 0) :

                        // get redirect data
                        $redirectData = session()->get('redirect.data');

                        // create array if not found
                        if (!is_array($redirectData)) $redirectData = [];

                        // lets add path
                        $redirectData[$pathWithQuery] = $data;

                        // set redirect data
                        session()->set('redirect.data', $redirectData);

                    endif;

                    // start buffer
                    ob_start();

                    // perform redirection
                    header('location: '. func()->url($pathWithQuery), true, 301); exit;

                endif;

            else:

                // @var string $query
                $query = '';

                if ($redirectDataName === '') :

                    // build query
                    $query = http_build_query($arguments);

                    // check length
                    $query = strlen($query) > 1 ? '?' . $query : $query;

                else:

                    if (count($arguments) > 0) :

                        // get redirect data
                        $redirectData = session()->get('redirect.data');

                        // create array if not found
                        if (!is_array($redirectData)) $redirectData = [];

                        // lets add path
                        $redirectData[$redirectDataName] = $arguments;

                        // set redirect data
                        session()->set('redirect.data', $redirectData);

                    endif;

                endif;

                // trigger redirection 
                if (event()->canEmit('ev.redirection')) event()->emit('ev', 'redirection', [
                    'path'  => &$path,
                    'query' => &$query,
                    'data'  => &$data
                ]);

                // start buffer
                ob_start();

                // redirect to external link
                header('location: ' . $path . $query, true, 301); exit;

            endif;

        else:   

            // return object
            return new class($redirectDataName)
            {
                /**
                 * @var array $exported
                 */
                private $exported = [];

                // load exported data
                public function __construct(string $redirectDataName = '')
                {
                    // get current request
                    if (session()->has('redirect.data')) :

                        // @var array $data
                        $data = session()->get('redirect.data');

                        // get view
                        $view = $redirectDataName != '' ? $redirectDataName : var_get('url')->view;

                        // get params
                        $params = implode('/', var_get('url')->params);

                        // load data
                        $redirectData = isset($data[$view]) ? $data[$view] : (isset($data[$params]) ? $data[$params] : null);

                        // check for exported data for current request
                        if ($redirectData !== null) :

                            // set
                            $this->exported = $redirectData;

                            // clean up
                            if (isset($data[$view])) unset($data[$view]);

                            // check params
                            if (isset($data[$params])) unset($data[$params]);

                            // set session again
                            session()->set('redirect.data', $data);

                        endif;

                    endif;
                }

                /**
                 * @method Common data
                 * @return array
                 */
                public function data() : array 
                {
                    return $this->exported;
                }

                /**
                 * @method Common has
                 * @param array $arguments
                 * @return bool
                 */
                public function has(...$arguments) : bool 
                {
                    // @var int $found
                    $found = 0;

                    // @var bool $has 
                    $has = false;

                    // check now
                    foreach ($arguments as $name) if (isset($this->exported[$name])) $found++;

                    //compare found
                    if (count($arguments) == $found) $has = true;

                    // return bool
                    return $has;
                }

                /**
                 * @method Common get
                 * @return mixed
                 */
                public function get(string $name) 
                {
                    return isset($this->exported[$name]) ? $this->exported[$name] : null;
                }

                /**
                 * @method Common __get
                 * @param string $name
                 * @return mixed
                 */
                public function __get(string $name) 
                {
                    // return value
                    return $this->get($name);
                }
            };

        endif;
    }
}