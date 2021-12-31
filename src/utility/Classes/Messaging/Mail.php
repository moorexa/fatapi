<?php
/** @noinspection All */
namespace Messaging;

class Mail
{
    // smtp host
    private $smtpHost;

    // smtp user
    private $smtpUser;

    // smtp pass
    private $smtpPass;

    // smtp port
    private $smtpPort;

    // smtp encryption
    private $smtpEncryption = null;

    // transport instance
    private static $transport = null;

    // build data
    private $mail_data = [];

    // load configuration
    public function __construct()
    {
        // load from env
        if (isset($_ENV['swiftmailer'])) : 

            // @var array mailer
            $mailer = $_ENV['swiftmailer'];

            // set the smtp host
            $this->smtpHost = isset($mailer['outgoing']) ? $mailer['outgoing'] : $this->smtpHost;

            // set the port
            $this->smtpPort = isset($mailer['port']) ? $mailer['port'] : $this->smtpPort;

            // set the encryption 
            $this->smtpEncryption = isset($mailer['encryption']) ? $mailer['encryption'] : $this->smtpEncryption;

            // set the username and password
            // look for default
            $default = isset($mailer['default']) ? $mailer['default'] : null;

            // are we good ?
            if ($default !== null && (isset($mailer['users']))) :

                // check for default account 
                if (isset($mailer['users'][$default])) :

                    // set the user
                    $this->smtpUser = $mailer['users'][$default]['user'];

                    // set the password
                    $this->smtpPass = $mailer['users'][$default]['password'];

                endif;
            endif;

        endif;
    }

    // create 
    public static function config(array $data)
    {
        // create a mail instance
        $mail = new Mail;

        // loop through array
        foreach ($data as $key => $val)
        {
            if (property_exists($mail, $key))
            {
                // set property
                $mail->{$key} = $val;
            }
        }

        // create transport
        self::createTransport($mail);

        // return mail class
        return $mail;
    }

    // create transport
    public static function createTransport(&$ins = null)
    {
        if (is_null(self::$transport))
        {
            // Create mail
            if (is_null($ins))
            {
                $ins = new Mail;
            }

            // Create the Transport
            $transport = (new \Swift_SmtpTransport($ins->smtpHost, $ins->smtpPort, $ins->smtpEncryption))
            ->setUsername($ins->smtpUser)
            ->setPassword($ins->smtpPass)
            ;

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // save transport
            self::$transport = $mailer;
        }

        return self::$transport;
    }

    // set subject
    public function subject(string $subject)
    {
        $this->mail_data['setSubject'] = $subject;
        return $this;
    }

    // set from
    public function from()
    {
        $args = func_get_args();
        $this->mail_data['setFrom'] = $args;
        return $this;
    }

    // set reciever
    public function to()
    {
        $args = func_get_args();
        $this->mail_data['setTo'] = $args;
        return $this;
    }

    // set body
    public function body(string $text)
    {
        $this->mail_data['setBody'] = $text;
        return $this;
    }

    // set html
    public function html(string $text)
    {
        $this->mail_data['addPart'] = [$text, 'text/html'];
        return $this;
    }

    // add attachment
    public function attach($file)
    {
        $this->mail_data['attach'] = \Swift_Attachment::fromPath($file);
        return $this;
    }

    // send mail
    public function send()
    {
        // get transport
        $transport = self::createTransport();
        
        // Create the message
        $message = new \Swift_Message();

        // read mail data
        if (count($this->mail_data) > 0)
        {
            foreach ($this->mail_data as $method => $args)
            {
                if (is_array($args))
                {
                    $message = call_user_func_array([$message, $method], $args);
                }
                else
                {
                    $message = call_user_func([$message, $method], $args);
                }
            }
        }

        $message = $transport->send($message);

        return $message;
    }
}