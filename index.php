<?php

// set script access control headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');

// is option
$preflightMode = isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) ? true : false;

// update options
if (!$preflightMode) $preflightMode = isset($_SERVER['X-REQUEST-METHOD']) && $_SERVER['X-REQUEST-METHOD'] == 'OPTIONS' ? true : false;

// check request method
if (!$preflightMode) $preflightMode = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ? true : false;

// must satisfy pre-flight request
if ($preflightMode) call_user_func(function(){
    
    // set status code
    http_response_code(200);

    // kill all
    die;
});

// load config
require_once 'app.config';

// load framework index file
require_once APPLICATION_ROOT . '/index.php';