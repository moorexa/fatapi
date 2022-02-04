<?php
namespace Engine;

use Whoops\Exception\Formatter;
use Lightroom\Common\Interfaces\ExceptionWrapperInterface;
/**
 * @package ErrorHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */
class ErrorHelper implements ExceptionWrapperInterface
{
    /**
     * @method ErrorHelper JsonResponseHandler
     * 
     * Captures exceptions and returns information on them as a JSON string. Can be used to, for example, play nice with AJAX requests.
     */
    public function JsonResponseHandler()
    {
        set_exception_handler(function($exception){
            
            $response = new Response();

            // set the message
            $error = [
                'Error' => [
                    'Message' => $exception->getMessage(),
                    'Line' => $exception->getLine(),
                    'File' => $exception->getFile(),
                    'Code' => $exception->getCode(),
                ]
            ];

            // exception occured
            $response->failed('This is a server error. Please contact the developer', $error);

            // save to file
            $errorLog = BASE . '/error.log';

            // save if file exists
            if (file_exists($errorLog)) :

                $fh = fopen($errorLog, 'a+');

                // add time
                $error['Date'] = date('Y-m-d g:i:s a');

                // add data
                fwrite($fh, json_encode($error, JSON_PRETTY_PRINT) . ',' . PHP_EOL);
                fclose($fh);

            endif;

        });

    }

}