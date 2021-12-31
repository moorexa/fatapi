<?php
use Lightroom\Packager\Moorexa\Router as Route;
/**
 * @author FregateLab <fregatelab.com>
 * This would load a request file by the request method
 */

// GET Verb handler
Route::resource(Verb\GET\Handler::class);

// POST Verb handler
Route::resource(Verb\POST\Handler::class);
