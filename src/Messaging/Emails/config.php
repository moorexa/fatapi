<?php
/**
 * Configuration for sending out emails
 */
return [
    'default' => 'Messaging\Emails\Handlers\SymfonyMailer',
    'dsn' => 'smtp://{user}:{pass}@{host}:{port}',
    'host' => 'smtp.mailtrap.io',
    'port' => 2525,
    'user' => '',
    'pass' => ''
];