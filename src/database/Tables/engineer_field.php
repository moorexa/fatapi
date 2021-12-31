<?php

/**
 * @package Database Table Engineer_field
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Engineer_field, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Engineer_field
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
        $option->rename('engineer_field'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'complete')
        {
            // do some cool stuffs.
            $records = explode(',', 'Aeronautical, Aerospace, Agric Mechanics, Agricultural, Air Conditioning, Arc Welding, Auto-Electrician, Automobile Engineering, Automobile Mechanics, Automotive Biomedical, Blocklaying, Blocklaying & Concreting, Bricklaying, Bricklaying & Masonry, Building, Building And Construction Works, Cabinet Making, Carpentary, Joinery, Carpentry, Ceramic, Chemical, Civil, Communication, Computer, Diesel Mechanic, Electrical, Electrical Installation, Electronics Fabrication, Fabrication & Welding, Fitting, Food Foundry, Gas, General Fitting, Industrial, Information Technology, Irrigation, Manufacturing, Marine, Mechanical, Mechanical Engineering Craft, Metal Fabrication, Metallurgical, Mining, Motor Vehicle Mechanic, Naval Architect, Painting, PAINTING & DECORATION, Petroleum, Pipe Fitting, Plant Mechanics, Plumbing, Plumbing & Pipefitting, Polymer, POLYMER & TEXTILE, PRINTING & SIGNWRITING, Production, Radio & TV, Radio & Tv Mechanics, Refrigeration & Airconditioning, Software, Steel Making, Systems, Textile, Turning, Tv Mechanics, Water Resources, Welding, WELDING AND FABRICATION, Wood Products, Wood Technology');

            // add to database
            foreach ($records as $record) :
                $table->insert(['engineerfield' => trim($record), 'createdby' => 1]);
            endforeach;
        }
    }
}