<?php 
use FileDB\FileDBClient;

// simple wrapper
function fdb($data = null)
{
    // load data
    if ($data !== null) return FileDBClient::load($data);

    // return instance
    return new FileDBClient;
}