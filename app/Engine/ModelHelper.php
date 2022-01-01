<?php
namespace Engine;

use Engine\RequestData;
use function Lightroom\Requests\Functions\{get};
use function Lightroom\Database\Functions\{db_with, db};
use Lightroom\Database\Interfaces\QueryBuilderInterface as QueryBuilder;
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

    /**
     * @method ModelInterface Filter
     * @param QueryBuilder $builder
     * @param array $option
     * @return QueryBuilder
     * 
     * This method would help us include filters into our queries,
     * You just need to pass your builder query into this function before adding ->go(); 
     */
    public function Filter(QueryBuilder $builder, array $option = []) : QueryBuilder
    {
        // where data
        $whereData = isset($option['where']) && is_array($option['where']) ? $option['where'] : [];

        // column
        $columnData = isset($option['column']) ? $option['column'] : '*';

        // load get class
        $getQuery = get();

        // has query
        if (isset($option['query']) && is_array($option['query'])) :

            // load all
            foreach ($option['query'] as $key => $value) :

                // is key and value string?
                if (is_string($key) && is_string($value)) :

                    // check if GET has query
                    if ($getQuery->has($key)) : $whereData[$value] = $getQuery->get($key); endif;

                elseif (is_numeric($key) && is_string($value)):

                    // check if GET has query
                    if ($getQuery->has($value)) : $whereData[$value] = $getQuery->get($value); endif;

                endif;

            endforeach;

        endif;

        // has column
        if ($getQuery->has('column')) $columnData = $getQuery->column;

        // clean column data
        if ($columnData != '*' && isset($option['allowedColumns'])) :

            // create array
            $columnDataArray = explode(',', $columnData);

            // create rule
            $allowedColumns = $option['allowedColumns'];

            // reset column $data
            $columnData = [];

            // push to column data
            foreach ($columnDataArray as $column) if (isset($allowedColumns[$column])) $columnData[] = $allowedColumns[$column]; 

            // empty ?
            $columnData = count($columnData) == 0 ? '*' : $columnData;

        endif;

        // return builder
        return $builder
        ->get(is_array($columnData) ? implode(',', $columnData) : '*')

        // add where statement
        ->where($whereData)

        // add sorting
        ->if($getQuery->has('sort'), function($builder) use (&$option){
            if (isset($option['primary'])) $builder->orderBy($option['primary'], get()->sort);
        })

        // add page limit
        ->if($getQuery->has('limit'), function($builder){
            $builder->limit(0, get()->limit);
        });
    }

    /**
     * @method ModelHelper cleanUpNullData
     * @return object
     */
    public function cleanUpNullData() : object
    {   
        // load all properties
        $properties = json_decode(json_encode($this));

        // now we create an empty array
        $notNullData = [];

        // we loop through
        foreach ($properties as $key => $value) if ($value != null) $notNullData[$key] = $value;

        // return object
        return (object) $notNullData;
    }   
}