<?php
namespace Engine;
use function Lightroom\Database\Functions\{db, db_with};
/**
 * @package SQLHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */
class SQLHelper
{
    /**
     * @var int $ID
     * Last insert ID
     */
    public static $ID = 0;

    /**
     * @method SQL __callStatic
     * @param string $queryName
     * @param array $options
     * @return 
     */
    public static function __callStatic(string $queryName, array $options)
    {
        // @var string $constantName
        $constantName = static::class . '::' . $queryName;

        // get pdo 
        $pdo = db()->pdo();

        // get the sql
        $sql = isset($options[0]) ? $options[0] : '';
        $data = isset($options[1]) ? $options[1] : [];

        // check if constant has been defined
        if (defined($constantName)) :

            // get the sql 
            $sql = constant($constantName);
            $data = $options;

            // is array?
            if (is_array($sql)) :

                // check for connection
                $pdo = isset($sql['connection']) && $sql['connection'] != '' ? db_with($sql['connection'])->pdo() : $pdo;
                $sqlStatement = isset($sql['sql']) ? $sql['sql'] : '';

                // check for helper
                if (isset($sql['helper']) && is_array($sql['helper'])) :

                    // load now
                    $sqlStatement = call_user_func_array($sql['helper'], [$sqlStatement, (isset($data[0]) ? $data[0] : $data)]);

                endif;

                // load sql
                $sql = $sqlStatement;

            endif;

        endif;

        // prepare statement
        $statement = $pdo->prepare($sql);
            
        // execute pdo statement
        call_user_func_array([$statement, 'execute'], $data);

        // commit transaction
        if ($pdo->inTransaction()) $pdo->commit();

        // Check for insertion
        if (stripos($sql, 'insert into ') !== false) self::$ID = $pdo->lastInsertId();

        // return statement
        return $statement;
    }

    /**
     * @method SQL extractValues
     * @param string $statement
     * @param array $values
     * @return string
     */
    protected static function extractValues(string $statement, array $values) : string
    {
        // get the keys
        $keys = array_keys($values);

        // add to statement
        $statement .= ' ('.implode(',', $keys).') VALUES ('.implode(',', array_map(function($e){
            return ':' . $e;
        }, $keys)).')';

        // return statement
        return $statement;
    }
}