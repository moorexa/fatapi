<?php
namespace Messaging;

use Messaging\Emails\EmailSender;
/**
 * @package EmailAlerts
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * This alert is meant for internal email notifications
 */
class EmailAlerts
{
    /**
     * @var string $sendTo
     */
    public static $sendTo = '';

    /**
     * @var bool $sendInBackground
     * 
     * To get the email to send in the background, ensure rabbitmq is running and rabbitmq client is running
     */
    public static $sendInBackground = true;

    /**
     * @method EmailAlerts newSubscriberAlert
     * @param array $data
     * @return void
     */
    public static function newSubscriberAlert(array $data = [])
    {
        EmailSender::newSubscriberAlert($data, [
            'background'    => self::$sendInBackground,
            'to'            => self::$sendTo,
            'subject'       => 'You have a new email subscriber'
        ]);
    }

    /**
     * @method EmailAlerts newContactAlert
     * @param array $data
     * @return void
     */
    public static function newContactAlert(array $data = [])
    {
        EmailSender::newContactAlert($data, [
            'background'    => self::$sendInBackground,
            'to'            => self::$sendTo,
            'subject'       => 'You have a new contact message'
        ]);
    }

    // add more...

}