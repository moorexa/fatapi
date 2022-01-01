<?php
namespace Engine\Interfaces;

use Engine\RequestData;
use Lightroom\Database\Interfaces\QueryBuilderInterface as QueryBuilder;
/**
 * @package Model Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface ModelInterface
{
    /**
     * @method ModelInterface Fillable
     * @param RequestData $data
     * @return void
     * 
     * Has data that can be populated to the class 
     */
    public function Fillable(RequestData $data) : void;

    /**
     * @method ModelInterface Query
     * @param string $tableName
     * @return mixed
     */
    public function DB(string $tableName = '');

    /**
     * @method ModelInterface Create
     * @return bool
     * 
     * This creates a new entry in the database tables
     */
    public function Create() : bool;

    /**
     * @method ModelInterface Update
     * @return bool
     * 
     * This updates a new entry in the database
     */
    public function Update();

    /**
     * @method ModelInterface Create
     * @return bool
     * 
     * This delete a record in the database
     */
    public function Delete();

    /**
     * @method ModelInterface Read
     * @return bool
     * 
     * This reads an entry from the database
     */
    public function Read();

    /**
     * @method ModelInterface Filter
     * @param QueryBuilder $builder
     * @return QueryBuilder
     * 
     * This method would help us include filters into our queries,
     * You just need to pass your builder query into this function before adding ->go(); 
     */
    public function Filter(QueryBuilder $builder) : QueryBuilder;
}