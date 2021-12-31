<?php
use Lightroom\Adapter\Configuration\Interfaces\ConfigurationSocketInterface;

/**
 * @method ConfigurationSocketInterface configurationSocket
 * @var mixed $config
 * 
 * Build configuration socket setting
 * We are linking this method via ConfigurationSocketHandler
 * They read a class, and class a method that in turn pushes the return value the Lightroom\Adapter\Configuration\Environment class.
 * You can access this configurations via env(string name, mixed value);
 */

$config->configurationSocket([
	'alias'  => $socket->setClass(Lightroom\Core\BootCoreEngine::class)->setMethod('registerAliases'),
]);

// Application Aliases
$config->alias([
    Moorexa\View\Engine::class => get_path(constant('PATH_TO_EXTRA'), '/view-engine.php'),
]);