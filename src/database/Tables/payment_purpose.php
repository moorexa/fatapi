<?php

/**
 * @package Database Table Payment_purpose
 * @author Amadi ifeanyi <amadiify.com>
 * 
 * This class provides an handler for Database table Payment_purpose, it can work with any database system,
 * it creates a table, drops a table, alters a table structure and does more. 
 * with the assist manager you can run migration and do more with this package.
 */
class Payment_purpose
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
        $option->rename('payment_purpose'); // rename table
        $option->engine('innoDB'); // set table engine
        $option->collation('utf8_general_ci'); // set collation
    }

    // promise during migration
    public function promise($status, $table)
    {
        if ($status == 'waiting')
        {
            // do some cool stuffs.
            $records = explode(',', 'Donation(s), Publication(s), Conference Support, Advert(s), Codet Fee, Sales of Past Assembly Materials, Proceed of Disposal of Asset, Refund, Tender Bidding, Full Registration Fee(March 2017 and earlier), Other, Purchase of Security Seal, Purchase of Iron Seal, 2016 Exhibition Payment, 2017 Exhibition Payment, Purchase of Stamp, Full Registration Fee (July 2017 Approvals), Full Registration Fee (September 2017 Approvals), Full Registration Fee (December 2017 Approvals), Full Registration Fee (March 2018 Approvals), 2018 Exhibition Payment, Full Registration Fee (July 2018 Approvals), Full Registration Fee (September 2018 Approvals), Full Registration Fee (December 2018 Approvals), Full Registration Fee (March 2019 Approvals), 2019 Exhibition Payment, Full Registration Fee (September 2019 Approvals), Full Registration Fee ( December 2019 Approval ), 2021 Exhibition Payment');

            // add to database
            foreach ($records as $record) :
                $table->insert(['paymentpurpose' => trim($record)]);
            endforeach;
        }
    }
}