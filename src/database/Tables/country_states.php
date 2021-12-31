<?php

/**
 * @package Database Table Country_states
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Country_states, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Country_states
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
        $option->rename('country_states'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'waiting')
        {
            // do some cool stuffs.
            $records = explode(',', 'Abia, Adamawa, Akwa Ibom, Anambra, Bauchi, Bayelsa, Benue, Bornu, Cross River, Delta, Ebonyi, Edo Ekiti, Enugu, Federal Capital Territory, Gombe, Imo, Jigawa, Kaduna, Kano, Katsina, Kebbi, Kogi, Kwara, Lagos, Nasarawa, Niger, Ogun, Ondo, Osun, Oyo, Plateau, Rivers, Sokoto, Taraba, Yobe Zamfara');

            // add to database
            foreach ($records as $record) :
                $table->insert(['state_name' => trim($record), 'countryid' => 1]);
            endforeach;
        }
    }
}