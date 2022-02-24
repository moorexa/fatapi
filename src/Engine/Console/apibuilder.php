<?php
use Classes\Cli\Assist;

use function Lightroom\Security\Functions\{md5s};
/**
 * @package API Builder
 * @author Amadi Ifeanyi <amadiify.com>
 */

// constants
define('MAKE_RESOURCE', 'make');
define('MAKE_EXTERNAL', 'make:ext');
define('MAKE_MODEL', 'make:model');
define('MAKE_ROUTE', 'make:route');
define('MAKE_MIDDLEWARE', 'make:ware');
define('MAKE_DOCUMENTATION', 'make:doc');
define('MAKE_CONNECTION', 'make:dbms');
define('MAKE_TOKEN', 'make:token');
define('COMMAND', 0);
define('ACTION', 1);

// read argv
$arguments = array_splice($_SERVER['argv'], 2);

// can we continue?
if (count($arguments) > 0 && isset($arguments[ACTION])) :

    // extract action and version
    list($action, $version) = array_values(getVersion($arguments[ACTION]));

    // build path
    $path = FATAPI_BASE . '/Resources/' . formatName($action) . '/' . $version;

    // load assist
    $assist = new Assist;

    // read command
    switch ($arguments[COMMAND]) :

        // create resource
        case MAKE_RESOURCE:
        case MAKE_EXTERNAL:

            // check if version exists
            if (is_dir($path)) return Assist::out('This resource "'.$path.'" already exists!', $assist->ansii('red'));

            // get main path
            $mainPath = substr($path, 0, strrpos($path, '/'));

            // make directory
            if (!is_dir($mainPath)) mkdir($mainPath);

            // make version directory
            mkdir($path);

            // create empty file
            if (!file_exists($mainPath . '/readme.md')) file_put_contents($mainPath . '/readme.md', '# ' . formatName($action) . ' Resource');

            // external or internal service?
            switch ($arguments[COMMAND]):

                // external service
                case MAKE_EXTERNAL:

                    // create config file
                    file_put_contents($path . '/config.json', file_get_contents(__DIR__ . '/external-config.json'));

                    // all good
                    Assist::out('External Resource "'.formatName($action).'" Created successfully in "'.$path.'"', $assist->ansii('green'));
                    Assist::out('Please remember to clean dummy sample in "routes"' . PHP_EOL);

                break;

                // internal service
                case MAKE_RESOURCE:

                    // required folders
                    $foldersAndFiles = [
                        'Documentation' => [
                            'Get{SERVICE}.md'   => __DIR__ . '/newgetdoc.md',
                            'Post{SERVICE}.md'  => __DIR__ . '/newpostdoc.md',
                        ],
                        'Model' => [],
                        'Events' => [
                            'Listener.php' => __DIR__ . '/listener-template.txt'
                        ],
                        'Data' => [
                            'GeneralQuery.php'      => __DIR__ . '/general-query-template.txt',
                            'Struct.php'            => __DIR__ . '/struct-template.txt',
                            'UnpackStruct.php'      => __DIR__ . '/unpack-struct-template.txt',
                            'SQL.php'               => __DIR__ . '/sql-template.txt',
                        ],
                        'Providers' => [
                            'CreateProvider.php' => __DIR__ . '/provider-template.txt',
                            'DeleteProvider.php' => __DIR__ . '/provider-template.txt',
                            'UpdateProvider.php' => __DIR__ . '/provider-template.txt',
                        ],
                        'Get{SERVICE}.php'  => __DIR__ . '/get-service-template.txt',
                        'Post{SERVICE}.php' => __DIR__ . '/post-service-template.txt',
                    ];

                    // files created
                    Assist::sleep('Creating service files and directories');

                    // create folders then files
                    foreach ($foldersAndFiles as $directory => $files) :

                        // build new path
                        $newPath = $path . '/' . $directory;

                        // has extension
                        if (strpos($directory, '.php') !== false) :

                            // replace service
                            $file = str_replace('{SERVICE}', formatName($action), $directory);

                            // Create now
                            $newPath = $path . '/' . $file;

                            // get content
                            $content = file_get_contents($files);

                            // update service and version
                            $content = str_replace('{VERSION}', $version, $content);
                            $content = str_replace('{SERVICE}', formatName($action), $content);

                            // create file now
                            file_put_contents($newPath, $content);
                            
                            // created
                            Assist::out($newPath . ' created successfully!', $assist->ansii('green'));

                        else:

                            // create now
                            if (!is_dir($newPath)) mkdir($newPath);

                            // is array
                            if (is_array($files) && count($files) > 0) :

                                foreach ($files as $file => $template) :

                                    // replace service
                                    $file = str_replace('{SERVICE}', formatName($action), $file);

                                    // build new path
                                    $newPath = $path . '/' . $directory . '/' . $file;

                                    // get content
                                    $content = file_get_contents($template);

                                    // update service and version
                                    $content = str_replace('{SERVICE}', formatName($action), $content);
                                    $content = str_replace('{VERSION}', $version, $content);

                                    // get the file name
                                    $fileName = substr($file, 0, strpos($file, '.'));

                                    // replace provider
                                    $content = str_replace('{PROVIDER}', $fileName, $content);

                                    // create file now
                                    file_put_contents($newPath, $content);
                                    
                                    // created
                                    Assist::out($newPath . ' created successfully!', $assist->ansii('green'));

                                endforeach;

                            endif;

                        endif;

                    endforeach;


                break;

            endswitch;

        break;

        // create model
        case MAKE_MODEL:

            // split action to controller and model name
            $actionArray = explode('/', $action);

            if (count($actionArray) != 2) return Assist::out('Could not proceed with request. You failed to format the action like this "RESOURCE/MODEL"', $assist->ansii('red'));

            // build path
            $path = FATAPI_BASE . '/Resources/' . formatName($actionArray[0]) . '/' . $version;
            
            // check if version exists
            if (!is_dir($path)) return Assist::out('This resource "'.$path.'" does not exists!', $assist->ansii('red'));

            // add model path
            $path .= '/Model/';

            // check if model exists
            if (!is_dir($path)) return Assist::out('This resource "'.$path.'" does not have a model directory!', $assist->ansii('red'));

            // add model file name
            $path .= formatName($actionArray[1]) . '.php';

            // check if file exists
            if (file_exists($path)) return Assist::out('This model file "'.formatName($actionArray[1]).'" already exists as "'.$path.'" Please delete or check spelling before trying again.', $assist->ansii('red'));

            // read template data
            $template = file_get_contents(__DIR__ . '/model-template.txt');

            // replace service, version, and model
            $template = str_replace('{SERVICE}', formatName($actionArray[0]), $template);
            $template = str_replace('{MODEL}', formatName($actionArray[1]), $template);
            $template = str_replace('{VERSION}', $version, $template);

            // save now
            file_put_contents($path, $template);

            // created
            Assist::out($path . ' created successfully!', $assist->ansii('green'));
        break;

        // create route
        case MAKE_ROUTE:

            // split action to controller and model name
            $actionArray = explode('/', $action);

            if (count($actionArray) != 2) return Assist::out('Could not proceed with request. You failed to format the action like this "RESOURCE/ROUTE"', $assist->ansii('red'));

            // create service
            $service = formatName($actionArray[0]);

            // build path
            $path = FATAPI_BASE . '/Resources/' . $service . '/' . $version;
            
            // check if version exists
            if (!is_dir($path)) return Assist::out('This resource "'.$path.'" does not exists!', $assist->ansii('red'));

            // Get route
            $route = formatName($actionArray[1]);

            // get last param
            $last = end($arguments);

            // backup path
            $backupPath = $path;

            // post or get
            if ($last == '-post' || $last == '-get') :

                // is post
                if ($last == '-post') :

                    // use post service
                    $path .= '/Post'.$service.'.php';
                
                else:

                    // use get service
                    $path .= '/Get'.$service.'.php';

                endif;

            else :

                // check for create, update, and delete
                if (stripos($route, 'create') === 0) :

                    // use create provider
                    $path .= '/Providers/CreateProvider.php';

                elseif (stripos($route, 'update') === 0) :

                    // use update provider
                    $path .= '/Providers/UpdateProvider.php';

                elseif (stripos($route, 'delete') === 0) :

                    // use delete provider
                    $path .= '/Providers/DeleteProvider.php';

                else:

                    // try be specific
                    if (stripos($route, 'get') === 0 || stripos($route, 'fetch') === 0) :

                        // use get service
                        $path .= '/Get'.formatName($actionArray[0]).'.php';

                    elseif (stripos($route, 'submit') === 0):

                        // use post service
                        $path .= '/Post'.formatName($actionArray[0]).'.php';

                    endif;

                endif;

            endif;

            // have a file
            if (!is_file($path)) return Assist::out('Could not proceed with request. We could not load a destination. Try adding -post or -end at the end of the command', $assist->ansii('red'));

            // use backup
            $useBackup = false;

            // use backup path
            if (stripos($path, 'provider') !== false) :
                
                // update bool
                $useBackup = true;

                // update backup path
                $backupPath .= '/Post'.$service.'.php';

            endif;

            // include file
            include_once ($useBackup ? $backupPath : $path);

            // get class name
            $basename = basename(($useBackup ? $backupPath : $path));

            // remove extension
            $className = substr($basename, 0, strpos($basename, '.'));

            // load content
            $content = file_get_contents(($useBackup ? $backupPath : $path));

            // get namespace
            $namespace = '';
            
            // check now
            if (strpos($content, 'namespace')) :

                // start here
                $namespace = substr($content, strpos($content, 'namespace'));

                // end here
                $namespace = substr($namespace, 0, strpos($namespace, ';'));

                // replace namespace
                $namespace = trim(str_replace('namespace', '', $namespace));

            endif;

            // reload content
            if ($useBackup) $content = file_get_contents($path);

            // add namespace to class
            $classNamespace = ($namespace != '') ? $namespace . '\\' . $className : $className;

            // check if class exists
            if (!class_exists($classNamespace)) return Assist::out('Could not proceed with request. We could not load class "'.$classNamespace.'".', $assist->ansii('red'));

            // create instance
            $instance = new $classNamespace;

            // check if method exists
            if (method_exists($instance, $route)) return Assist::out('Could not proceed with request. Route "'.$route.'" already exists in "'.$classNamespace.'"', $assist->ansii('red'));

            // get template
            $template = file_get_contents(__DIR__ . '/route-template.txt');

            // replace method name
            $template = str_replace('{METHOD}', $route, $template);
            $template = str_replace('{CLASS}', $className, $template);

            // get the last brace
            $lastBracePosition = strrpos($content, '}');

            // extract content
            $content = substr($content, 0, $lastBracePosition);

            // add template data
            $content .= "\n" . $template . "\n}";
            
            // update file
            file_put_contents($path, $content);

            // all good
            Assist::out('Route method "'.$route.'" added to "'.$path.'" successfully!', $assist->ansii('green'));
            
        break;

        // create middleware
        case MAKE_MIDDLEWARE:

            // build path
            $path = FATAPI_BASE . '/Middlewares/' . formatName($action) . '.php';

            // does middleware exists?
            if (file_exists($path)) return Assist::out('Could not proceed with request. There is a model with the same name as this "'.formatName($action).'"', $assist->ansii('red'));
            
            // replace middleware name
            $template = str_replace('{MIDDLEWARE}', formatName($action), file_get_contents(__DIR__ . '/middleware-template.txt'));

            // save now
            file_put_contents($path, $template);

            // created
            Assist::out($path . ' created successfully!', $assist->ansii('green'));

        break;

        // create documentation
        case MAKE_DOCUMENTATION:
        break;

        // create connection
        case MAKE_CONNECTION:

            // load dbms file
            $dbms = new Engine\DBMS();

            // format method
            $method = formatName($action);

            // does method exists?
            if (method_exists($dbms, $method)) :

                Assist::out('This method "'.$method.'" already exists as a connection name. Please try another.', $assist->ansii('red'));

            else:

                // path
                $path = FATAPI_BASE . '/Engine/DBMS.php';

                // get content
                $content = file_get_contents($path);

                // get template
                $template = file_get_contents(__DIR__ . '/dbms-template.txt');

                // replace connection name
                $template = str_replace('{CONNECTION}', $method, $template);

                // get the last brace
                $lastBracePosition = strrpos($content, '}');

                // extract content
                $content = substr($content, 0, $lastBracePosition);

                // add template data
                $content .= "\n" . $template . "\n}";
                
                // update dbms file
                file_put_contents($path, $content);

                // all good
                Assist::out('Connection method "'.$method.'" added to "'.$path.'" successfully!', $assist->ansii('green'));
                
            endif;

        break;

        // create token
        case MAKE_TOKEN:

            $token = sha1(md5s(file_get_contents(FATAPI_BASE . '/info.json')) . time() * mt_rand(2, 50) . $arguments[ACTION]);

            Assist::out('Token generated size {'.strlen($token).'}' . PHP_EOL, $assist->ansii('green'));
            Assist::out($token . "\n\n");

        break;

    endswitch;

else:

    // load assist
    $assist = new Assist;

    // no action
    Assist::out('You forgot to add an action', $assist->ansii('red'));

endif;

// get version number
function getVersion(string $line) : array
{
    // build data
    $data = [
        'data'      => $line,
        'version'   => func()->finder('version')
    ];

    if (strpos($line, ':') !== false) :

        // create array
        $lineArray = explode(':', $line);

        // update data
        $version = array_pop($lineArray);

        // update data
        $data['data'] = implode('/', $lineArray);
        $data['version'] = $version;

    endif;

    // return array
    return $data;
}

// format name
function formatName(string $line) : string 
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