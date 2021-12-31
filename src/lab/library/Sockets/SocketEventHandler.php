<?php
namespace Sockets;

use Lightroom\Socket\Interfaces\SocketListenerInterface;
/**
 * @package Socket event handler
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * This package would listen for events if registered as the default event listener.
 * You can register one or more events, listen for events like connected, disconnected, message and more.
 */
class SocketEventHandler implements SocketListenerInterface
{
    use SocketEvents;

    /**
     * @method SocketListenerInterface events
     * This method should register all your events to be listened for by the emit method
     * @return array
     */
    public static function events() : array
    {
        // you can also try using an external class with a static method
        // 'event' => ['class name', 'static method']

        // they should all be static methods visible to the public
        return [
            'connected' => 'newConnection',
            'disconnected' => 'connectionClosed',
            'new message' => 'newMessage'
        ];
    }

    /**
     * @method SocketListenerInterface emit
     * @param string $event
     * @param array $config
     * This would emit an event from the socket server when something happens
     */
    public static function emit(string $event, array $config = [])
    {
        // get class reflection
        static $handler;

        // load reflection class
        if ($handler === null) $handler = new \ReflectionClass(static::class);

        // @var array $events
        $events = self::events();

        // let's emit now
        foreach ($events as $eventName => $methodOrArray) :

            // check for event
            if ($event == $eventName) :

                // is array
                if (is_array($methodOrArray)) :

                    // load external
                    call_user_func($methodOrArray, $config['connection'], $config);

                else:

                    // check for method 
                    if ($handler->hasMethod($methodOrArray)) :

                        // call method
                        call_user_func([static::class, $methodOrArray], $config['connection'], $config);

                    endif;

                endif;

            endif;

        endforeach;
    }
}