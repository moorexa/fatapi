<?php
namespace Engine;

use Engine\RequestData;
use function Lightroom\Database\Functions\{db_with, db};
/**
 * @package ModelHelper
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait ModelHelper
{
    /**
     * @method ModelInterface Fillable
     * @param RequestData $data
     * @return void
     * 
     * Has data that can be populated to the class 
     */
    public function Fillable(RequestData $data) : void
    {
        // get data
        $requestData = $data->getData();

        // unpack data
        foreach ($requestData as $key => $value) $this->{$key} = $value;
    }

    /**
     * @method ModelInterface Query
     * @param string $tableName
     * @return mixed
     */
    public function DB(string $tableName = '')
    {
        // DBMS connection name is empty
        if ($this->DBMSConnection == '') return db($tableName);

        // All good
        return call_user_func([DBMS::class, $this->DBMSConnection], $tableName);
    }
}