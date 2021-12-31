<?php

/**
 * @package Basic Test for SocketTesting
 * @author Amadi Ifeanyi <amadiify.com>
 *
 * These are traits below. You can use which ever you desire, and also extend phpunit to
 * enjoy both benefits. The goal here is to increase productivity, use only what you need.
 */
use Lightroom\Test\DatabaseTest;
use Lightroom\Test\EventTest;
use Lightroom\Test\RouteTest;
use Lightroom\Test\SecurityTest;
use Lightroom\Test\TemplateTest;
use Lightroom\Socket\SocketClient;
use Lightroom\Test\TestCase as LightroomTestCase;

class SocketTesting
{
    use LightroomTestCase;
    
    /**
     * @var array $triggers
     * You can add some basic triggers to fast track your tests.
     * eg. 'callMethod1' => 'my_very_long_test_name'
     */
     public $triggers = ['connected' => 'can_socket_connect_locally'];

    // build method
    public function can_socket_connect_locally()
    {
        // create connectionn
        $socket = new SocketClient('0.0.0.0', '8082');

        // using the queue functionality

        // queue message
        $socket->queue('message', json_encode([
            'accountid' => 'ifeanyi',
            'sendTo'    => 'paul',
            'message'   => 'hello'
        ]));

        // queue message
        $socket->queue('message2', json_encode([
            'accountid' => 'ifeanyi',
            'sendTo'    => 'paul',
            'message'   => 'hello'
        ]));

        // queue message
        $socket->queue('meta.api', json_encode([
            'meta'     => [
                'service'   => 'user',
                'method'    => 'login'
            ],
            // 'header'    => [],
            // 'query'     => [],
            'method'    => 'post',
            'version'   => 'v1',
            'signature' => '8337sijdfu',
            'body'      => [
                'username' => 'chris',
                'password' => '1234'
            ]
        ]));

        // getting response from the signature
        // $socket->on('8337sijdfu', function($data){
        //     var_dump($data);
        // });

        // send all queues now
        $socket->send();

        // or just send a message out

        // send a message
        $socket->send('message2', json_encode([
            'accountid' => 'ifeanyi',
            'sendTo'    => 'paul',
            'message'   => 'hello'
        ]));
    }
}