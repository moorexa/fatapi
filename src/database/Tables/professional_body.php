<?php

/**
 * @package Database Table Professional_body
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Professional_body, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Professional_body
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up($schema)
    {
         
    }

    // drop table
    public function down($drop, $record)
    {
        // $record carries table rows if exists.
        // execute drop table command
        $drop();
    }

    // options
    public function option($option)
    {
        $option->rename('professional_body'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'waiting')
        {
            // do some cool stuffs.
            // $this->table => for ORM operations to this table.
        }
    }
}