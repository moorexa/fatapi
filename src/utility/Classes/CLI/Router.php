<?php

// check for file
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';

// get document root
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

// clean url
$cleanRequestUrl = $requestUri;

// check for query
if (strpos($requestUri, '?') !== false) $cleanRequestUrl = substr($requestUri, 0, strpos($requestUri, '?'));

// check if is file
if (is_file($documentRoot . '/' . $cleanRequestUrl)) :

    // include functions
    include_once 'Assist/Functions.php';

    // get file path
    $file = $documentRoot . '/' . $cleanRequestUrl;

    // clean output
    ob_clean();

    // get mime type
    $mimeType = get_mime($file);

    // include the content type
    if ($mimeType != '') :

        header('Content-Type: '.$mimeType);

        // get file content
        echo file_get_contents($file);

    else:

        if (basename($file) == 'index.php') :

            // remove index.php
            $requestUri = str_replace('index.php', '', $requestUri);

            // update server
            $_SERVER['REQUEST_URI'] = $requestUri;

        endif;

        // include file
        include_once $file;

    endif;

else:

    include_once $documentRoot . '/index.php';

endif;

return;