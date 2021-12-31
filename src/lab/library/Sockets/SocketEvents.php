<?php
namespace Sockets;
/**
 * @package SocketEvents 
 * @author Amadi Ifeanyi <amadiify.com>
 * This trait should contain all your event menthods
 */
trait SocketEvents
{
    // new connection ?
    public static function newConnection($connection)
    {

    }

    // disconnected ?
    public static function connectionClosed($connection)
    {
        
    }

    // new messaage
    public static function newMessage($connection, $data)
    {
        
    }
}