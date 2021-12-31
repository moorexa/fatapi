<?php

use Lightroom\{
   Core\BootCoreEngine, Core\Payload, 
   Core\PayloadRunner, Adapter\ClassManager
};

// include the init file
require_once 'init.php';

/**
 * @package  Moorexa PHP Framework
 * @author   Fregatelab inc http://fregatelab.com
 * @author   Amadi ifeanyi http://amadiify.com
 * @version  0.0.1
 */

try {

   // create BootCoreEngine instance
   $engine = ClassManager::singleton(BootCoreEngine::class);

   // create Payload instance
   $payload = ClassManager::singleton(Payload::class)->clearPayloads();

   // display errors
   $engine->displayErrors(true);

   // apply default character encoding
   $engine->setEncoding('UTF-8');

   // apply default time zone
   $engine->setTimeZone(constant('DEFAULT_TIME_ZONE'));

   // apply default content type
   $engine->setContentType(constant('DEFAULT_CONTENT_TYPE'));

   // clear buffer if sent already
   if (strlen(ob_get_contents()) > 5) ob_end_clean();

   // load default packager
   if (isset($MAIN_PACKAGER)) $engine->defaultPackageManager($payload, $MAIN_PACKAGER);

   // boot application
   $engine->bootProgram($payload, ClassManager::singleton(PayloadRunner::class)->clearPayloads());

} catch (\Lightroom\Exceptions\ClassNotFound $e) {}