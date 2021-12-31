<?php

use Classes\Cli\Assist;
use Lightroom\Socket\Interfaces\SocketHandlerInterface;

/**
 * @package Bash script for assist cli manager
 * @author Amadi Ifeanyi <amadiify.com>
 * This script can contain one or more jobs for your assist command
 */
// command helper
// Within this function, you can register some default options to your assist commands
if (defined('SYSTEM_TYPE') && SYSTEM_TYPE === 'services') :
    command_helper([
        'new page|new controller' => [
            '-excludeDir=Views,Custom,Partials,Packages,Static',
            function()
            {
                Assist::onDecrypt(function(&$content)
                {
                    // here you have the content
                    $content = str_replace('use function Lightroom\Templates\Functions\{render, redirect, json, view};', '', $content);

                    // remove $this->view->render
                    $content = preg_replace('/(\$this->view->render\([\'|"](\S+?)[\'|"]\)[;])/', 'app(\'screen\')->render([\'status\' => \'success\', \'message\' => \'route works!\']);', $content);
                });
            }
        ],
        'deploy' => [
            '--notrack'
        ],
        'new route' => [
            function()
            {
                Assist::onDecrypt(function(&$content)
                {
                    // remove $this->view->render
                    $content = preg_replace('/(\$this->view->render\([\'|"](\S+?)[\'|"]\)[;])/', 'app(\'screen\')->render([\'status\' => \'success\', \'message\' => \'route works!\']);', $content);
                });
            }
        ]
    ]);
else:
    // frontend possibly used.
    command_helper([
        'deploy' => [
            '--notrack'
        ]
    ]);
endif;

// return bash
try {
    return bash(
        [
            // load default test manager built into framework
            Lightroom\Packager\Moorexa\TestManager::class,

            // load external bash. Class must implement Classes\Cli\CliInterface
            // eg. MyNamespace\MyClass::class
        ],

        // inline bash
        [
            // this is not a predefined command but would execute those jobs if you run 'php assist init'
            'init' => [
                'start' => [
                    function () {
                        // Set RIGHT privileges for root dir:
                        Assist::runCliCommand('sudo chmod -R 777 ' . (isset($_SERVER['PWD']) ? $_SERVER['PWD'] : '') . '/');
                    },
                    // generate unique openssl key
                    //'php assist generate certificate',
                    // we would clean up the caching system
                    'php assist cache clean',
                    // we would generate a new secret key for our application and for encryption
                    'php assist generate key',
                    // we would generate a new secret key salt for our application and for encryption
                    'php assist generate key-salt',
                    // we would also generate a csrf-key to prevent our app from cross site scripting
                    'php assist generate csrf-key',
                    // we would also generate an assist-token for our CLI utility manager
                    'php assist generate assist-token',
                ]
            ],
            // we use this for scaffolding . You can try 'php assist make auth' or basic
            'make' => [
                'start' => function (string $args, array $arguments) {
                    return 'php assist scaffold:' . $args;
                }
            ],
            // we use this for running GIT jobs. basically, with this you can push your code to github.
            // ensure you have git installed and you have remote origin configured before using this command
            // you can update it to suit your needs.
            'commit' => [
                'start' => [
                    // check branch
                    'git status',
                    // add all files
                    'git add .',
                    // commit to branch
                    function () {
                        // get commit message
                        Assist::out('Commit message (Enter new line to save or -s):');
                        $lines = [];

                        while ($line = Assist::readline()) {
                            if ($line == '' || $line == '-s') {
                                break;
                            } else {
                                $lines[] = $line;
                            }
                        }

                        // save message
                        $message = implode("\n", $lines);

                        if ($message == null || $message == '') {
                            $message = 'initial commit.';
                        }

                        // commit with message
                        return "git commit -m '$message'";
                    },
                    // push commit
                    'git push -f origin master'
                ]
            ],
            'conf' => [
                'start' => function (string $arg, array $args) {
                    if ($args[0] == 'ubuntu') {
                        // enable mod rewrite engine and add conf
                        //Console\Conf\Ubuntu\UbuntuConf::mod_rewrite();

                        // enable Apache’s virtual host for site domain
                        Console\Conf\Ubuntu\UbuntuConf::site_default();

                        // enable Apache’s secure virtual host for site domain
                        //Console\Conf\Ubuntu\UbuntuConf::site_secure();
                    }
                }
            ],
            // run migration for session database drivers
            'session' => [
                'start' => function () {
                    // read env
                    $driverClass = env('session', 'class');

                    // check if class exists
                    if (is_string($driverClass) && class_exists($driverClass)) :

                        // create reflection class
                        $reflection = new ReflectionClass($driverClass);

                        // get file location
                        $file = $reflection->getFileName();

                        // get base name
                        $fileName = basename($file);

                        // remove base from $file, just get the directory
                        $directory = rtrim($file, $fileName);

                        // remove extension
                        $fileName = substr($fileName, 0, strrpos($fileName, '.'));

                        // use session connection identifier
                        $identifier = env('session', 'identifier');

                        // we have an identifier
                        if (is_string($identifier) && strlen($identifier) > 1) if (!defined('USE_CONNECTION')) define('USE_CONNECTION', $identifier);

                        // build command
                        return 'php assist migrate ' . $fileName . ' -from=' . $directory;
                    else:

                        self::out('Session driver not found. Could not run migration.');

                    endif;
                }
            ],
            // run migration for cookie database drivers
            'cookie' => [
                'start' => function () {
                    // read env
                    $driverClass = env('cookie', 'class');

                    // check if class exists
                    if (is_string($driverClass) && class_exists($driverClass)) :

                        // create reflection class
                        $reflection = new ReflectionClass($driverClass);

                        // get file location
                        $file = $reflection->getFileName();

                        // get base name
                        $fileName = basename($file);

                        // remove base from $file, just get the directory
                        $directory = rtrim($file, $fileName);

                        // remove extension
                        $fileName = substr($fileName, 0, strrpos($fileName, '.'));

                        // use cookie connection identifier
                        $identifier = env('cookie', 'identifier');

                        // we have an identifier
                        if (is_string($identifier) && strlen($identifier) > 1) if (!defined('USE_CONNECTION')) define('USE_CONNECTION', $identifier);

                        // build command
                        return 'php assist migrate ' . $fileName . ' -from=' . $directory;
                    else:

                        self::out('Cookie driver not found. Could not run migration.');
                    endif;
                }
            ],
            // start the socket server
            'socket' => [
                'start' => function () {
                    // get instance
                    $ass = Assist::getInstance();

                    // moorexa image
                    $text = 'czo5MTQ6Ijsiczo4OTY6Ik5qZE9hd3FjY21nY2dhdkJXVzRkY3RYdWl2azJ2Q2VhRVdXbDBSS04rbGx3dkZ3dE1oRjRWTVRYK3BGOGtJRXZMOHlEeFVYYVBKa2Z1WmxOemsxa2pGSmlDQVd6Y0dmMCtOS0FKc2Jqc1N0c2luWHRST05BSnlEOG1qUVRJWUVSZHNFdHlSOGdGQ3hISUt1Mk5WdTZBamVIcUpnUGJRWnN5b25jTytLRk9tcTJnMTYwZ1IzY1AyR2NCV2tGVzBvQ3lIWDRsdUkxSE9kVUN4SkIwcCtSek85TlZOcWpBZUNoZzRxRXJuS0FuakpNSHFhaWtwYU5lN282dDJJa2x2YmhDY2xUYVFXcm15ejV4b1NhSlN5cVF4VmRkR2pZUEt3aytFN2hkdjkrY1RJelBZVXVqcU5LQ2xZZ2ZRY0pYdUtJcTM1QS8va05RcGRjOFlCR0N0eWo5UXFOTFpzUUFaQWVxcGdTWlhsZG9EQllWOFhyMHQ1bk9NMWR1VFNOK1pueTlKd0xzSWlBZWpGS0ZpVk1IQWFXNTJBc21BODdWTzA2c0gvbGQ1d3A2WVNtUWE3ZncwOWNsSlRQZ05xdW5xS01PYWhhMWJjQjAvWjd3c0NianJOTzBkYndKZW1DdVVDaE9MWVY0TWhYUmpsQkV2T0J2L1VNL0dVNHpPN21KdWU0RC9aV0lnckRqLzd3ME1VL1pVSXNvcmZ2RDdWWW8vK1ZKakJRZ1dFdC93Z3haeDF3cG9RdS92c2FybjR1OGZ1NnJ5bHFkc21FNWZMV0g0WVg0anlnam4vOWJMVE1lbldKajlMeWRmdEZCSDNPc2ljSCtFYUdOUVVlOWFWNWhjSCtXZENIRkxjcUg5Z0wzUHJCSldwS2Foa3RGQTUvZ3VXd0VHaS9sTms3Tklsb0JPbWZGVWhSYlVsZm92V2dMWWRySnZWUXdGcmx0SkFnWnl6THhDMzdxcU5yZUEvemtqUzlnTVRQUUZjdU4rZStFN0hOcFpNNm9ESCtTY1I5M3E1YzVVczFyRXVvTEIwRFJmTTI2MW9TMWR6eGFxWEd6Q05yanlnMHF0OEZpVXY5Zmx2em9QQVZ5QmVVZldCWXZ6WUtqTERUMEJJaVBnNENVMHpkcndJYXpCVHVPSTVFdWlpVTZIMm5XYngrWEN3TVBEVFZ5QUZPOTZPUThJQm44algxIjsiOjUwOTpzIjs=';

                    // write image to screen
                    fwrite(STDOUT, $ass->ansii('green') . decryptAssist($text) . $ass->ansii() . PHP_EOL);
                    self::out($ass->ansii('bold') . "Socket Server Starting..");

                    // read the env file
                    if (isset($_ENV['socket']) && isset($_ENV['socket']['handler'])) :

                        // @var string $handler
                        $handler = $_ENV['socket']['handler'];

                        // @var active handler
                        $activeHandler = $handler;

                        // @var string $command
                        $command = isset($_ENV['socket']['command']) ? $_ENV['socket']['command'] : null;

                        // selected handler
                        $handlerSelected = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : $handler;

                        // check now
                        if ($handlerSelected !== null && isset($_ENV['socket']['handlers'][$handlerSelected])) :

                            // replace handler
                            $handler = $_ENV['socket']['handlers'][$handlerSelected];

                            // is array
                            if (is_array($handler)) :

                                // update command
                                $command = isset($handler['command']) ? $handler['command'] : $command;

                                // get class path
                                $handler = $handler['handler'];

                            endif;

                            // update active handler
                            $activeHandler = $handlerSelected;

                        else:
                            // for debugging
                            $handler = $handlerSelected;
                        endif;

                        // load handler
                        if (class_exists($handler)) :

                            // load reflection
                            $reflection = new \ReflectionClass($handler);

                            // check if class implements SocketHandlerInterface
                            if ($reflection->implementsInterface(SocketHandlerInterface::class)) :

                                // run server
                                self::out($ass->ansii('bold') . "Started..");

                                // update argv
                                if ($command !== null) $_SERVER['argv'][1] = $command;

                                // update argv
                                if (isset($_SERVER['argv'][3])) :

                                    unset($_SERVER['argv'][1]);

                                    // sort array
                                    sort($_SERVER['argv']);

                                endif;

                                // add to glob
                                $GLOBALS['argv'] = $_SERVER['argv'];

                                // add to glob
                                $GLOBALS['activeHandler'] = $activeHandler;

                                // start
                                call_user_func([$handler, 'startServer']);

                            else:
                                // socket interface
                                self::out($ass->ansii('red') . 'Could not find socket interface ' . SocketHandlerInterface::class . ' for class ' . $handler . '.' . $ass->ansii());
                            endif;

                        else:
                            // socket handler class not found
                            self::out($ass->ansii('red') . 'Could not find socket handler ' . $handler . '.' . $ass->ansii());
                        endif;

                    else:
                        // oops
                        self::out($ass->ansii('red') . 'Could not find a default socket handler.' . $ass->ansii());
                    endif;
                }
            ],
            // install script
            'install' => [
                'start' => function (string $command) {

                    // replace name
                    $command = (trim($command) == '') ? 'require' : $command;

                    // install composer
                    if ($command == 'composer') :

                        // check note
                        Assist::out('Checking for composer in root directory' . PHP_EOL);

                        // check for composer
                        if (file_exists(HOME . '/composer')) : return Assist::out('Could not continue. You already have composer installed.'); endif;

                        // run composer installation
                        pclose(popen('php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"', "w"));
                        pclose(popen('php -r "if (hash_file(\'sha384\', \'composer-setup.php\') === \'906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8\') { echo \'Installer verified\'; } else { echo \'Installer corrupt\'; unlink(\'composer-setup.php\'); } echo PHP_EOL;"', 'w'));
                        sleep(2);

                        // continue if file exists
                        if (file_exists(HOME . '/composer-setup.php')) :
                            
                            pclose(popen('php composer-setup.php --filename=composer', 'w'));
                            pclose(popen('php -r "unlink(\'composer-setup.php\');"', 'w'));
                            
                        endif;

                    else :

                        // check for composer
                        $composerFiles = [HOME . '/composer', HOME . '/composer.phar'];

                        // load dependencies file
                        $dependencies = get_path(func()->const('config'), '/dependencies.php');

                        // load from array ?
                        if (file_exists($dependencies)) :

                            // read data
                            $data = include $dependencies;

                            // continue with array
                            if (is_array($data)) :

                                // load packages
                                if (isset($data[$command])) :

                                    // load flag
                                    $flag = isset($data['flag']) ? $data['flag'] : '--no-update';

                                    // list as string
                                    $packages = [];

                                    // run loop
                                    foreach ($data[$command] as $id => $package) :
                                        // add
                                        $packages[] = ($package == '*') ? ("'$id:$package'") : "'$package'";
                                    endforeach;

                                    // finally,
                                    $packages = implode(' ', $packages);

                                    // add flag
                                    $packages .= ' ' . $flag;

                                    // start
                                    $start = 'composer require ';

                                    // process now
                                    foreach ($composerFiles as $file) :

                                        // which is available
                                        if (file_exists($file)) :

                                            // get file
                                            $start = 'php ' . basename($file) . ' require ';

                                            // break out
                                            break;

                                        endif;

                                    endforeach;

                                    // append packages
                                    $packages = $start . $packages;

                                    // should we update composer at the end ?
                                    if (strpos($packages, '--no-update') !== false) $packages .= '; ' . str_replace('require', 'update', $start) . ';';

                                    // process now
                                    Assist::runCliCommand($packages);

                                else:

                                    // could not load package
                                    Assist::out('Could not load packages within this category "' . $command . '", check spellings.' . PHP_EOL);

                                endif;

                            else:

                                // invalid file
                                Assist::out('Invalid dependency file \'' . $dependencies . '\'. Expected a return type of array.' . PHP_EOL);

                            endif;

                        endif;

                    endif;
                }
            ],
            // you can register more jobs here
        ]);
} catch (\Lightroom\Exceptions\ClassNotFound $e) {
} catch (\Lightroom\Exceptions\InterfaceNotFound $e) {
}