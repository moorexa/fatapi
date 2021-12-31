<?php

/**
 * @package Database Table Users
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Users, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Users
{
    // connection identifier
    public $connectionIdentifier = '';


    // create table structure
    public function up($schema)
    {
        // and more.. 
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
        $option->rename('users'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'waiting')
        {
            $table->insert([
                'username' => 'admin2',
                'email' => 'helloamadiify2@gmail.com',
                'usergroupid' => 2
            ]);
        }
    }
}