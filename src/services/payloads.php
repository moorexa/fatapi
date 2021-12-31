<?php

use Moorexa\View\Engine as ViewEngine;
use Lightroom\Common\CsrfRequestManager;
use Lightroom\Templates\TemplateHandler;
use function Lightroom\Functions\GlobalVariables\var_set;
/**
 * @package Payloads Registry. 
 * @author Amadi Ifeanyi <amadiify.com>
 * @var mixed $payload
 * @var array $incomingUrl
 * 
 * This file gives you the ability to add payloads to the controller stack. If you choose Moorexa MVC, this file
 * would be called after the middleware payload has been attached.
 * 
 * Here you have access to $payload variable, $incomingUrl and the Controller ViewHandler class itself with $this
 */

 // export incoming url
 var_set('incoming-url', $incomingUrl);

 /**
  * @method CsrfRequestManager
  * This payload registers a default csrf manager for forms. you can import its functions from common directory. example.
  * use function Lightroom\Common\Functions\{csrf, csrf_error, csrf_verified}
  */
 $payload->register('load-csrf-manager', $payload->handler(CsrfRequestManager::class)->arguments('loadFormCsrf'));

 