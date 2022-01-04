<?php
namespace Messaging\Emails;

use Messaging\Mail;
/**
 * @package EmailSender 
 * @author Amadi Ifeanyi <amadiify.com>
 */
class EmailSender
{
    use EmailHelper;

    /**
     * @var array $option
     */
    public static $option = [
        'background' => false
    ];

    /**
     * @method EmailSender __callStatic
     * @param array $data
     * @param array $option
     */
    public static function __callStatic(string $method, array $collection)
    {
        // @var object $emailList
        $emailList = json_decode(file_get_contents(__DIR__ . '/email-list.json'));

        // check for method
        if (isset($emailList->{$method})) :

            // get data
            $data = $collection[0];

            // get option
            $option = isset($collection[1]) ? $collection[1] : [];

            // get callback
            $callback = isset($collection[2]) ? $collection[2] : null;

            // load from cofig
            $emailConfig = include __DIR__ . '/config.php';

            // load process
            self::processBackgroundRequest($method, $data, $option, function($data, $option) use ($method, $emailList, $emailConfig, &$callback){

                // get config
                $configData = (array) $emailList->{$method};

                // load template
                $template = call_user_func([EmailTemplate::class, $configData['category']], $configData['template'], $data, $configData);

                // load default connection
                $mail = self::loadDefaultConnection($emailConfig);

                // set the subject
                $mail->subject((isset($option['subject']) ? $option['subject'] : $configData['subject']));

                // add from
                if (isset($configData['from']) && $configData['from'] != '') $mail->from($configData['from']);

                // read from option
                if (isset($option['from']) && $option['from'] != '') $mail->from($option['from']);

                // add to
                if (isset($option['to']) && $option['to'] != '') $mail->to($option['to']);

                // add html
                $mail->html($template);

                // add mail to callback
                if (is_callable($callback)) call_user_func($callback, $mail);

                // send mail
                $mail->send();
    
            });

        endif;
 
    }
}