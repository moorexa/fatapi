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

    // email sender constant
    const PREFLIGHT_MODE = '100xr5su';
    const READY_STATE = '120xxwt2';

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
 
                // get preflight and ready state
                $preflightMode = \Messaging\Emails\EmailSender::PREFLIGHT_MODE;
                $readyState = \Messaging\Emails\EmailSender::READY_STATE;

                // add mail to callback
                if (isset($option[$preflightMode]) && is_callable($option[$preflightMode])) call_user_func_array($option[$preflightMode], [&$mail]);

                // send mail
                $response = $mail->send();

                // email response ready
                if (isset($option[$readyState]) && is_callable($option[$readyState])) call_user_func($option[$readyState], $response);
    
            });

        endif;
 
    }

    /**
     * @method EmailSender sendInlineMessage
     * @param array $data (should contain 'subject', 'from', 'to', and 'message')
     * @param array $option (can have 'background' to be true to run this in the background, or 'callback' with a closure function)
     */
    public static function sendInlineMessage(array $data, array $option = [])
    {
        // apply filter
        $filter = filter($data, [
            'subject'   => 'required|string|min:3',
            'from'      => 'required|email|notag|min:5',
            'to'        => 'required|email|notag|min:5',
            'message'   => 'required|min:2'
        ]);

        // are we good?
        if (!$filter->isOk()) throw new \Exception(sprintf("Validation failed for mail body. See errors reported (%s)", json_encode($filter->getErrors())));

        // load from cofig
        $emailConfig = include __DIR__ . '/config.php';

        // run request instantly or in the background.
        self::processBackgroundRequest('sendInlineMessage', $data, $option, function($data) use (&$emailConfig, &$option){

            // load default connection
            $mail = self::loadDefaultConnection($emailConfig);

            // set subject
            $mail->subject($data['subject']);

            // set from
            $mail->from($data['from']);

            // set to
            $mail->to($data['to']);

            // add html
            $mail->html($data['message']);

            // get preflight and ready state
            $preflightMode = \Messaging\Emails\EmailSender::PREFLIGHT_MODE;
            $readyState = \Messaging\Emails\EmailSender::READY_STATE;

            // add mail to callback
            if (isset($option[$preflightMode]) && is_callable($option[$preflightMode])) call_user_func_array($option[$preflightMode], [&$mail]);

            // send mail
            $response = $mail->send();           

            // email response ready
            if (isset($option[$readyState]) && is_callable($option[$readyState])) call_user_func($option[$readyState], $response);

        });
    }
}