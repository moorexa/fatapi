<?php
namespace Messaging\Emails;

use Closure;
use Messaging\Mail;
use Lightroom\Queues\QueueHandler;
/**
 * @package EmailHelper 
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait EmailHelper
{
    /**
     * @method EmailHelper processBackgroundRequest
     * @param string $method
     * @param array $data 
     * @param array $option
     * @param Closure $callback
     * @return void
     */
    public static function processBackgroundRequest(string $method, array $data, array $option, Closure $callback)
    {
        // merge option
        $option = array_merge(self::$option, $option);

        // send email by the background
        if ($option['background'] == true) :

            // send task
            QueueHandler::sendTask('send-email-'.time(), function() use (&$data, $method, &$option){

                // no background
                $option['background'] = false;

                // send request
                call_user_func([EmailSender::class, $method], $data, $option);

            });

        else:

            // load callback
            $callback($data, $option);

        endif;
    }

    /**
     * @method EmailHelper loadDefaultConnection
     * @param array $config
     * @return mixed
     */
    private static function loadDefaultConnection(array $config)
    {
        // has default
        $default = isset($config['default']) ? $config['default'] : Mail::class;

        // set data
        $_ENV['mailer_config'] = $config;
        $_ENV['mailer']['outgoing'] = $config['host'];
        $_ENV['mailer']['port'] = $config['port'];
        $_ENV['mailer']['default'] = 'fatapi_user';
        $_ENV['mailer']['users'][$_ENV['mailer']['default']] = [
            'user' => $config['user'],
            'password' => $config['pass']
        ];

        // read dsn
        $dsn = isset($config['dsn']) ? $config['dsn'] : '';

        // replace data
        if ($dsn != '') :

            // replace data
            foreach ($config as $key => $val) $dsn = str_replace('{'.$key.'}', $val, $dsn);

        endif;

        // set dsn
        $_ENV['mailer']['dsn'] = $dsn;

        // create instance
        $instance = new $default;

        // check for connect
        if (method_exists($instance, 'connect')) return $instance->connect();

        // return instance
        return $instance;
    }
}