<?php
namespace Engine;
/**
 * @package ModelHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait ModelHelper
{
    /**
     * @method ModelInterface Fillable
     * @param array $data
     * @return void
     * 
     * Has data that can be populated to the class 
     */
    public function Fillable(array $data) : void
    {
        // unpack data
        foreach ($data as $key => $value) $this->{$key} = $value;
    }

    /**
     * @method ModelInterface Query
     * @param string $tableName
     * @return mixed
     */
    public function DB(string $tableName = '')
    {
        // DBMS connection name is empty
        if ($this->DBMSConnection == '') return app('screen')->render([
            'Status'    => false,
            'Message'   => 'You\'ve failed to set a connection method name for your model class "'. get_class($this) . '". Please fix this error and try again. Remember, a name is a method that must exist in the Engine\\DBMS class'
        ]);

        // All good
        return call_user_func([DBMS::class, $this->DBMSConnection], $tableName);
    }
}