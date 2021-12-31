<?php

/**
 * @package Database Table Global_status
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Global_status, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Global_status
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
        $option->rename('global_status'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'complete')
        {
            // add record
            $records = [
                ['status_name' => 'Activated'],
                ['status_name' => 'Approved'],
                ['status_name' => 'Awaiting Admin Action'],
                ['status_name' => 'Awaiting Payment'],
                ['status_name' => 'Awaiting Transcript'],
                ['status_name' => 'Declined by Vetting Officer'],
                ['status_name' => 'Declined for additional details'],
                ['status_name' => 'Expired'],
                ['status_name' => 'Failed'],
                ['status_name' => 'Recommended for Council'],
                ['status_name' => 'Recommended for further vetting'],
                ['status_name' => 'Recommended for Interview'],
                ['status_name' => 'Rejected By Council'],
            ];

            // add to database table
            foreach ($records as $record) $table->insert($record);
        }
    }
}