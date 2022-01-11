<?php
namespace Engine;

use Lightroom\Database\Interfaces\DriverInterface;
/**
 * @package DBMS 
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * To generate a new database configuration please run:
 * $ php assist database add {name}
 * 
 * replace {name} with a connection name. eg service2,authentication
 * 
 * and follow the next prompt on your console.
 */
class DBMS
{
    use DBMSHelper;

    /**
     * @method DBMS Connection1
     * @param string $table
     * @return DriverInterface|
     */
    public static function Connection1(string $table = '') 
    {
        // connection name
        $connectionName = '';

        // get connection
        $connection = self::CreateConnection($connectionName);

        // has table
        return $table != '' ? self::ConnectToTable($connection, $table) : $connection;
    }
}