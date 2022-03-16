<?php
namespace Messaging\Emails\Handlers;

use Messaging\Mail;
/**
 * @package SwiftMailer
 * @author Amadi Ifeanyi <amadiify.com>
 */
class SwiftMailer
{
    // email builder
    private $emailBuilder;

    /**
     * @method SymfonyMailer connect
     * @return 
     */
    public function connect()
    {
        // load builder
        $this->emailBuilder = Mail::config([
            'smtpHost' => $_ENV['mailer_config']['host'],
            'smtpUser' => $_ENV['mailer_config']['user'],
            'smtpPass' => $_ENV['mailer_config']['pass'],
            'smtpPort' => $_ENV['mailer_config']['port'],
        ]);

        // return class
        return $this;
    }

    // build mail
    public function __call(string $method, array $data)
    {
        $res = call_user_func_array([$this->emailBuilder, $method], $data);

        // send
        if ($method == 'send')
        {
            var_dump($res);
        }

        // return response
        return $res;
    }
}