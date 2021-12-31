<?php
namespace Engine;

use Lightroom\Database\Interfaces\DriverInterface;
use function Lightroom\Database\Functions\{db_with, db};
/**
 * @package DBMSHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */

trait DBMSHelper
{
    /**
     * @var array $tableLoaded
     */
    private static $tableLoaded = [];

    /**
     * @method DBMS CreateConnection
     * @param string $connectionName
     * @return DriverInterface
     */
    private static function CreateConnection(string $connectionName) : DriverInterface
    {
        // return database connection 
        return $connectionName == '' ? db() : db_with($connectionName);
    }

    /**
     * @method DBMS ConnectToTable
     * @param DriverInterface $connection
     * @param string $table
     * @return void
     */
    private static function ConnectToTable(DriverInterface $connection, string $table)
    {
        return $connection->table($table);
    }

    /**
     * @method DBMS FindTableFromConstant
     * @param string $table
     * @return string
     */
    private static function FindTableFromConstant(string $table) : string
    {
        if (isset(self::$tableLoaded[$table])) return self::$tableLoaded[$table];

        // get constant
        $constant = strpos($table, '\\') !== false ? constant($table) : constant(Table::class.'::'.$table);

        // cache table
        self::$tableLoaded[$table] = is_string($constant) ? $constant : $table;

        // return from cache
        return self::$tableLoaded[$table];
    }
}