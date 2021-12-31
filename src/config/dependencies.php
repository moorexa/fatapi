<?php
/**
 * This packages would be installed via composer on your command.
 * Do ensure you have composer ready and installed.
 * To quickly install composer in the root directory, see https://getcomposer.org/download/
 */
return [
    'flag' => '--no-update',

    // This packages would be installed when you run "php assist install"
    'require' => [
        'symfony/yaml:^4.4',
        'monolog/monolog:^1.25', // optional
        'filp/whoops:^2.7', // optional
    ],

    // You can install this packages later
    // The command to use is "php assist install others"
    'others' => [
        "ext-pdo:*",
        "ext-mysqli:*",
        "ext-dom:*",
        "fzaninotto/faker:^1.9", // for generating fake test data
        "guzzlehttp/guzzle:~6.0", // for http requests
        "swiftmailer/swiftmailer:^6.0", // for sending of mails
    ],

    // packages for socket
    // The command to use is "php assist install socket"
    'socket' => [
        "ext-pcntl:*",
        "cboden/ratchet:^0.4.3", // for sockets using ratchet
        "workerman/phpsocket.io:^1.1", // for sockets using socket.io
    ],

    // packages for work queues
    // The command to use is "php assist install queue"
    'queue' => [
        "php-amqplib/php-amqplib:^2.11", // for work queues and background jobs using rabbitmq
        "opis/closure:^3.5", // for serializing queue closures
    ]
];