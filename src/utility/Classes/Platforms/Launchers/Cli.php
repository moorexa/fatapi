<?php
namespace Classes\Platforms\Launchers;

use Lightroom\Core\{
    Payload, BootCoreEngine
};
use Classes\Platforms\PlatformInterface;
/**
 * @package Cli Launcher
 * @author Amadi Ifeamyi <amadiify.com>
 */
class Cli implements PlatformInterface
{
    use Helper;

    /**
     * @var array $headers_list
     */
    private $headers_list = [
        'Platform' => 'Cli',
        'Platform-Token' => '1296882afd1c8be921bdd837b06b113cc4ffd685' // you should update this constantly
    ];

    /**
     * @method PlatformInterface loadPlatform
     * @param Payload $payload
     * @param BootCoreEngine $engine
     * @return bool
     * 
     * Remember, you can run $this->generateToken(<salt>) to generate a new platform token 
     */
    public function loadPlatform(Payload &$payload, BootCoreEngine $engine) : bool
    {
        // @var bool $continue
        $continue = $this->hasHeaders();

        if ($continue) :

            // set content type
            $engine->setContentType($this->loadContentType('cli'));

            // get arguments
            $argc = $_SERVER['argc'];

            // continue if length is greater than zero
            if ($argc > 0) :

                // get query
                $query = $_SERVER['argv'][0];

                if (strpos($query, '=') !== false) :

                    // query
                    $query = substr($query, strpos($query, '=') + 1);

                    // application
					$app = explode(' ', $query);
					
					// args
					array_unshift($app, 'assist');

					// update argv
                    $_SERVER['argv'] = $app;
                    
                    // stdout
					$out = fopen('php://output', 'w+');

					define('STDOUT', $out);
                    define('STDIN', fopen('php://input', 'r'));
                    
                    // define ASSIST_TOKEN
                    if (!defined('ASSIST_TOKEN')) define('ASSIST_TOKEN', true);
                    
                    // include assist manager
                    include_once APPLICATION_ROOT . 'assist';

                endif;

            endif;

        endif;

        // return bool
        return $continue;
    }

    
}