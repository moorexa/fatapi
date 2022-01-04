<?php
namespace Messaging\Emails\Handlers;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
/**
 * @package SymfonyMailer
 * @author Amadi Ifeanyi <amadiify.com>
 */
class SymfonyMailer
{
    // transport instance
    private $transport = null;

    // email builder
    private $emailBuilder;

    /**
     * @method SymfonyMailer connect
     * @return 
     */
    public function connect()
    {
        // load transport
        $this->transport = Transport::fromDsn($_ENV['mailer']['dsn']);

        // return email builder
        $this->emailBuilder = new Email();

        // return class
        return $this;
    }

    // send mail
    public function send()
    {
        // load mailer
        $mailer = new Mailer($this->transport);
        $mailer->send($this->emailBuilder);
    }

    // build mail
    public function __call(string $method, array $data)
    {
        return call_user_func_array([$this->emailBuilder, $method], $data);
    }
}