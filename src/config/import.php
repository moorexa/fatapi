<?php

/**
 * @package Import files through require_once
 * @author Amadi Ifeanyi <amadiify.com>, <wekiwork.com>
 * 
 * Provided by the Moorexa Packager.
 */

return [
    // Will require all the files contained in the 'init' array by default
    'init' => [
        // file paths seperated by comma (,)
        HOME . 'app/autoloader.php',
    ],

    // create yours here and require when you need them
    /**
     * example
     * 'user-files' => [
     * ...files heere
     * ]
     * 
     * you can later call them via;
     * @var array $userFiles
     * $userFiles = import('@user-files');
     * 
     */
];