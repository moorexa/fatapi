<?php
namespace Engine;

use Lightroom\Requests\Filter;
use Lightroom\Adapter\ClassManager;
use Engine\Interfaces\ModelInterface;
use Engine\Interfaces\ResourceInterface;
use function Lightroom\Requests\Functions\{headers, get};
/**
 * @package Request Handler
 * @author Amadi Ifeanyi <amadiify.com> 
 */
class Request implements Interfaces\RequestInterface
{
    /**
     * @var array $requestData
     */
    private $requestData = [];

    /**
     * @var array $prameters
     */
    private $prameters = [];

    /**
     * @var object|null $schema
     */
    private $schema = null;

    /**
     * @var mixed $header
     */
    private $header = null;

    /**
     * @var array $methods
     */
    private $methods = [];

    /**
     * @method RequestInterface get
     * @param string $key
     * @return mixed
     * 
     * This return data cached from the request body
     */
    public function get(string $key)
    {
        if (isset($this->requestData[$key])) return $this->requestData[$key];
    }

    /**
     * @method RequestInterface getSchema
     * This returns the current schema of the request service method.
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @method RequestInterface query
     * @param string $key
     * 
     * This returns the current query data
     */
    public function query(string $key)
    {
        /**
         * @var string $query
         */
        $query = null;

        // check query data
        if (get()->has($key)) $query = get()->get($key);

        // return query
        return $query;
    }

    /**
     * @method RequestInterface getParam
     * @param int $index
     */
    public function getParam(int $index)
    {
        static $prameters;

        if (is_null($prameters)) $prameters = is_array($this->prameters) ? $this->prameters : explode('/', $this->prameters);

        /**
         * @var mixed $param
         */
        $param = null;

        // has index
        if (isset($prameters[$index])) $param = $prameters[$index];

        // return param
        return $param;
    }

    /**
     * @method RequestInterface getData
     * @return array
     * 
     * This returns all the data cached from the request body
     */
    public function getData() : array
    {
        return $this->requestData;
    }

    /**
     * @method RequestInterface getOnly
     * @param mixed $args
     * @return array
     * 
     * This returns selected data from the cached request body
     */
    public function getOnly(...$args) : array
    {
        /**
         * @var array $data
         */
        $data = [];

        // check for args in request data
        foreach ($args as $key) if (isset($this->requestData[$key])) $data[$key] = $this->requestData[$key]; 

        // return data
        return $data;
    }

    /**
     * @method RequestInterface getHeader
     * @param string $key
     * @return mixed
     * 
     * This return data cached from the request header
     */
    public function getHeader(string $key)
    {
        /**
         * @var null $header
         */
        $header = null;

        // check if it has 
        if ($this->header->has($key)) $header = $this->header->get($key);

        // return header
        return $header;
    }

    /**
     * @method Request loadResource
     * @param Filter $filter
     * @param ResourceInterface $class
     * @param mixed $param 
     * @return mixed
     */
    public static function loadResource(Filter $filter, ResourceInterface $class, $param)
    {
        // load instance
        $instance = ClassManager::singleton(static::class);

        // load json file
        $request = json_decode(file_get_contents(HOME . 'app/Resources/input.json'));

        // set the prameters
        $instance->prameters = $param;

        // must be an array
        if (is_array($request) && count($request) > 0) :

            // build resource
            $resource = $filter->service . '.' . $filter->method;

            // get http verb
            $verb = strtoupper($_SERVER['REQUEST_METHOD']);

            // build required data
            $requiredSchema = [
                'service'   => 'required|string|notag|min:2',
                'method'    => 'required|string|notag|min:2',
                'version'   => 'required|string|notag|min:1',
                'verb'      => 'required|string|notag|min:3',
                'body'      => 'object'
            ];

            // loop through
            foreach ($request as $data) :

                // match resource
                if (!isset($data->service) || !isset($data->method) || !isset($data->verb) || !isset($data->version)) :

                    // run filter
                    $checkSchema = filter((array)$data, $requiredSchema);

                    // is schema good 
                    if (!$checkSchema->isOk()) return app('screen')->render([
                        'Status'        => false,
                        'Message'       => 'You failed to format your request data properly in "app/Resources/request.json". Please change your schema data',
                        'From'          => $data,
                        'ToMatch'       => $requiredSchema
                    ]);

                endif;

                // @var bool $isMethod
                $isMethod = ($data->service.'.'.$data->method) == $resource ? true : (($data->service.'.'.self::formatName($data->method)) == $resource ? true : false);

                // Has resource, verb, and version to continue
                if ($isMethod && strtoupper($data->verb) == $verb) :

                    // clean up data version
                    $data->version = preg_replace('/[\s]+/', '', $data->version);

                    // read version to array
                    $version = explode(',', $data->version);

                    // check the version
                    if ($data->version == '*' || in_array(MetaDataService::$version, $version)) :

                        // check the body
                        if (isset($data->body)) :

                            // load filter
                            $filterBody = filter($verb, (array) $data->body);

                            // failed?
                            if (!$filterBody->isOk()) return app('screen')->render([
                                'Status'    => false,
                                'Message'   => 'You have failed to send the right request body for this resource "'.$filter->service.'.'.$filter->method.'". Please see what request body to send below',
                                'Body'      => $data->body,
                                'Errors'     => $filterBody->getErrors()
                            ]);

                            // set schema
                            $instance->schema = $data->body;

                            // all good so add data
                            foreach($data->body as $key => $val) $instance->requestData[$key] = $filterBody->{$key};

                            // break
                            break;

                        endif;

                    endif;

                endif;

            endforeach;

        endif;

        // set the header
        $instance->header = headers();

        // load methods
        $classMethods = get_class_methods($instance);

        // loop through
        foreach ($classMethods as $method) :

            // load reflection
            $reflection = new \ReflectionMethod($instance, $method);

            // if it's public
            if ($reflection->isPublic()) :

            // add to class methods
            $instance->methods[static::class . '::' .$method] = [
                'parameter'     => $reflection->getParameters(),
                'description'   => $reflection->getDocComment()
            ];

            endif;

        endforeach;

        // clean up
        $classMethods = $method = $reflection = null;

        // load service method
        call_user_func([$class, $filter->method], $instance, ClassManager::singleton(Response::class));

        // clean up
        $instance = $request = null;
    }

    /**
     * @method Request __get
     * @param string $name
     * 
     * Would check from the request body before going all the way down
     */
    public function __get(string $name)
    {
        // check request body
        $data = $this->get($name);

        // is null
        if ($data !== null) return $data;

        // check query
        $data = $this->query($name);

        // is null
        if ($data !== null) return $data;

        // check header
        $data = $this->getHeader($name);

        // is null
        if ($data !== null) return $data;

        // is id
        if (strtolower($name) == 'id') return $this->getParam(0);  
    }

    /**
     * @method Request __set
     * @param string $name
     * @param mixed $data
     * @return void
     * 
     * This would update or create a new entry in our request data array
     */
    public function __set(string $name, $data)
    {
        $this->requestData[$name] = $data;
    }

    /**
     * @method RequestInterface useModel
     * @param string $model
     * @return ModelInterface
     * 
     * This loads a model to handle the request data sent
     */
    public function useModel(string $model) : ModelInterface
    {
        // check if class exists
        if (!class_exists($model)) throw new \Lightroom\Exceptions\ClassNotFound($model);

        // load class reflection
        $reflection = new \ReflectionClass($model);

        // check if it implements the model interface
        if (!$reflection->implementsInterface(ModelInterface::class)) throw new \Exception('The model class "'.$model.'" does not implement the model interface "'.Interfaces\ModelInterface::class.'"');

        // get class instance
        $model = ClassManager::singleton($model);

        // get the data
        $data = $this->getData();

        // get the id
        $id = isset($data['id']) ? intval($data['id']) : $this->getParam(0);

        // id exists
        if (is_numeric($id)) :
            
            // update current id
            if (isset($data['id'])) $data['currentId'] = $id;

            // update id
            if (!isset($data['id'])) $data['id'] = $id;

        endif;

        // load fillables
        $model->Fillable(new RequestData($data));

        // return model
        return $model;
    }

    /**
     * @method Request formatName
     * @param string $line
     * @return string
     */
    private static function formatName(string $line) : string 
    {
        // Remove '-'
        $line = str_replace('-', ' ', $line);

        // camelcase next
        $line = ucwords($line);

        // trim off spaces
        $line = preg_replace('/[\s]+/', '', $line);

        // return line
        return $line;
    }
}