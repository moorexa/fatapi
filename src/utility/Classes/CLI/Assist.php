<?php
namespace Classes\Cli;

use PDO,Closure;
use ZipArchive;
use Console_Table;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Lightroom\Adapter\ClassManager;
use Lightroom\Packager\Moorexa\Router;
use Lightroom\Packager\Moorexa\MVC\Helpers\ControllerLoader;
use Lightroom\Database\ConnectionSettings as Connection;
use function Lightroom\Database\Functions\{query, schema, db, db_with};
use Lightroom\Adapter\Configuration\Environment;

// define GLOB_BRACE
if(!defined('GLOB_BRACE')) define('GLOB_BRACE', 128);

/**˚
 * @package Moorexa CLI Assist Manager
 * @version 0.0.1
 * @author  Ifeanyi Amadi <amadiify.com>
 */
class Assist
{
    // version, not to worry! This is incremental.
    private static $version = '0.0.1';

    // available commands
    public static $commands = [
        'commands' => 'List all the avaliable commands',
        'help' => "Displays a list of available commands",
        'generate' => 'Generates encryption key, key salt, csrf salt key',
        'about' => "Short information about moorexa",
        'backup' => "Creates a complete backup of app",
        'migrate' => "Performs a database migration",
        'restore' => "Restore from last backup",
        'new' => "Can be used to add the following, [arguments] (page, model, package, hyphe templates, middleware, guard, route, table, cli command, provider).",
        'doc' => "Gives you a comprehensive documentation about moorexa for quick guide.",
        'version' => "Shows moorexa,assist,composer versions.",
        'database' => "Can add database configuration, clean database config file, set defaults, create channels.",
        'table' => 'Can add a table to database, update, delete, insert, describe, get rows, show records, drop, clean, generate data',
        'serve' => 'Opens up PHP built in sever for this app.',
        'credits' => 'Shows a list of packages running on this version and credits the authors who wrote them.',
        'page' => 'Allows you set a page as default, generate avialiable routes',
        '<function>' => 'Call a function that exists within application',
        'deploy' => 'Deploy project to production server',
        'system' => 'Allows you check for system updates and make them.',
        'cache' =>  'Helps clean up file caches.'
    ];

    // assist path
    public static $assistPath = HOME;

    // controller base path
    public static $controllerBasePath = PATH_TO_WEB_PLATFORM;

    // avaliable options
    public static $options = [
        '-h' => "will call help for a command or show general help usage. Usage 'assist -h' 'assist open -h' etc",
        '-v' => "will call the version command. Usage 'assist -v'"
    ];

    // commands help
    public static $commandHelp = [
        'new package' => [
            'info' => 'Create a re-usable package in pages/<page>/packages and can be shared between controllers.'
        ],
        'new hy' => [
            'info' => 'Generates a Reusable Template Syntax for Dynamic HTML',
            'optional commands' => [
                'new hy <component> -<path>'
            ],
            'usage' =>
                [
                    'php assist new hy <component>' => 'Generate/Overwrite and save to ./chs/',
                    'php assist new hy <component> -<path>' => 'Generate/Overwrite and save to <path>',
                    'php assist new hy <component>,<component> -<path>' => 'Generate/Overwrite and save to <path>'
                ],

            'component naming style' => [
                'component-body' => 'becomes ComponentBody',
                'componentBody' => 'becomes ComponentBody',
                'component' => 'becomes Component',
                'Component' => 'remains Component'
            ]
        ],
        'new guard' => [
            'info' => 'Generates a guard handler',
            'optional commands' => [
                'new guard <name> -<path>'
            ],
            'usage' =>
                [
                    'php assist new guard <name>' => 'Generate/Overwrite and save to ./utility/Guards/',
                    'php assist new guard <name> -<path>' => 'Generate/Overwrite and save to <path>',
                    'php assist new guard <name>,<name> -<path>' => 'Generate/Overwrite and save to <path>',
                    'php assist new guard <name>,<name> -dir=<directory>' => 'Generate/Overwrite and save to <directory>',
                ],

            'guard naming style' => [
                'name' => 'becomes NameGuard',
                'nameBody' => 'becomes NameBodyGuard',
                'Name' => 'remains NameGuard'
            ]
        ],
        'new table' => [
            'info' => 'Generates a database table php file for defining table structure, renaming, dropping etc.',
            'options' => [
                '-<path>'
            ],
            'usage' => [
                'php assist new table <tableName>',
                'php assist new table <tableName> -<path>' => 'Generates table file and save to path.',
                'php assist new <assistCommand>:new <tableName>',
                'php assist new <assistCommand>:new <tableName> -dir=<directory>' => 'Generates table file and save to directory.'
            ]
        ],
        'new console' => [
            'info' => 'Generates a console helper file in utility/console/helper for additional CLI Assist commands.',
            'register' => 'Generated console helper must be registered in kernel/assist.php, see example below;
                    "<shortcut-pointer>" => [
                        "path" => PATH_TO_CONSOLE <or your path>,
                        "assist" => PATH_TO_CONSOLE . "<console-filename>.php"
                    ]
                ',
            'options' => [
                '-<path>' => 'Change save path to user prefarred directory.'
            ],
            'usage' => [
                'php assist new console <helper-name>',
                'php assist new console <helper-name> -<path>',
                'php assist new clihelper <helper-name> -<path>',
                'php assist new clihelper <helper-name>',
                'php assist new clihelper <helper-name> -dir=<directory>',
            ]
        ],
        'backup' => [
            'info' => 'Creates a backup and saves in lab/backup directory. Format : backupYear-Month-Day.zip',
            'usage' => 'php assist backup'
        ],
        'restore' => [
            'info' => 'Restores from last backup or you can pass additional condition',
            'usage' => [
                'php assist restore',
                'php assist restore <backup name>'
            ]
        ],
        'clean' => [
            'info' => 'Command wipes everything and restores default behavior. In essence, all files that\'s not default would be lost.',
            'options' => [
                '-keep=dirs' => "reserves directory after clean up. seperate path with comma (,) eg"
            ],
            'usage' => [
                'php assist clean',
                'php assist clean -keep=pages,lab/backup'
            ]
        ],
        'cache' => [
            'info' => 'Cleans the cache system. Removes all cached files.',
            'usage' => [
                'php assist cache clean'
            ]
        ],
        'new page' => [
            'info' => 'creates a new page',
            'options' => [
                '-default' => 'sets page as the default page'
            ],
            'usage' => [
                'php assist new page <pagename>',
                'php assist new page <pagename> -dir=<directory>',
                'php assist new page <pagename> -default',
                'php assist new page <pagename>,<pagename> -default'
            ],
            'page naming style' => [
                'page-name' => 'becomes PageName',
                'pageName' => 'becomes PageName',
                'pagename' => 'becomes Pagename',
                'Pagename' => 'remains Pagename'
            ]
        ],
        'new middleware' => [
            'info' => 'creates a new middleware',
            'usage' => [
                'php assist new middleware <name>',
                'php assist new middleware <name>,<name>',
                'php assist new middleware <name> -dir=<directory>'
            ],
            'page naming style' => [
                'middle-ware' => 'becomes MiddleWare',
                'middleWare' => 'becomes MiddleWare',
                'middleware' => 'becomes Middleware',
                'Middleware' => 'remains Middleware'
            ]
        ],
        'new model' => [
            'info' => 'Creates or overwrite a model. Also pushes model to <page> controller.',
            'options' => [
                '-addview' => 'Add a view wrapper method to controller class for Model name'
            ],
            'usage' => [
                'php assist new model <page>/<model>',
                'php assist new model <page>/<model> -addview',
            ],
            'model naming style' => [
                'model-name' => 'becomes ModelName',
                'modelName' => 'becomes ModelName',
                'modelname' => 'becomes Modelname',
                'Modelname' => 'remains Modelname'
            ]
        ],
        'new provider' => [
            'info' => 'Creates or overwrite a controller view provider.',
            'usage' => [
                'php assist new provider <page|controller>/<providerName>'
            ],
            'provider naming style' => [
                'provider-name' => 'becomes providerName',
                'providerName' => 'remains providerName'
            ]
        ],
        'new route' => [
            'info' => 'Generate a new route for controller.',
            'options' => [
                '-render=<name>|<path>' => 'Set render view'
            ],
            'usage' => [
                'php assist new route <page>/<method>',
                'php assist new model <page>/<method> -render=<method>',
                'php assist new model <page>/<method> -render=<folder>/<method>',
                'php assist new model <page>/<method>,<page>/<method>'
            ],
            'method naming style' => [
                'method-name' => 'becomes methodName',
                'methodName' => 'becomes methodName',
                'methodname' => 'remains methodname',
                'Methodname' => 'becomes methodname'
            ]
        ],
        'generate' => [
            'info' => 'Generates csrf key, security key or a unique token',
            'usage' => [
                'php assist generate key',
                'php assist generate csrf-key',
                'php assist generate token',
                'php assist generate key-salt',
                'php assist generate certificate',

            ]
        ],
        'generate token' => [
            'info' => 'Generates a unique token',
            'usage' => [
                'php assist generate token'
            ]
        ],
        'generate certificate' => [
            'info' => 'Generates a unique certificate for your application',
            'usage' => [
                'php assist generate certificate'
            ]
        ],
        'generate key' => [
            'info' => 'Generates a security key',
            'usage' => [
                'php assist generate key'
            ]
        ],
        'generate key-salt' => [
            'info' => 'Generates a unique secret key salt',
            'usage' => [
                'php assist generate key-salt'
            ]
        ],
        'generate csrf-key' => [
            'info' => 'Generates a CSRF key',
            'usage' => [
                'php assist generate csrf-key'
            ]
        ],
        'database clean' => [
            'info' => 'Cleans out database config file. Reset to default',
            'usage' => 'php assist database clean'
        ],
        'database channel' => [
            'info' => 'Creates a channel for outgoing database queries',
            'usage' => 'php assist database channel <channel-name>'
        ],
        'database add' => [
            'info' => 'Adds a database configuration to /database/database.php',
            'prompt' => [
                'driver' => 'would be from (mysql,sqlite,pgsql, etc.)',
                'host' => 'database connection host. eg localhost',
                'user' => 'database user',
                'pass' => 'database password',
                'name' => 'database name'
            ],
            'options' => [
                '-default=dev' => 'sets as active development database',
                '-default=live' => 'sets as active production database'
            ],
            'usage' => [
                'php assist database add <target-name>',
                'php assist database add <target-name> -default=dev',
                'php assist database add <target-name> -default=live'
            ]
        ],
        'database create' => [
            'info' => 'Creates a new database.',
            'options' => [
                '-pass' => 'DBMS connection password (optional)',
                '-host' => 'DBMS connection host (optional) default is "localhost"',
                '-driver' => 'DBMS connection driver.'
            ],
            'usage' => [
                'php assist database create <name>',
                'php assist database create <name> -pass=password',
                'php assist database create <name> -pass=password -driver=mysql|sqlite|pgsql',
                'php assist database create <name> -pass=password -driver=mysql|sqlite|pgsql -host=localhost',
            ]
        ],
        'database destroy' => [
            'info' => 'Destroys a database. Removes it from your DBMS',
            'options' => [
                '-pass' => 'DBMS connection password (optional)',
                '-host' => 'DBMS connection host (optional) default is "localhost"',
                '-driver' => 'DBMS connection driver.'
            ],
            'usage' => [
                'php assist database destroy <name>',
                'php assist database destroy <name> -pass=password',
                'php assist database destroy <name> -pass=password -driver=mysql|sqlite|pgsql',
                'php assist database destroy <name> -pass=password -driver=mysql|sqlite|pgsql -host=localhost',
            ]
        ],
        'database reset' => [
            'info' => 'cleans up a database. Removes all tables',
            'options' => [
                '-keep' => 'remove others except ?..',
                '-pass' => 'DBMS connection password (optional)',
                '-host' => 'DBMS connection host (optional) default is "localhost"',
                '-driver' => 'DBMS connection driver.'
            ],
            'usage' => [
                'php assist database reset <name>',
                'php assist database reset <name> -keep=account,users',
                'php assist database reset <name> -pass=password',
                'php assist database reset <name> -pass=password -driver=mysql|sqlite|pgsql',
                'php assist database reset <name> -pass=password -driver=mysql|sqlite|pgsql -host=localhost',
            ]
        ],
        'database empty' => [
            'info' => 'empties all tables in a database',
            'options' => [
                '-keep' => 'empty others except ?..',
                '-pass' => 'DBMS connection password (optional)',
                '-host' => 'DBMS connection host (optional) default is "localhost"',
                '-driver' => 'DBMS connection driver.'
            ],
            'usage' => [
                'php assist database empty <name>',
                'php assist database empty <name> -keep=account,users',
                'php assist database empty <name> -pass=password',
                'php assist database empty <name> -pass=password -driver=mysql|sqlite|pgsql',
                'php assist database empty <name> -pass=password -driver=mysql|sqlite|pgsql -host=localhost',
            ]
        ],
        'database' => [
            'info' => 'when attached to a table you can generate dummy data, empty or reset table. Else see commands that runs on a database.',
            'table usage' => [
                'clean' => 'php assist database <table> clean',
                'empty' => 'php assist database <table> empty',
                'generate' => 'php assist database <table> generate -table=20 (default is 5)'
            ],
            'table generate options' => [
                '-table' => 'a number of dummy data to generate',
                'field=val' => 'set a default value for a field row. eg image=lady.png username=moorexa'
            ],
            'other usage' => [
                'clean' => '{%database clean/info%}. see php assist database clean -h',
                'add' => '{%database add/info%}. see php assist database add -h',
                'create' => '{%database create/info%}. see php assist database create -h',
                'destroy' => '{%database destroy/info%}. see php assist database destroy -h',
                'reset' => '{%database reset/info%}. see php assist database reset -h',
                'empty' => '{%database empty/info%}. see php assist database empty -h',
            ],
            'options' => [
                '-pass' => 'DBMS connection password (optional)',
                '-host' => 'DBMS connection host (optional) default is "localhost"',
                '-driver' => 'DBMS connection driver.'
            ]
        ],
        'table drop' => [
            'info' => 'drops a table and removes it from local dbms,sql files.',
            'usage' => [
                'php assist table drop <table>',
                'php assist table drop <table> -database=<databse-source>'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table empty' => [
            'info' => 'truncates a table. ',
            'usage' => [
                'php assist table empty <table>',
                'php assist table empty <table> -database=<databse-source>'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table show' => [
            'info' => 'shows all the rows and column in a table. ',
            'usage' => [
                'php assist table show <table>',
                'php assist table show <table> -database=<databse-source>',
                'php assist table show <table> -column=<col>,<col>',
                'php assist table show <table> -database=<databse-source> -where= userid=2 -end',
                'php assist table show <table> -database=<databse-source> -where= userid=2 -end -orderby=id desc',
                'php assist table show <table> -database=<databse-source> -where= userid=2 -end -orderby=id desc -end -limit=0,3'
            ],
            'optional' => [
                '-database=<databse-source>',
                '-column=<col>,<col>',
                '-where= userid={id} -end',
                '-orderby=id desc -end -limit=0,3'
            ]
        ],
        'table describe' => [
            'info' => 'describes a table. ',
            'usage' => [
                'php assist table describe <table>',
                'php assist table describe <table> -database=<databse-source>'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table all' => [
            'info' => 'shows all the tables in a database. ',
            'usage' => [
                'php assist table all',
                'php assist table all -database=<databse-source>'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table rows' => [
            'info' => 'returns the total rows in a table. ',
            'usage' => [
                'php assist table rows <table>',
                'php assist table rows <table> -database=<databse-source>',
                'php assist table rows <table> -database=<databse-source> -where= userid=2 -end',
                'php assist table rows <table> -database=<databse-source> -where= userid=2 -end -orderby=id desc',
                'php assist table rows <table> -database=<databse-source> -where= userid=2 -end -orderby=id desc -end -limit=0,3'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table delete' => [
            'info' => 'deletes a record from a table. ',
            'usage' => [
                'php assist table delete <table> -database=<databse-source> -where= userid=2'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table insert' => [
            'info' => 'inserts a record into a table. ',
            'usage' => [
                'php assist table insert <table> -database=<databse-source> -set= userid=2, password=\'moorexa\''
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'table update' => [
            'info' => 'updates a record in a table. ',
            'usage' => [
                'php assist table update <table> -database=<databse-source> -set= userid=2, password=\'moorexa\' -where= id=2'
            ],
            'optional' => [
                '-database=<databse-source>'
            ]
        ],
        'serve' => [
            'info' => 'Starts PHP development server.',
            'usage' => [
                'php assist serve',
                'php assist serve -notab',
                'php assist serve <port>',
                'php assist serve 9090',
                'php assist serve 9090 -notab',
                'php assist serve -goto=page/view',
                'php assist serve 9090 -goto=page/view',
            ]
        ],
        'page' => [
            'info' => 'Allows you to generate avialiable routes.',
            'usage' => [
                'php assist page <page> routes' => 'Generates avialiable routes'
            ]
        ],
        'migrate' => [
            'info' => 'performs a database migration',
            'options' => [
                '-tables' => 'Migrate tables in lab/tables directory or list of tables to migrate, seperated with comma. (Optional)',
                '-database' => 'database to migrate to. (Optional) default would be used.',
                '-from' => 'performs a migration from a file',
                '-prod' => 'Migrate to production database',
                '-drop' => 'Drop tables',
                '-options'  => 'Run table options',
                '-prefix'  => 'Set table prefix',
                '--force' => 'Force Migration',
                '--nocache' => 'Ignore migrating saved cached'
            ],
            'usage' => [
                'php assist migrate',
                'php assist migrate --force',
                'php assist migrate -prod',
                'php assist migrate -drop -tables=users',
                'php assist migrate -drop',
                'php assist migrate --nocache',
                'php assist migrate -prod -tables=users,account',
                'php assist migrate -options -tables=users,account',
                'php assist migrate -tables=account,users',
                'php assist migrate -prefix=test_',
                'php assist migrate -tables',
                'php assist migrate <table-name> -tables',
                'php assist migrate <table-name>,<table-name> -tables',
                'php assist migrate -database=moorexa',
                'php assist migrate table -from=pages/app/packages/auth'
            ]
        ],
        'deploy' => [
            'info' => 'Deploy project to production server.',
            'usage' => [
                'php assist deploy' => 'Run deployment to production server.',
                'php assist deploy --notrack' => 'Deploy without keeping track of deploys.',
                'php assist deploy -<filename>|<directory>' => 'Deploy file or every file inside a directory. You can seperate more values with (,)',
                'php assist deploy -exclude=<filename>|<directory>' => 'Deploy and exclude file or directory.'
            ]
        ],
        'test' => [
            'info' => 'Opens up the system for testing. It gives you an environment for testing the entire framework',
            'usage' => [
                'php assist test <test-name>' => 'This would run a complete test for <test-name> class.',
                'php assist test <test-name> <method-or-shortlink>' => 'This would run a test for a specific method or test case.',
                'php assist test make:data <data-name>' => 'This would generate a test data that can be applied to your program.',
                'php assist test make <test-case>' => 'This would generate a test case class. '
            ]
        ],
        'test make' => [
            'info' => 'This would generate a test case class',
            'usage' => [
                'php assist test make <test-case>' => 'This would generate a test case class having <test-case>'
            ]
        ]

    ];

    // option mapping
    public static $optionMapping = [
        '-h' => 'commandHelp'
    ];

    // database query cache path
    protected static $queryCachePath = null;

    // database instance
    private static $instance = [];

    // database migrate option
    private static $migrateOption = false;

    // assist instance
    private static $assistInstance;

    // private storage
    private static $storage = [];

    // command helpers
    private static $commandHelpers = [];

    // decryption listeners
    private static $decryptListeners = [];

    // set paths
    protected static $tablePath = PATH_TO_DATABASE . '/Tables/'; // table base path
    protected static $directivePath;
    public    static $bashConfiguration = [];

    // ansii codes
    private $ansii = [
        'reset' => "\033[0m",
        'save'  =>  "\0337",
        'green1' => "\033[32;1m",
        'green' => "\033[32m",
        'bold' => "\033[1m",
        'clean' => "\033[K",
        'return' => "\0338",
        'red' => "\033[31m",
        'red1' => "\033[31;1m",
        'line' => "\033[4m",
        'clear-screen' => "\033[2J",
        'quit-bg' => "\033[37;41;1m",
        'quit-color' => "\033[37m"
    ];

    // constructor
    public function __construct()
    {
        // set the table path
        self::$tablePath = get_path(PATH_TO_DATABASE, '/Tables/');

        // set the directive path
        self::$directivePath = defined('PATH_TO_DIRECTIVES') ? get_path(PATH_TO_DIRECTIVES, '/') : null;

        // running from a browser ?
        if (defined('ASSIST_TOKEN')) :
        
            $this->ansii['reset'] = '</span>';
            $this->ansii['save'] = '';
            $this->ansii['clean'] = '';
            $this->ansii['quit-bg'] = '<span style="background-color:#f90; color:#000">';
            $this->ansii['quit-color'] = '<span style="color:#f90">';
            $this->ansii['line'] = '<span>';
            $this->ansii['clear-screen'] = '';
            $this->ansii['return'] = '<span style="display:block;">';
            $this->ansii['green1'] = '<span style="color:#090; font-weight:bold;">';
            $this->ansii['red1'] = '<span style="color:#f00; font-weight:bold;">';
            $this->ansii['green'] = '<span style="color:#090;">';
            $this->ansii['red'] = '<span style="color:#f00;">';
            $this->ansii['bold'] = '<span style="font-weight:bold">';

        endif;

        // Assist running on CLI?
        if (substr(php_sapi_name(), 0, 3) == 'cli' || defined('ASSIST_TOKEN')) :
        
            
            if (self::$assistInstance === null) :

                // clear buffer
                if (defined('ASSIST_TOKEN')) ob_clean();

                // set instance
                self::$assistInstance = $this;

                // console tables
                include_once get_path(func()->const('console'), '/Table.php');

                // include functions
                include_once __DIR__ . '/Assist/Functions.php';

                // get bash configuration
                $bashConfiguration = include_once get_path(func()->const('konsole'), '/bash.php');

                // load command helper
                self::loadCommandHelper();

                // save bash configuration
                self::$bashConfiguration = $bashConfiguration;

                // call method
                $argv = $_SERVER['argv'];

                // call help method
                if (count($argv) == 1) return self::help();

                // @var array $argvCopy
                $argvCopy = $argv;

                // @var array $arguments
                $arguments = array_splice($argv, 2); 

                // @var string $method
                $method = $argv[1];

                try
                {
                    // load assist manager
                    self::loadAssistManager($argvCopy, $arguments, $method, $bashConfiguration);
                }
                catch(\Throwable $exception)
                {
                    self::out($this->ansii('red') . '=== Exception ('.get_class($exception).') Throwned ==' . $this->ansii());
                    self::out($exception->getMessage() . "\n");
                    self::out($this->ansii('green') . '== Let see where this exception was throwned ==' . $this->ansii());

                    // get trace
                    $trace = $exception->getTrace();

                    if (isset($trace[0]['file'])) :
                        // where it was throwned
                        self::out('The exception was throwned in '.$this->ansii('red') . $trace[0]['file']. $this->ansii() .' on line ' . $trace[0]['line'] . '.');
                    endif;

                    // trace back
                    self::out("\n".'== Hmmm. And a trace back == ');

                    // manage trace from 1
                    foreach ($trace as $index => $traceArray) :

                        if ($index > 0 && (isset($trace[$index]['file']) && isset($trace[$index]['line']))) :
                            self::out($this->ansii('red') . $trace[$index]['file']. $this->ansii() .' on line ' . $trace[$index]['line'] . '.');
                        endif;

                    endforeach;

                    self::out(PHP_EOL);
                }

            endif;

        endif;
    }

    // get class instance
    public static function getInstance() : Assist 
    {
        return self::$assistInstance;
    }

    // get ansii codes
    public function ansii($code = 'reset')
    {
        if (strtolower(PHP_SHLIB_SUFFIX) == 'dll')
        {
            //window
            return null;
        }
        else
        {
            return $this->ansii[$code];
        }
    }

    // handle backups
    public static function backup()
    {
        fwrite(STDOUT, "\0337");
        self::out("\033[1m"."\nBackup\n");

        $dir = self::$assistPath . 'lab/Backup/';

        if (!is_dir($dir))
        {
            // create dir
            self::sleep("Creating directory.");
            mkdir($dir, 0777);
            self::sleep("Backup folder added to lab. Path => ". $dir);
        }

        self::out("Starting backup..");

        $i = 0;

        $complete = false;
        $files = [];

        $zip = new ZipArchive();

        $zipfile = $dir . 'backup'.date('Y-M-D').'.zip';

        if ($zip->open($zipfile, ZipArchive::CREATE) === true)
        {
            $data = glob(self::$assistPath .'{,.}*', GLOB_BRACE);

            foreach ($data as $i => $f)
            {
                if ($f != '.' && $f != '..')
                {
                    if (is_file($f))
                    {
                        $zip->addFile($f);
                    }
                    elseif (is_dir($f) && basename($f) != 'backup')
                    {
                        $dr = getAllFiles($f);

                        $single = reduce_array($dr);

                        if (count($single) > 0)
                        {
                            foreach ($single as $z => $d)
                            {
                                $zip->addFile($d);
                            }
                        }
                    }
                }
            }

            $close = false;

            for($i=0; $i <= 100; $i++)
            {
                fwrite(STDOUT, "\0337");
                $step = intval($i/10);
                fwrite(STDOUT, "\033[32m"."[". str_repeat('#', $step). str_repeat('.', 10 - $step) ."]"."\033[0m");
                fwrite(STDOUT, "{$i}% complete");
                fwrite(STDOUT, "\0338");

                if ($close == false)
                {
                    usleep(100000);
                    $close = $zip->close();
                }
                else
                {
                    usleep(10000);
                }
            }

            fwrite(STDOUT, PHP_EOL);
        }

        self::out("Backup zip file created >> "."\033[32m". $zipfile ."\n");
    }

    // restore backup
    public static function restore($args)
    {
        fwrite(STDOUT, "\0337");

        self::out("\033[1m"."\Restore\n");

        $dir = self::$assistPath . 'lab/Backup/';

        if (is_dir($dir))
        {
            if (count($args) > 0)
            {
                $file = $dir . rtrim($args[0], '.zip').'.zip';
            }
            else
            {
                $files = glob($dir.'*');
                $file = null;

                foreach ($files as $i => $fil)
                {
                    $created = date('Y-M-d', filemtime($fil));
                    if ($created == date('Y-M-d'))
                    {
                        $file = $fil;
                        break;
                    }
                }

                if ($file == null)
                {
                    $file = count($files) > 0 ? end($files) : $dir;
                }
            }

            if (is_file($file))
            {
                self::sleep("Staging "."\033[32m".$file."\033[0m"." for restore.\n");
                $zip = new ZipArchive();
                $res = $zip->open($file);

                if ($res === true)
                {
                    self::sleep("Restore started..");
                    fwrite(STDOUT, "Should we continue (y/n)? ");
                    $answer = strtolower(trim(fgets(STDIN)));

                    if ($answer == 'y')
                    {
                        $zip->extractTo(self::$assistPath);

                        $close = false;

                        for($i=0; $i <= 100; $i++)
                        {
                            fwrite(STDOUT, "\0337");
                            $step = intval($i/10);
                            fwrite(STDOUT, "\033[32m"."[". str_repeat('#', $step). str_repeat('.', 10 - $step) ."]"."\033[0m");
                            fwrite(STDOUT, "{$i}% complete");
                            fwrite(STDOUT, "\0338");

                            if ($close == false)
                            {
                                usleep(100000);
                                $close = $zip->close();
                            }
                            else
                            {
                                usleep(10000);
                            }
                        }

                        fwrite(STDOUT, PHP_EOL);
                    }
                    else
                    {
                        self::out("\033[31m"."Restore canceled..\n");
                    }
                }
            }
            else
            {
                self::out("\033[31m"."No backup found to restore\n");
            }
        }
        else
        {
            self::out("\033[31m"."No backup found to restore\n");
        }

        return 0;
    }

    // show text and sleep for 100000 milleseconds.
    public static function sleep($text)
    {
        fwrite(STDOUT, $text . PHP_EOL);
        usleep(100000);
    }

    // help method
    public static function help()
    {
        $ass = self::getInstance();

        $text = 'czo5MTQ6Ijsiczo4OTY6Ik5qZE9hd3FjY21nY2dhdkJXVzRkY3RYdWl2azJ2Q2VhRVdXbDBSS04rbGx3dkZ3dE1oRjRWTVRYK3BGOGtJRXZMOHlEeFVYYVBKa2Z1WmxOemsxa2pGSmlDQVd6Y0dmMCtOS0FKc2Jqc1N0c2luWHRST05BSnlEOG1qUVRJWUVSZHNFdHlSOGdGQ3hISUt1Mk5WdTZBamVIcUpnUGJRWnN5b25jTytLRk9tcTJnMTYwZ1IzY1AyR2NCV2tGVzBvQ3lIWDRsdUkxSE9kVUN4SkIwcCtSek85TlZOcWpBZUNoZzRxRXJuS0FuakpNSHFhaWtwYU5lN282dDJJa2x2YmhDY2xUYVFXcm15ejV4b1NhSlN5cVF4VmRkR2pZUEt3aytFN2hkdjkrY1RJelBZVXVqcU5LQ2xZZ2ZRY0pYdUtJcTM1QS8va05RcGRjOFlCR0N0eWo5UXFOTFpzUUFaQWVxcGdTWlhsZG9EQllWOFhyMHQ1bk9NMWR1VFNOK1pueTlKd0xzSWlBZWpGS0ZpVk1IQWFXNTJBc21BODdWTzA2c0gvbGQ1d3A2WVNtUWE3ZncwOWNsSlRQZ05xdW5xS01PYWhhMWJjQjAvWjd3c0NianJOTzBkYndKZW1DdVVDaE9MWVY0TWhYUmpsQkV2T0J2L1VNL0dVNHpPN21KdWU0RC9aV0lnckRqLzd3ME1VL1pVSXNvcmZ2RDdWWW8vK1ZKakJRZ1dFdC93Z3haeDF3cG9RdS92c2FybjR1OGZ1NnJ5bHFkc21FNWZMV0g0WVg0anlnam4vOWJMVE1lbldKajlMeWRmdEZCSDNPc2ljSCtFYUdOUVVlOWFWNWhjSCtXZENIRkxjcUg5Z0wzUHJCSldwS2Foa3RGQTUvZ3VXd0VHaS9sTms3Tklsb0JPbWZGVWhSYlVsZm92V2dMWWRySnZWUXdGcmx0SkFnWnl6THhDMzdxcU5yZUEvemtqUzlnTVRQUUZjdU4rZStFN0hOcFpNNm9ESCtTY1I5M3E1YzVVczFyRXVvTEIwRFJmTTI2MW9TMWR6eGFxWEd6Q05yanlnMHF0OEZpVXY5Zmx2em9QQVZ5QmVVZldCWXZ6WUtqTERUMEJJaVBnNENVMHpkcndJYXpCVHVPSTVFdWlpVTZIMm5XYngrWEN3TVBEVFZ5QUZPOTZPUThJQm44algxIjsiOjUwOTpzIjs=';

        fwrite(STDOUT, $ass->ansii('save'));
        fwrite(STDOUT, $ass->ansii('green') . decryptAssist($text). $ass->ansii(). PHP_EOL);

        self::out("\nMoorexa version 1.0 <www.moorexa.com/assist/commands> ".date('Y-m-d g:i:s a'));
        self::out($ass->ansii('green1'). "Usage:");
        self::out("\tCommand [arguments] [options]");
        self::out($ass->ansii('green1'). "\n\nAvailable Commands:");
        foreach (self::$commands as $command => $info)
        {
            self::out($command."\t\t--\t".$info);
        }

        self::out($ass->ansii('green1'). "\n\nAvailable Options:");
        foreach (self::$options as $command => $info)
        {
            self::out($command."\t\t--\t".$info);
        }

        // listen for command
        self::listen();
    }

    // send output to screen
    public static function out($text, $code = null)
    {
        $ass = self::getInstance();

        if ($code === null)
        {
            $code = $ass->ansii('reset');
        }

        fwrite(STDOUT, $code . $text . $ass->ansii() . PHP_EOL);
    }

    // about moorexa
    public static function about()
    {
        $ass = self::getInstance();

        $text = 'czo5MTQ6Ijsiczo4OTY6Ik5qZE9hd3FjY21nY2dhdkJXVzRkY3RYdWl2azJ2Q2VhRVdXbDBSS04rbGx3dkZ3dE1oRjRWTVRYK3BGOGtJRXZMOHlEeFVYYVBKa2Z1WmxOemsxa2pGSmlDQVd6Y0dmMCtOS0FKc2Jqc1N0c2luWHRST05BSnlEOG1qUVRJWUVSZHNFdHlSOGdGQ3hISUt1Mk5WdTZBamVIcUpnUGJRWnN5b25jTytLRk9tcTJnMTYwZ1IzY1AyR2NCV2tGVzBvQ3lIWDRsdUkxSE9kVUN4SkIwcCtSek85TlZOcWpBZUNoZzRxRXJuS0FuakpNSHFhaWtwYU5lN282dDJJa2x2YmhDY2xUYVFXcm15ejV4b1NhSlN5cVF4VmRkR2pZUEt3aytFN2hkdjkrY1RJelBZVXVqcU5LQ2xZZ2ZRY0pYdUtJcTM1QS8va05RcGRjOFlCR0N0eWo5UXFOTFpzUUFaQWVxcGdTWlhsZG9EQllWOFhyMHQ1bk9NMWR1VFNOK1pueTlKd0xzSWlBZWpGS0ZpVk1IQWFXNTJBc21BODdWTzA2c0gvbGQ1d3A2WVNtUWE3ZncwOWNsSlRQZ05xdW5xS01PYWhhMWJjQjAvWjd3c0NianJOTzBkYndKZW1DdVVDaE9MWVY0TWhYUmpsQkV2T0J2L1VNL0dVNHpPN21KdWU0RC9aV0lnckRqLzd3ME1VL1pVSXNvcmZ2RDdWWW8vK1ZKakJRZ1dFdC93Z3haeDF3cG9RdS92c2FybjR1OGZ1NnJ5bHFkc21FNWZMV0g0WVg0anlnam4vOWJMVE1lbldKajlMeWRmdEZCSDNPc2ljSCtFYUdOUVVlOWFWNWhjSCtXZENIRkxjcUg5Z0wzUHJCSldwS2Foa3RGQTUvZ3VXd0VHaS9sTms3Tklsb0JPbWZGVWhSYlVsZm92V2dMWWRySnZWUXdGcmx0SkFnWnl6THhDMzdxcU5yZUEvemtqUzlnTVRQUUZjdU4rZStFN0hOcFpNNm9ESCtTY1I5M3E1YzVVczFyRXVvTEIwRFJmTTI2MW9TMWR6eGFxWEd6Q05yanlnMHF0OEZpVXY5Zmx2em9QQVZ5QmVVZldCWXZ6WUtqTERUMEJJaVBnNENVMHpkcndJYXpCVHVPSTVFdWlpVTZIMm5XYngrWEN3TVBEVFZ5QUZPOTZPUThJQm44algxIjsiOjUwOTpzIjs=';

        fwrite(STDOUT, $ass->ansii('green') . decryptAssist($text). $ass->ansii(). PHP_EOL);
        self::out($ass->ansii('bold')."History");

        $history = "\nMoorexa started off as a personal framework late 2016 formally 'PIHYPE' by Ifeanyi Amadi for building web apps as a freelancer. He wanted enough flexibility in PHP and freedom to build without worries so he open sourced Pihype, see repo (https://github.com/xchriscode/pihype) and in 2017 he decided to make it a whole lot better and ready for enterprise apps for PHP Geeks around the world.\n\nBefore Moorexa, he had experimented with several framework architectures and interfaced with PHP developers to know what works for them and to build a strong secure system that's ready for any web application in record time.\n\nPihype was finally renamed to Moorexa and became bigger with more power. On the 23rd of April 2018 it was released for Beta Testing and had over 220 downloads and was later removed August 26th, 2018 in preperation for a stable release.\n\nMoorexa was built out of pihype for amazing possibilities on any skill level. Means beginners can build secure apps with little effort and proffessionals can just whisper and 'whoola' we have a app. \n\n";

        self::out(wordwrap($history, 50));

        self::out($ass->ansii('bold')."About");

        $about = "\nMoorexa is an eloquent Open-Source PHP MVC Framework provided by fregatelab for developing modern web applications that explains power, freedom, creativity, relationship, speed, flexibility and rapid growth. it's a framework for PHP Geeks who wish to build PHP apps for faster and improved web experience, rapid in behavior, friendly to humans and scales as the project grows.\n";

        self::out(wordwrap($about, 50));

        // listen for new input
        self::listen("Try another command, ");
        return 0;
    }

    // sub function for clean
    private static function __rmdirs($dirs, $parent = [])
    {
        if (is_array($dirs))
        {
            foreach ($dirs as $x => $dir)
            {
                if (is_dir($dir))
                {
                    $dig = glob($dir.'/*');

                    if (count($dig) > 0)
                    {
                        foreach ($dig as $i => $path)
                        {
                            if (is_dir($path))
                            {
                                $files = glob($path . "/*");

                                if (count($files) == 0)
                                {
                                    @rmdir($path);
                                }
                                else
                                {
                                    $newdirs = [];

                                    foreach($files as $z => $fl)
                                    {
                                        if (is_dir($fl))
                                        {
                                            $newdirs[] = $fl;
                                        }
                                    }

                                    if (count($newdirs) > 0)
                                    {
                                        self::__rmdirs($newdirs, $dirs);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if (is_dir($dir))
                        {
                            @rmdir($dir);
                        }
                    }
                }
            }
        }
    }

    // sub function for clean
    private static function __cleandir($dir, $id, $filecount, &$rootFiles, &$status)
    {
        $continue = false;
        $rootused = false;
        static $track = 0;
        $complete = false;

        if (isset($dir[$id]))
        {
            static $current;
            $current = $dir[$id];

            static $files;
            $files = getAllFiles($current);

            static $flatten;

            $flatten = reduce_array($files);
            $continue = true;
        }
        else
        {
            $continue = true;
            $flatten = $rootFiles;
            $rootused = true;
        }


        if ($continue)
        {
            $ass = self::getInstance();

            if (count($flatten) > 0)
            {
                foreach ($flatten as $i => $path)
                {
                    fwrite(STDOUT, $ass->ansii('save'));
                    fwrite(STDOUT, "({$track}/{$filecount}) Complete.");
                    fwrite(STDOUT, $ass->ansii('return'));


                    if (basename($path) != 'assist' && basename($path) != 'composer' && basename($path) != 'starter.zip')
                    {
                        unlink($path);
                    }

                    $sleep = (100000 / $filecount) + $filecount;
                    usleep($sleep);

                    $track += 1;

                    $status = $track;

                    if ($i == count($flatten)-1 && $rootused == false)
                    {
                        $id++;
                        self::__cleandir($dir, $id, $filecount, $rootFiles, $status);
                    }
                    elseif ($i == count($flatten)-1 && $rootused == true)
                    {
                        $complete = true;
                    }
                }
            }
            else
            {
                $id++;
                self::__cleandir($dir, $id, $filecount, $rootFiles, $status);
            }
        }

        return $complete;
    }

    // listen for commands
    protected static function listen($text = "Enter a command to try, ")
    {
        $ass = self::getInstance();

        fwrite(STDOUT, $ass->ansii('save'));
        fwrite(STDOUT, $ass->ansii('quit-color'). "\n{$text}".$ass->ansii('reset').$ass->ansii('quit-bg')."'quit'".$ass->ansii('reset')." to cancel: ");
        $start = microtime(true);

        while($line = fgets(STDIN))
        {
            $line = trim(strtolower($line));
            $breakLine = explode(" ", $line);

            if ($breakLine[0] == 'assist')
            {
                unset($breakLine[0]);
                sort($breakLine);
            }

            if (trim($line) == 'quit' || trim($line) == 'clear' || trim($line) == 'cls')
            {
                $end = microtime(true) - $start;
                self::out($ass->ansii('red')."Terminated successfully... ". $end .'ms'."\n");
                fwrite(STDOUT, $ass->ansii('return'));
                return 0;
            }
            else
            {
                $line = $breakLine[0];

                if (isset(self::$commands[$line]))
                {
                    if (self::hasOption($breakLine, $option, $command))
                    {
                        self::generateQuickHelp($option, $command);
                        return 0;
                    }
                    else
                    {
                        fwrite(STDOUT, $ass->ansii('clear-screen')); // clear screen
                        $other = array_splice($breakLine, 1);
                        $line = lcfirst(preg_replace('/[\s]/','',ucwords($line)));

                        self::{$line}($other);
                        fwrite(STDOUT, $ass->ansii('return'));
                        return 0;
                    }
                }
                else
                {
                    // failed.
                    self::invalid($line);
                }
            }
        }
    }

    // invalid command
    public static function invalid($line, $class = 'assist', $commands = [])
    {
        $ass = self::getInstance();

        if (count($commands) == 0)
        {
            $commands = self::$commands;
        }

        self::out($ass->ansii('red1')."\n".'---'.$ass->ansii('reset')."Invalid {$class} command '".$line.$ass->ansii('bold')."\n\nAvaliable Commands:\n");

        $commands = array_keys($commands);
        $table = new Console_Table();
        $table->setHeaders(['Command', 'Hint']);
        foreach($commands as $i => $command)
        {
            $table->addRow(["assist ".$command, substr_replace(self::$commands[$command], '.. -h for more', 50)]);
        }
        self::out($table->getTable());
    }

    // generate command
    public static function generate($arg)
    {
        $ass = self::getInstance();

        if (count($arg) > 0)
        {
            $command = isset($arg[0]) ? trim(strtolower($arg[0])) : null;

            self::out($ass->ansii('bold')."\ngenerate {$command}\n");

            $char = range('A','Z');
            shuffle($char);
            $char2 = range('a','z');
            shuffle($char2);

            $num = range(1,1000);
            shuffle($num);

            $start = mt_rand(1,5);
            $end = mt_rand(5,10);

            $string = implode("",array_splice($char, $start, $end)).'-'.implode("", array_splice($char2, $start, $end));
            $string = strtoupper(str_shuffle($string));


            $sep = mt_rand($end, 800);
            $number = implode($sep, array_splice($num, $start, $end));


            $string = str_shuffle($string.'-'.$number);
            $string = hash('sha256', $string);

            $certificate = str_shuffle(file_get_contents(PATH_TO_UTILITY . '/Certificate/Public/certificate.key'));

            if (function_exists('password_hash'))
            {
                $string = password_hash(sha1(time().date('Y-m-d g:i:s').'kegen-'.$string), PASSWORD_BCRYPT);
            }
            else
            {
                $string = crypt(sha1(time().date('Y-m-d g:i:s').'kegen-'.$string), CRYPT_BLOWFISH);
            }

            $key = sha1(hash('sha256', $string . $certificate));

            $configPath = PATH_TO_CONFIG;
            $base = preg_quote(HOME, '/');
            $configPath = preg_replace("/^($base)/", '', $configPath);

            $content = file_get_contents(self::$assistPath . $configPath . '/config.php');

            switch ($command)
            {
                case 'certificate':
                    //  get password
                    fwrite(STDOUT, 'Enter an encryption password unique to you :');
                    $password = self::readline();

                    // get user
                    fwrite(STDOUT, 'A username, company name, project name or email :');
                    $user = self::readline();

                    // generate random if null
                    $password = $password == '' ? str_shuffle('883038-password') : $password;
                    $user = $user == '' ? 'support@moorexa.com' : $user;

                    // file name
                    $file = 'moorexa-framework.pub';

                    // generate command
                    $command = 'ssh-keygen -t rsa -b 4096 -C "'.$user.'" -f "'.$file.'" -N "'.$password.'" -q';

                    self::runCliCommand($command);

                    if (file_exists (__DIR__ . '/' . $file))
                    {
                        file_put_contents(PATH_TO_UTILITY . 'Certificate/Public/certificate.key', file_get_contents(__DIR__ . '/' . $file));
                        unlink(__DIR__ . '/' . $file);
                        unlink(__DIR__ . '/' . $file . '.pub');
                    }
                break;

                case 'key':
                    $start = strstr($content, "'secret_key'");
                    $end = strpos($start, ',');
                    $new = "'secret_key' => '$key'";
                    $line = substr($start, 0, $end);
                    $content = str_replace($line, $new, $content);
                    file_put_contents(self::$assistPath . $configPath . '/config.php', $content);
                    self::out("New Security Key ($key) generated ". $ass->ansii('green')."successfully.");
                    break;

                case 'key-salt':
                    $start = strstr($content, "'secret_key_salt'");
                    $end = strpos($start, ',');
                    $new = "'secret_key_salt' => '$key'";
                    $line = substr($start, 0, $end);
                    $content = str_replace($line, $new, $content);
                    file_put_contents(self::$assistPath . $configPath . '/config.php', $content);
                    self::out("New Security Key Salt ($key) generated ". $ass->ansii('green')."successfully.");
                    break;

                case 'assist_token':
                case 'assist-token':
                case 'clitoken':
                    $key = sha1($key);
                    $start = strstr($content, "'assist_token'");
                    $end = strpos($start, ',');
                    $new = "'assist_token' => '$key'";
                    $line = substr($start, 0, $end);
                    $content = str_replace($line, $new, $content);
                    file_put_contents(self::$assistPath . $configPath . '/config.php', $content);
                    self::out("self::getInstance() CLI Token ($key) generated ". $ass->ansii('green')."successfully.");
                    break;

                case 'csrf-key':
                    $start = strstr($content, "'csrf_salt'");
                    $end = strpos($start, ',');
                    $new = "'csrf_salt' => '$key'";
                    $line = substr($start, 0, $end);
                    $content = str_replace($line, $new, $content);
                    file_put_contents(self::$assistPath .  $configPath . '/config.php', $content);
                    self::out("New CSRF Key ($key) generated ". $ass->ansii('green')."successfully.");
                    break;

                case 'token':
                    $currentTime = time();
                    // generate random number
                    $num = mt_getrandmax();
                    // get the secret key
                    $secretkey = str_shuffle(env('bootstrap', 'secret_key'));
                    // get server information
                    $server = md5(implode(',', reduce_array(array_values($_SERVER))));
                    // BUILD Token
                    $token = md5(encrypt($currentTime .'@'. $num . '/key=' . $secretkey . '/server=' . $server));
                    // output token
                    self::out($token);
                    break;

                default:
                    self::out($ass->ansii('red')."Command '$command' not a valid command. try (key or csrf-key)");
            }

            self::out(PHP_EOL);
        }
    }

    // generate quick help
    public static function generateQuickHelp( $structure,  $command)
    {
        $ass = self::getInstance();

        // title
        fwrite(STDOUT, $ass->ansii('green')."\n==>".$ass->ansii('reset')." Command '". $command ."' help". $ass->ansii('reset'). PHP_EOL);

        // ilterate
        foreach ($structure as $name => $info)
        {
            // top layer
            self::out($ass->ansii('green1')."\n".ucwords($name));
            if (!is_array($info))
            {
                self::out($info);
            }
            else
            {
                foreach ($info as $i => $inf)
                {
                    if (preg_match('/({%)/', $inf))
                    {
                        $start = ltrim(strstr($inf, '{%'), '{%');
                        $text = substr($start, 0, strpos($start, '%}'));
                        $break = explode('/', $text);
                        $current = self::$commandHelp;
                        $text = "";

                        foreach ($break as $x => $key)
                        {
                            $text = $current[$key];
                            if (is_array($current[$key]))
                            {
                                $current = $current[$key];
                            }
                        }

                        $inf = $text;
                    }

                    if (is_string($i))
                    {
                        self::out($ass->ansii('bold').$i." : ".$ass->ansii('reset').$inf."\n");
                    }
                    else
                    {
                        self::out("- ".$inf."\n");
                    }

                }
            }
        }
        fwrite(STDOUT, "\n");
    }

    // check if request has an option tag
    public static function hasOption($stdin, &$option, &$command)
    {
        $has = false;

        foreach ($stdin as $i => $line)
        {
            if (isset(self::$options[$line]))
            {
                if (isset(self::$optionMapping[$line]))
                {
                    $goto = self::$optionMapping[$line];

                    if (isset($stdin[$i-1])) :

                        $before = $stdin[$i-1];
                        // get the last benchmark
                        $new = array_splice($stdin,0,$i);
                        // get command used
                        $newArray = array_splice($new, 0, 2);
                        $command = trim(implode(' ', $newArray));

                        if (isset(self::${$goto}[$command]))
                        {
                            $option = self::${$goto}[$command];
                            $has = true;
                        }
                        elseif (isset(self::${$goto}[$newArray[0]]))
                        {
                            $option = self::${$goto}[$newArray[0]];
                            $command = $newArray[0];
                            $has = true;
                        }
                        
                    endif;
                }

                break;
            }
        }

        return $has;
    }

    // call static
    public static function __callStatic($method, $args)
    {
        if ($method == 'new')
        {
            $method = '_new';
        }
        elseif ($method == 'system')
        {
            $method = '_system';
        }

        // handle errors
        self::invalid($method);
    }

    // return full path
    protected static function getFullPath(string $path) : string
    {
        $path = self::$assistPath . self::removeBasePath($path);
        return $path;
    }

    // remove base from path
    private static function removeBasePath(string $path) : string
    {
        // remove base path from path if found
        $quote = preg_quote(self::$assistPath, '/');
        $path = preg_replace("/^($quote)/", '', $path);
        return $path;
    }

    // creates new page, model, packages, headers, footers etc.
    public static function _new($arg)
    {
        if (count($arg) > 1)
        {
            $ass = self::getInstance();

            $command = isset($arg[0]) ? trim(strtolower($arg[0])) : null;

            self::out($ass->ansii('bold')."\nnew {$command}\n");

            // set base path
            self::$controllerBasePath = env('bootstrap', 'controller.base.path');

            // append forward slash to base path
            self::$controllerBasePath = rtrim(self::$controllerBasePath, '/');

            // get namespace 
            $namespace_prefix = ControllerLoader::getNamespacePrefix();

            switch ($command)
            {
                case 'page':
                case 'controller':

                    $page = $arg[1];
                    $exp = explode(',', $page);

                    foreach ($exp as $i => $page)
                    {
                        $page = preg_replace('/[^a-zA-Z_0-9]/', ' ', $page);
                        $page = ucwords($page);
                        $page = preg_replace('/[\s]/','',$page);

                        $check = self::$controllerBasePath . '/'. $page;

                        if (!is_dir($check))
                        {
                            // good, so we can create page directories
                            $directories = [
                                CONTROLLER_MODEL, 
                                CONTROLLER_STATIC, 
                                CONTROLLER_PARTIAL, 
                                CONTROLLER_PACKAGE, 
                                CONTROLLER_PROVIDER, 
                                CONTROLLER_CUSTOM, 
                                CONTROLLER_VIEW
                            ];

                            $controller = 'czo5NTg6Ijsiczo5NDA6Ij1vZjFsM28xVHJkdUxTbVBVNHZCZitLS2tVRXEzaVo1Z0ExbVgrRUpzUDl0WXpHSk1OQ2FpdGdwencvNzMxbWFHek1TdmlJR052S2xpZHZkY3ZKb05QZm0zQ1A2dEt5aUN3K3k1T2F4UElQOXFGQlNlSGxrdHZ4OXZLbHRaakFIWkFFa1BFa2xFd240NnB1ZlRsR25saWo3dk15czR6UVVKQU45eExWdm8zTERHNzRxYTRaQVVVWmRhTHJyZ1MrbnlXeVd4R2NGenpXRDdkTjRkVGZYaS9mVFF3MlM5bmI2ZnFCclF5d1FiSzBiZVVKNVVNZmNkRXlUUXBucWJodVpTSzFyR0VHRzRkeUxvcEI2eXRjU0hsbU1KcmkxaXdmQkMzWDhOem9GUzdsaEpqYXZFWUdsb3QyV3ZSbFgvaFB2STZTcEV1TzJUNFpheVpCZEZvN3YzNERxU0JnRjBWM1hENDhyWDRPYmhBbE5TUkF6Wlh1STZRV1JkeFpxWGhYRENBMVAyb0NrbmJRTlVES0tFNk9BV0xyK1RuVGFmWjlkazBUMnVpOWdSYjVoL0xPVXhJV0hSYzZUMmRhbm55eVFXMmZiUDZUUjhDbXZHVll1akZtOEpFVGxQZDZMSlJMNklmNzBzUC9zc1duMFJzaUdFVHoxdE5VaEhRUkRVQW5QZ0pSNkNqK0c0NWZGUWNNdEo1aG9wc0c4Zmd5b2QrbDVHNnY3eDI5SU5rbXlXbjNWYXhYN1pUVFpaRXlBcUdpcUtBTW5kY0dtWVB5ZEduV0ZhTjRqSHEvYUxlRzRncTA2SWpITmdtVytLMTNtL2VDS1JSZXZ2ZTYybzRqQ1FVVWZPandFa3Z2MmNwUlYrMFRiL09yYkcyMVRpQmJuTUhKdDRhc2FoN1hveEREd2huS25Za1RjTnFUTy92OWo0MHlQeDRBVE1nQXNBSmI3V1JVdGNpUmE2MUNTd0x1SGxPY2VBbmpHVUgyWTdXcTVjL0QycFY2TmZkT3ZBMDd5cWxlZjMxTWpDZEYyOEt0OFExbjVYSkxwclc5a3JxazltQTR5YWs4N0RPMEUvMEc5NGRFUS9KYk5vTlduYnJSZ1FncEhuUlhJdTJhOUJTWktBSEFGVlM3eFgvYkYxbTg5clRkMUxqZjFIaU1Lc3BHaGlMV0pqekhCcmpFUEZTRVJRbU9udC9VS1lXSS9Wa3Bybk9OK2pxTnNWZFpiMC9nTUdBYnIxb1FJakJkZmxZSEYiOyI6OTQ5OnMiOw==';
                            $header = 'czo1NzQ6Ijsiczo1NTY6Ij13S0hCcTBkOW5ISkJBcmFpRFlGUEk4bkFSZ2QvbERScnFyN1VsSFk5NUVSUTdOSjVXYXVqL0UvczhGTDlGUFkvUkNJZmppSDdJSHhUR3FsQVBRYnphWHhuSXc5VjRZbm9rYitLM1FUT25vTHZBSzNiayt1UUt4Ukk2MlZIeEVNWkNtN21nM2dCZFhhc3c5cTloMDBvYy9xWlZBS0VQQkJkbVZacCtUVlpicmc3dUZFRXVVQUJyYUY2VERqS2k4dWNNUi9laWlEYzhjUWpUZ0pwRlFJUjhTellFV0htTENuNDNQZ3lYWUlTdVgxbU0yK1BmYzF2V0diY3c3UGZ1RnAzUWhEdzlERHlyalk3KzRkcGQrWFo3a2V0ZTdvNS9KbCtDdmlXQ3dvb3RHZWxBOEdPK0NvUENPZ2M1TCtBSGg4ZVYydk9FVFhWMkMvK2ZjZ2hadTJ2UlhwWDlWOWZOVFpwZUFERU40QUhvQTJjYTVIbEtuZ2VCdDZHeTc1WXpadUF3Y0Z2dUtVLzZxb3lHOHNsV1V5ei9UVHdZYkxqclZyeXVRSE44K0x1Z082Zjh5bytRNHVZTTVNckhBK0lwWDZYSjdyYjU1dWtWeTZQb1lMTEI3Q2RWNGQ0djcrSFFONlBiYnE5eSsrREx2bkVuNk94RzNBVzF2b1VnSkFNcldwSUJOMnV2d2FPcnhRb3hKcHV0NytZZ2UzMmJvMWIrd2toRk9qNDAzVWI3REgiOyI6NTY1OnMiOw==';
                            $file = [];
                            $footer = 'czoxNDY6IjsiczoxMjg6IjUyWDFDZHRMa0tFdVZBMFZiU2dzQ3Myb1ROOWJxVFNjUUNGQXdsdW5jckczQ0NmeFg1UzZoT0NCZmI4dkJZb3hqZ0l3c2h4b0hXa1RsNTJsMUcvbHRhcUJZOXB3cHNhTC9qakpjWS9JY3pBdGFMalRyM3FwdEVkUUZmY2d3MjFYIjsiOjczMTpzIjs=';
                            $readme = 'czoyMDY4OiI7InM6MjA0ODoiVDFzQURiUkhkSXdxdHBXY05zSWIzL0JiaXFtcm1CRkZHYjJkSEgzNTJVbWlXOVk2WUZ6OGQ4SXh3YThTaWlqSTZGY0Vja01LN2pPSHNuWkRKSkUzUUlFdXVySUY4Si9lU3ZCcFdSd21JU2lsOStYY2tnSXlGQi9vb0QzNDRsQmYvcmJEaW5lMXdNNDFGdEwxY0Nkc3lNWkp0Z28veUlNMTJDRmRBeXRTMXpNdXRRRDAwb0h6bG5TQ0JiTUhHNkhBQjlNVEFmZ3RiUnRsa2d0RUp0NTZkZEFkekdhZllhRmhrWkwvZnFjVDJFVXBpSGRYQThSOGcvYWh4V0NLSWY0YzB6Mk5oNDZQQnhoY2RlUkpLVlJDUnJqV0pkOHAxVGVlZzJFRVRPb1hKTXVYUDJmelZNbmtyMm1hejJtZXRLdkxNcW9MMWtIOEhQS2pHbzBvWEoxNHJGSHA4ZFJ2MVAyN1AyQVoxSkVjUkFiOTUxaTcvODBSSU0vTmNoVHVsK1dHVTd1SVJHekFVeHdrRnZTOVhCYUtHcEF3Rm1IY3dsUE9FNVQ3QS9SZkc1UDFjc1ErUWNaUmVxTFRmK0w0VkE0amVXeElwbXM5bFhGWXhOMmhYS1M3UVEydzZJT0xxR0h5RnlPeS8yOTdvY1dhN2NBNzlWS1ZuQllwN1VHbmlLSjAybCsrakYyUFdMZkptd3RLZFNVMzlQanROYVYvNkdzTUpzcDNTWlNBV2tLRVZCK0Q4RFp0TGJKUmJqRzRCbm5MYXVhOEQrQzlsWDVScmw2d1NadUFzTWdybnUxOVk1WmJUWkE3S0YzMUNCZ2VKaGFRZkd5T1BRNEo4L05nR2RVQ0hjN0R5NXdWZi9KSGdibVNubXFCUk1XTUNGemNqL2RaZ3JjdVdvamp6WDRxWFdvNEVCSHhBeDFsMkd0cVlGb0dIaVhlSFVYT2gwbjIyRm12Y2toOWZVTVBmeWNTSkhIUHFsWk96K0lCeUhlNnF0U05ta0h3Yk5UVzhlaXp5eS9iTDZJRWljWW1NNk1RdXM0dU5weGdJM21JaVd1N2VRN2JFKzdIcGRjTkdJN3BoSGkvbkpzalRiQnlYc01pbVdhU3QzakxteVRLMG41OHlDQkttTXhKbWxtSkNYS0ZlSkw5SlVTUXhRZkxWV0pOYWQzL0IrcGtsQitxaldiWGJGMjZyN0trV0xhMGh2dTl3YUJPUUxaN0VSMVVEdURXUHNGWVhyc2RzN1MxaHNoMHRKSXVqdjJjc3VkWEhYK0ZvSEZFZzFnSjBvUkpCU2gzL0tYOW1Fa05Kb3IvQ3lRVjhVNXBDQnBZMHFPQzlYQ0RzYVB1LzZncXF1bTUxa002Q1ZuRE05NUpSVm40VCsvY0thK05iZUo3TVp4UWxJQXVXR0hDZGkwcjJnSjg2TVVVVE43NG1MblBjODF2VjlqbXc1dDltMUZrenZnNEJSR1FRYUFvbzJIbnV4bk5la0pHWHMxUU00OTNMZDBjOXlxWUllZDk2SWJPbXZXVHJ4dnNvRDQ2RE5BcVdZa0h4RTBmbzB5VVFjUlk3UUZza1lNUnRBMVpSSVpITHRET2trSWRQZ1pSTitqWHNZaWl2MFFnY09FQWN0ZktGQ3VSTlhhZ29IajFtbTNiaTZibU00N1BWWHEyM3JEd1RhUWVnM3FKVFBBSjJFSDdpZnZtcmFSblNCQTFqc3MrUHlBMDJxMjVyV0h2V1RlK2d5RmVCdkxsSEtockloSTRsVG1LRXB2akhpQmwwN2hidlhWeGpiVXVkK2NNc3lHbGpuMXUwdVNydHc5LzF0eUlPMU9IaTdjYmZBY003Z3FuT3dmZ0taNndFWlpVZjcwZVNFQjdlWGtNNDNENWtqYmNmVDdlTjByTXpLU3JVUERsOXBSeGhKVHIyUDhCRUNURWtpOFlOMGM2Zk1nSGZ3WHlvRDVIaU0zUkg3T2dycTd4N3JYSWJpdSs1TU9ieGFTc3BVcngvaUVNNWZFQU5Cc1I4U0s5VHJTTHg3UzdGVGlWZ0w2b2Nlc3J4ZmQ4bGprQkk0aUpGanltQVMwbnV4Ri9NSDNBeG5ZN1BMZmtGUHVBRzVnZUYwTVV2SVVTVlc4TENrdUdiL1loeUhlNUZ0a1VrOGlIL0hpY3VobVdMNFFqeC8rcktlUVo3aHNKVFl3cDBhNy9lbFhnYVN4Um92ZmloWFRHbEpkK3B0TjNNVGFZYjh0dnVyNDRtVURGZVZlck52U0M2RCtNK0JkbWQwenVMa1Y3SjF3MmRzdUZUMUljVkhtTzRiZG1wYU1kQWRNaUVuVFpPbHdhdzF3aU5ZbDJzaHRpYW5ZRzcyQ1VFSnIvcm9mL3RUMGdWc0tsaUFzSkRQeHFBQXZ5cExVM3hNMC9GU01CZkFZVzd5OFJsQVlBbm14eUFCTEdFZnpwQTJwM1FxbzJYT2hFTTJCb2dNcVQvOGtxUHRsenBEVXpwV3FDWHlYMXFYTTdIdjV6QU8xVlloUUZ3QmM0bk1qaHkvRjVVamV4Wlg4cFMvckFZOTZuM0o1cUk5V2Q5RWlCR1ZRUW05eEhVNWVNZ0k2Sk9QSzRxM3ZDODMvVlpNNGZmQ0l0Z01vaThZVG9ZMERoa3o3bXVtb01ycldlZndEczJnWVh4d2MrUXdFRzRmd3RIUUE3TkFOWVQ3UGVkSllhZ2dFcXZXOE5nVngrd1ZqcEtSOXBqRGtEbldnbFFJWGgiOyI6ODUwMjpzIjs=';
                            $config = 'czoyMjYwOiI7InM6MjI0MDoiWDB6OUsrbEJobWZSMEpKT256eTBCaDlSMTBkdTRDMTcrcTVoSkZSK2QxWjRHMHZyYmZUeUxKMHdYbHVqLzY4TzBaaWdkcjMwVzBrYjBNQUk0YXFXMm9uSnRxSFhsUWVFdzlXNk13UVZaTkRlSHEwWThJMHdnN0VmVlE5eGk2NWVQdGlpY015RmQrQUh0d0xGc1RJTGxCVzFtVzBFNWZHMEZqUnFwR2FZeFc1MEtJaWpZeWo0dTRjbEpVdmFDUmhObE4zZzNUUXBLZnAyaW05Y2xDYXY1SHhkRWZxYW1YQkRUVzdFaU5jUG5HRkRzMVg2M2JXRFdOdUhQRE9OaENuNG5KRzI4c0RoQ0R0QStjd0NwelM1K0M4aW1zSlVxUEgyRXRpWjc0UllnQmtoeDdXZHhmaWZhc2o5MXJWYWRGcnUvNTlIUnkycWx6RklxeXdkcnBVdUJTZW9JL0RuTzZ2MXkwTVQrNUVVVW0xT2JTL3BEaEhSRnNsSGE4MnNTZjd2d244TW5HVmtlOFdkQnAxS0E3bTczTUQxR2VObnFXanpoWmZIZFVsVUNQSkRSTFdmZXBMa2psVUpBMUZuakQ3NnYxM204Qi9JbXFDc3FBT1QvUVRLWW54eTlYQlFxZldxT3poVHV0dFFiKzcrMEpGNmRYWXYxVmxjaUxGbnNZdmJOeHRkUWZBT1dlVDludHNSK2FGTHlTV1gvdmdQNkQvbE02Mklzd3BKUDloT3NXM2VZR2JScnJhVFFIbXl1azcxVkpyVzEwMzlMUmJnb2R1UkVLY3RHZkV6cUZoK21wMGFXRGNUYTlvdUZ2VVdjNE1tejJJQk9GL3VIYUZLSmRNclRPMGQvdk5qWVlrYjd0eWdPcGovNjhIV1hhVW8rZmxBaXRKSitnNnE5ZlI3VWlWLzFMY2l0T08xR2JnZGJaaDQ0MW5JTW1SRTgwb0VDajduYUhPQVBvYmFkdlViMnY2QnZwelRkQjVUZlp1V2VUaDN3cm8wOGN1UWtSUkZoa2JnLzkrZUEyOFFhUWE0akd3SDluR0NMUDdOYmgreDRKUWx2THJhYmRWQVhEdWowK1pyaXgwU01GTW1jNm1Pb1d6ckVpY1h5MzB5SUtnSTBHZG5pSXh4MDJsNGNWaGpVcktkVjAvcG5NMHVqTkpwZ0hUWmtjL3NiejRHUENWRlg1TVNTSmpCd3YxYU1qSmdTRnN6VGZhK0Z2MlBqY3d4dnZ1Z2Vqb0J2NS8wQU9BdWRXMlM4c3Y0YzB4NzlNUDNRZ1ZRbUxZVk1qM1BTUW50cmZrcUhCQnpjcXNrL1hKTDhjeDVRUFQ2ZmNBVE94T0NCTHdXT0FxODN2a0VlRGlkYWluTnNvcHVZK3JKTEc3YVNRZkd2dDRFM05Qa2ZocWdnVFdLa2loVTVYcnR5RkxuQU1OU2lqbmJYMDhvcXc4cnVTazRpRExLb2FVMEpSSlhMc3pMQzZlV2FJV1JjanNtSEo3VU52YlozMjNraTZiazNmdE1lcmZoWEc0N3dQc01ZQkQ5cHU5WHF1QkhpczBoaG5zMXVhRlpxWmlJUWdNRWpyWWFnME1ZY1g1ZUN5dFo1VzJrQkpNUnJHOFY5R3d0UFRZelliaTNrYVRpMXNvdmFHZXhjUUlHLzU0NXVmVkpOZUhFK2g0bTJnU3JtQm1XR1o3eGVWeS9ZbS9PKzF1eDRrcmhLeGEzd3hLT2JYbjVQdVFkT1RqTldpVERrWjhzL01nRjdRa2pSL0hJcytJaXhEd0VkZkVoaXgxcjJZMnBYK00wbzJwd2IxZncxVjZGaVdXWVUyc0svS0hLQ1pIU1dTd2VDZ3BZRnJpM21sVjBDY3Mya0E2dnlweFpIRnJYRFA3aXpJNm5oeEVadmd2Ukx2R2Jwb0J4dVpPbGdVeFBpNVUvSHVEZ2drM0c4Z1VVVVdOL0tIdnhmVUNJN1RqSTBUWVRVNHFCWkp5anN4bHdDMjVrYkJHa0c2OWlqN09ZeVYyU1NHRnZjYm1Lb0lteisybW13RU5GL0VFUVUrS1h0NEFTb3psTzY4MjFEUmVFZVVZTGFFZUVQVU1WNE5SZmUwam5LVVZxeXFZN0dkcFkzazJ5bllZckoxclRpclkwTUt0eVByV1lSN1g2VjV0TkprT2RXazVwTWdtVTU0YkZyTFNvQ1k2Y0dyZzY4c3NUaVlqek56eWJnOHVucmgvUWZaakdab3hWbnhtYitvN2JiUlBVTmFqL2xpZWcxbmVJV2NsV1ljc0dxL25XSFoxTTRnQmd1c1VJRmxodFFaNkc3VTg4TCt3VWdHcTZaNUZYN0FpY1BDRklZbDg2ZFZjTFZheVJuaWk4L0N1Z1RNK2VNQ1NQSHBmWGV3TEdNUWlNMmlZRGRzbXhVRWQ4WlpsQlhTVC9oRzJrY1psRERWTlpBVDE4ODh4ZG10blJ2NmF2VklKbFpiVTdNbkYwT1V5NW9HYUxZbHFaK3BvSTRjWVpyWURtdW5CVWxIV2g5WUxtUU9YU0JsY0MyZjR4UXc3ZzE3ZWhDS2NsdTl0bEdCcUFqWlNYT0tHU1VkYlZtc2Q5ZjRrdk01TXRCR0pxcE41ZWhoWjlyQWZ4Y3hYR3RUNjhLWkJpUjF0TGNOSloyYTdEZWowdzl4R0dXLyt4bnFveFlZMEk5UWQrZG1MSjVFaGV6L0g1NXI0TDVtUks1Qml1T215NkY4NVhLMFRNMG4rNEVGTVA4Njdjc2ZXeldDVUgvUVNJRUdZUDN3YWViNy9mLzVwa3RTM0l5clY5WTl5aVZCdyt2V01ISUFhdWVWbElYODk2enhSSHhOSjhYcTdVb3plMFlMV0JJY0N3MWhta1ErRDcvR3lNUUppaVUvY0t2ZUM1b3RDRHNpTTN5WHJISVJDQUo5eEtaU1pWYlppUCt2NnNaMU5CYjk1cHBjaXZIaGJCT3hTR3FKNCt5Yi9aSjhVY0wvU1QrL2FqSENXTm9TVkUiOyI6MDUyMjpzIjs=';
                            $modelHttp = 'czoxMDAyOiI7InM6OTg0OiI9PUFuNWsvL2tJRCt5cEZaTFBlcVQ4Q0NTaVRkTWVXbW9OVmk4cDZhMmJDVmNNb2s0NkFWY2x5bWtWdlFwcXNKeExySFJob0gwQnRxbnN0SFJybDdtbjVETHRMUVlCLzljU0lDaTErOXNEejN4ZTFTRitPbW82Wm5MTzRPSXR5VW51azREcE90c3NDWFBTcVZtb0N3eG1mSXNCVGl4bTFyQmpkUkNBMXFla0hOSGc3NGdKK3BhODcwdzZOcmhzNWV2S3h6clk0ek4waGxreWtGQ1FhcWRuUGNzZ04rRlVOYU90TmlzUndsWllBZVlGUXlJQW9ZcDlGZHJXeHJ5OHA2WU5oVGtBblBpZ2FJckY2Njk0bVlERnJESHptRzFEdWFDS0lLbFlXS0xBWldQWmdNbWlHZGJueHErWXZMVCsyb2pRSU02RC9FeURUS3Y3QXU0ZUlWVkhmd0EvQnN6aTVPejA2M3daRTZJNHFpTnl5Wm42Q2JiaTNkaU84cHNYS3NtL0ZrUXB5NlNiYmorSDFFSzF0L1UySi9QUmNSNm1FbFdYRDhTeWJuS2dxTnZUYnJLTFY2MzMyYkdwSllyQzJFMW1zYTJ1emFYOGFZUUczamhyc2VYNnI2c3dXS2NucjB1M1BmTCtlNlJNOWNmRHkxUHBRTldBaVRFWU9HbFVucHhKajhxbTY2cGR5UkZhSXEvR0ljVkova0s4UklsZlFJS1hJRWFhUHhiNk4wWDY0bVlVT1p6dFd6OG1Sb05CMkJTUEhYdUllQ0RxMGx6aGJZYm50U051WU13ZTJFUUNiQjM0cmp1aFlGMHAwUVkwWXdFZXRxN3RHTDhhVzI0Y01jcG9hOEJaeGVmSGd4SzhCUzBOa3hRdGltOG0yRndHMndhRC9yd1V5ZFFNQWR5akpqUk50WTZ4a3Fidzc1Q3JndHJMRnF1OTI2L3RqOXhMeThPRG02NHNNRVhRaTA4am8zTVVkRTJDbFZqb3ZXSUJHM05nY1o0MXFnb3ZPRHRXU3FuZnhTclFDWFFOdDhKa0pIYWlLTTE1TzFmMURHMmlWYnc0bFdFZ1B0T3ZMbnc1aG1LWjVYeEhzQlVjTjZtM1FBYkRkRTBuc2g1Y1d1alJnZXMrVXljRUw1ZnQ4M0xkTDZZQ2U5VHdRSUtMUWd4RCtxVVVUcFVMdWRFMmgranVSb1RMajRaeEtpbDVBSlNkV0pIazF2K0YvZU5yNWl1K3JDZmp3Z1BBSVc2RnlIT29nOFc0TEZSWG1SOFlNNy85Ujl3d0RXS09CRFlrUVJMNDgwQTBXWWZrK2EiOyI6Mzk5OnMiOw==';
                            $provider = 'czoxNTE2OiI7InM6MTQ5NjoiPT13MnZTUnFnMml0d3JCK3o0cTVDWVJmS3Q2alVYd29GSDdYelRoRTFsWmswQmlidHYwdlNkaHZiWkl1azNaNG9tRGl5RHBxRENqN2I4dHd2bHMvMUhIK3hQd1hlK2ZPV2V0NUx0SmRCVDl1aWh1d0JmWms4K05xSnpMUkhvWUJlaHJjczNjVHdCb3ZJdndCUmZQODI0WWRYNFVocDRZOTZIZmZSL1lHSTQwdWx0MU9IdWEyREhuWlo4VTBvWWMyMTB6OFoyNzhUMzhrRkFKUlhyS1VyL3RUQ0NGMWl0T29sdjg4NUREd0o2SnRVOW9ySEVYdC9WR3ZBNndTYklWZG5malNrYjNTbkdRdmllUWlwVnZsQ0g5MzZwV0tXWFpVdmlIWEUySUxwUWVkbHorSjNqVzBlWVV4SlZKYWNweUNJemNycnUxTDQvVXdUaXFzOUZERFpCbWxUTFZOZWdMdGZkZ2FpdmxKTFJFbDhwUnhVWCtqNEJERDh1YUF4aS95Z1k3S09jY0JvbFRLU0padVVFVkIvRXhSUEcvcUh1UjNmUG94U3Qza01vL1dHRG5SN0NZMzNvNlFiS2ZudVpTSnYwclR6SWx0WC9NMmtvYi84dXZvdVZYcFFub0tRWWVqY1NSRXdoTFFHeEZjMWtPM2ZZWG1XeDdJVENjT3RUN2VDK2dqWWxyc0F4KzNpa05pTGdybmxhejhtUS9DemswcjF4TmtkRjdZdWFybUgyUmphL2hvSW14SUJMRnBpcWxMWTlsbDFxbE84dmZZVm5lSHVHOCtsT2k2SDkyN3pjNm8rTENSMlpzRFo5QitVdG42OGFLa05hZXNwZi9USVhKME1xaWo0N1lRRVpMdllCSTlpbFVLeHE3MXN2Ykh2bzE4bkt4WklWYjYzQkcrL0VINTZKVzI5eEpERSt0RnFudnh4ZVBXMXlkOHRCRG4zNXJjRFZham5veHVyc2hOU2dScjZZU0dhMW1PSFZwSkkwVmxWUHBGRDVMMGhvd2NGY1VJdEZ3UFNKeTBtYXFKVHJ1VEM2SFpEOUVBV3V6bTVmemlyQkxJL2gwNTlRNmNUd29PaXBFbER3STFob1QzVzAyTm1ZWkJxL2QrSlg0Ny9JUmlrRWh2MUhzeXFMa1B5L0FmWGoya1pUYUNOQ1ViamZWQk5tQVZNMWg5SXU4SzFITzU2SVFMajVEYXpNaVg4bXNzdWZLU0xvZXFDbjQvTHdMMWNrZnA5enpJYlB1QlZSMGdRbEN3enpkVlN0d0RCY2QzenVDZGRObVJZRjV1eUwrMkoyZjB6aDV2N0NuMHdGUlphUXlXL0pQNnVTTm9rdHoyS05Ub252RDdoTWMwY2tPNlMvd3AvenpSZGcwQlFUUG16RTV2ekNjTnAzMy9hV3cwREZxcDZUOUZZSURaajNYR2FiYWp5d05UVWNRMU80WHZraUhYYVErSy9MNzZRN0sxTys5cjhIQk5YSHhQRFNscHJyVUxmRk1iaTJxZVFQWFBQWW1yd2xnRXZ2ZDA1WEVSUmEwbXQrY2lwM2dab0VmamhNbTJGMFhCbVI1Z2E2L0lRQ3pFQlNZYWVHNHFLai9hVzlJaVhvbFRpaUNlZlRocWtKTHNsZzB1MS94UHcxelZ5eENFdFhHMmFhL2xzcVNRK0lsMHNNNW4wRnZBeWpsWXJoOFR0aHcwVlBBK1lsSEUwcUQ5UG8rS0xJVkF5TDRUUFNsUURyNm1qRUVpUTNPbTJKUTVjQVlQOTlYQm9BSG0yTWtTQmRrU2tkaDEybnNwT2JHM3M5THByTTJZZjA3VEIxTmI3d2t1ZGo2SGs3cldLQTFINEtnR2xuVHp1ZURITGZUWDRHK3lMcDdQREpDRnBrWHhNalA1S3pWYVhrM2NCR0E1dm5rQ2hoM1NPOWM1b3hFbzVXT043RldWZCtVS1lXSS9Wa3Bybk9OK2pxTnNWZFpiMC9nTUdBYnIxb1FJakJkZmxZSEYiOyI6NjA1MTpzIjs=';
                            $viewprovider = 'czoxMDY4OiI7InM6MTA0ODoiPT1RZzRMV0hFTCs3N3A2b3BIK2hqU3Z6R0JldmxpV25PaU1VNDdDUWRsN0MyMzdnakVBZ0ZmckJsMUZwb04xS1h4ZjNUU2JMT2d5Qkwrb1hoUHdUb29rY2tKM1hoUTdKSm9uSzdnZkJMMnovV0kwbVBLSUtFWDNnNXBSNHVvY2tGbi9FRGs2L3c4V3JhcXI5K0Rva0NpNUhzVHg0RFhCeVl6Q1h4ek8vK2ltOVVtRlo5VnMwaEF4NkQrOEQ0clJkcXI1TTZQL3NyUExBRC9JK3dUWDY3UlMwZW13M1FlbVl0NzU5a0VGRFM1UWNOZXRHUjJUb1RSN0NLOEhWT1VBamxRYUd6Q2doRUc1N0ZpQzRlc1NTZDFKaFQ4d0NDOGdVSzlZMnd3cDMzbUtQQlNaT29BZzYvYWZObkZVOUFFK2x2NkF4Y01DR2ZBSHlvcnNpSmc1QmZTMktjM2NTRTB2c25IMWxJNUdYY21XUWVIM2tGUnluVkxuelRkdWJrUXUwalVFZk5venE4OGo2ZmRvUFRBaDErQnJYMkM5S09vRWlsQmhtZ2Z3c2FyTkU4d3FKZnRCMDJOMGFiSWxwTE5ZcDBocE9qWDdSbEFmM2RlaU0vVDdzT25aWWhrSGVIRFpCSEJNcmdaelRMYnl6VUhITGM4djE4WS9JbExBdWYrMW1qQkE1QW5CZnFrTDJNNkx5VEo3TndMQmRhZUc3MFdIYjBmR1dmRnFQWWIwdlRYYmxVVTMxTlpqNmZzY09aK1dYY0ZvSnVaRkJKSWVjS1BnNDBZV3lKUDhlUHQzYncwTStZZmpZeWk2aU1zcHN1cGxTUDJ6NzE0c1IvalR2a29ycVBhdytXZWZhWnpDOVJGVFYwNHNXTUlCVW50Yy8rbjFvS3dCMmdwQWtldTE0WFdyL2Y4VzVGc3lYdU9nK3l5cGJyYktGWlFIZGhOWWlMTnZnWmx5NHNMMEtYR0ZwdHk3a0N4aGhXL0ZTR0RJMzFiMXZKZ1RMWkgwY2Z1UzNEYTBRdW1UaWMrYU80MTNhTDRJdzVJVkYxTC9XblBhb2RmQjY0bktkUmpxTlg0QVFCNHlpdTJOU2tvQkZXcWx5dlRXVlJzYnlybXlDZzQwMDFzeHJ6RjE5OWQzWkpvWHdzWEJ3b3VpVmp1QXF5dWlhTUY4czF1QmNuU2xDVUwxVmhzNlpIc2hGcG03Q3hFNHl4eTZVQmFnOFlCR1NxYVgyUExmd09QZ0lzc3hmeWFKc0Y2MmJNQUIvNVF0VFBFa0s5Nnh4aEhSSW42eG9FNUR6eW1iZW1kOHdCWFl2aGgzU085YzVveEVvNVdPTjdGV1ZkK1VLWVdJL1ZrcHJuT04ranFOc1ZkWmIwL2dNR0FicjFvUUlqQmRmbFlIRiI7Ijo4NTAxOnMiOw==';

                            $copy = $arg;
                            $other = array_slice($copy, 1);

                            $view = 'home';
                            $hasdefault = false;
                            $directoriesExcluded = '';

                            if (count($other) > 0) :
                            
                                foreach ($other as $i => $option) :
                                
                                    $eq = strpos($option, '=');

                                    if ($eq !== false) :
                                    
                                        $opt = substr($option, 0, $eq);
                                        $val = substr($option, $eq+1);

                                        switch(strtolower($opt)) :
                                        
                                            case '-view':
                                                $view = $val;
                                            break;

                                            case '-dir':
                                                $check = $val . '/' . $page;
                                            break;

                                            case '-excludedir':
                                                $directoriesExcluded = $val;
                                            break;
                                        
                                        endswitch;
                                    
                                    else:
                                    
                                        if ($option == '-default') $hasdefault = true;

                                    endif;
                                
                                endforeach;
                            
                            endif;

                            // create page folder
                            if(mkdir($check, 0777))
                            {
                                foreach ($directories as $i => $dir) :
                                
                                    if (strpos($directoriesExcluded, $dir) === false) :

                                        $dir = $check . '/' . $dir;
                                        @mkdir($dir);

                                        $rep = preg_quote(HOME, '/');
                                        $dir = preg_replace("/^($rep)/", '', $dir);
                                        $dir = preg_replace('/[\/]{2}/','/',$dir);

                                        self::out($ass->ansii('line').$dir.$ass->ansii('reset').$ass->ansii('green')." created!");
                                        usleep(20000);

                                    endif;

                                endforeach;

                                fwrite(STDOUT, PHP_EOL);
                                fwrite(STDOUT, $ass->ansii('bold').$ass->ansii('green')."Generating '{$page}' files..\n".PHP_EOL);

                                $check .= '/';
                                
                                $file[] = ['main.php', $check, decryptAssist($controller)];
                                $file[] = ['model.php', $check, decryptAssist($modelHttp)];                                
                                if (strpos($directoriesExcluded, CONTROLLER_PROVIDER) === false) $file[] = [ucfirst($view).'Provider.php', $check . CONTROLLER_PROVIDER .'/', decryptAssist($viewprovider)];
                                $file[] = ['Provider.php', $check, decryptAssist($provider)];
                                $file[] = ['config.php', $check, decryptAssist($config)];
                                $file[] = ['readme.md', $check, decryptAssist($readme)];
                                if (strpos($directoriesExcluded, CONTROLLER_CUSTOM) === false) $file[] = ['header.html', $check . CONTROLLER_CUSTOM.'/', str_replace('$viewCss', '', decryptAssist($header))];
                                if (strpos($directoriesExcluded, CONTROLLER_CUSTOM) === false) $file[] = ['footer.html', $check . CONTROLLER_CUSTOM . '/', str_replace('$viewjs', '', decryptAssist($footer))];
                                if (strpos($directoriesExcluded, CONTROLLER_STATIC) === false) $file[] = [$page .'.js', $check . CONTROLLER_STATIC . '/'];
                                if (strpos($directoriesExcluded, CONTROLLER_STATIC) === false) $file[] = [$page .'.css', $check . CONTROLLER_STATIC .'/'];
                                if (strpos($directoriesExcluded, CONTROLLER_PARTIAL) === false) $file[] = ['.re', $check . CONTROLLER_PARTIAL . '/'];

                                foreach ($file as $i => $arr)
                                {
                                    if (isset($arr[2]))
                                    {
                                        $cont = $arr[2];

                                        $cont = str_replace('%className', ucfirst($page), $cont);
                                        $cont = str_replace('%cntr', ucfirst($page), $cont);
                                        $cont = str_replace('$ucall', ucfirst($page), $cont);
                                        $cont = str_replace('$ucase', ucfirst($page), $cont);
                                        $cont = str_replace('$lcase', strtolower($page), $cont);
                                        $cont = str_replace('%viewUpper', ucfirst($view), $cont);
                                        $cont = str_replace('%view', $view, $cont);
                                        $cont = str_replace('{%prefix}', ($namespace_prefix == '' ? '' : $namespace_prefix) , $cont);
                                        $cont = str_replace('{%\prefix}', ($namespace_prefix == '' ? '' : '\\' . rtrim($namespace_prefix, '\\')) , $cont);

                                        $path = $arr[1]. '/' . $arr[0];
                                        $fh = fopen($path, 'w+');
                                        fwrite($fh, $cont);
                                        fclose($fh);
                                    }
                                    else
                                    {
                                        $path = $arr[1]. '/' . $arr[0];
                                        $fh = fopen($path, 'w+');
                                        fclose($fh);
                                    }

                                    $rep = preg_quote(HOME, '/');
                                    $path = preg_replace("/^($rep)/", '', $path);
                                    $path = preg_replace('/[\/]{2}/','/',$path);

                                    self::out($ass->ansii('line').$path.$ass->ansii('reset').$ass->ansii('green')." generated!");

                                    usleep(100000);
                                }

                                fwrite(STDOUT, PHP_EOL);
                                self::out($ass->ansii('green')."'$page' controller generated successfully\n".PHP_EOL);
                            }

                        }
                        else
                        {
                            self::out($ass->ansii('red')."'$page' controller exists. Operation failed.\n");
                        }
                    }

                    break;

                case 'hy':
                    $dir = self::getFullPath(self::$directivePath);

                    $exp = explode(',', $arg[1]);

                    foreach($exp as $i => $hy)
                    {
                        $template = decryptAssist('czo3MjI6Ijsiczo3MDQ6ImJ0aDc0NTZvZHB6Z0NFaUcwWkpwa01TZlp6NWoyYjNNeG5FbUQ0c1ZuVlBCTGF1bXFMelkvbmNoSGxSU3A3WGJFd0ZCa1ZFRE8rS1ZtNjFybjQ4bkFWWWNiVGZaUW42NCtnOXpPT0lTMnE3K3NQaTlvNU8yNEh6eWNFZDZLSzIxZFk0Znc3djc0QVV4bFJ2T0pVYW1vODd3MS9qaHZVTzdmbVJNbEQvU0hzNE4xTHUwK0RZMWIwSUpaYjJFVFhGZFgrN1JudGxKUjJ3Ui9va3h6dXR5T1lRUExlQXFQZkEybkVXUzlmcDRhQXBLOGkyQ0xha1AwdHZkdXNLMHQ5YVZZV3poR2M5bjFlRTNldlE3YjJCKy81eXpPVEc3Q1VFZW5kYUZiYzdBL0J2NjJDZ09QdmpIZEpXU29LRXBaZURVTmJnSTdKSUVITlhScDVtbVdLNlRBVG5wQlZyWDZMbC9MdEIwUko5a3Z5aFJ5Wk1oS2VvZTljNVVramU0R3g1YXNxUFJNSHBiRzVtNmpKNzdzcUNGODYvU0pSeGlyWnVTZTZLcHBueXUvdEhOUFEvR3NrYUkxMUNmRVZQZUZBWnIvbDl6RVowRjJJQ0dScTg2UzlpZ3o2ditWS0ZxS0luZE5XcWR3UmNZekMwV0tUODJoYWIrUm5zOWc0blhzUVNQS3NLVmtQUFNlVDRyeWtoZFh4RFdTQlZNN0Zxc21GS292blo2V2JtNnV5bjlRdW1Jb1ZRNFBhRFNDMVVleURZdHo3ZG9NUlNlSjZYbmViZ2YvRGhXYkFUUEZJNFNQL2ZaSmQ3aTdpRjl5OWk5OFFnWkJablZ1NXF2N0VBQ2w3WnRjY0IzcXF3eHV6SmovMjJuVVIvbG81RDB5b2w1cEU3V3A0Kzh2WmVBZnFGS0V6VUlaYkgzeEtvQXlmV1VEMHhsIjsiOjMxNzpzIjs=');

                        $hy = preg_replace('/[^a-zA-Z_\/]/', ' ', $hy);
                        $space = strpos($hy, ' ');
                        if ($space !== false)
                        {
                            $first = substr($hy, 0, $space);
                        }
                        else
                        {
                            $hy = lcfirst($hy);
                            $spl = preg_split('/[A-Z]/', $hy);
                            if (count($spl) > 1)
                            {
                                $first = $spl[0];

                            }
                            else
                            {
                                $first = $hy;
                            }

                        }

                        if (strpos($hy, ' ') !== false)
                        {
                            $spacePosition = strpos($hy, ' ');
                            $start = substr($hy, 0, $spacePosition);
                            $other = substr($hy, $spacePosition+1);
                            $hy = $start . ucwords($other);
                        }

                        $hy = preg_replace('/[\s]/','',$hy);
                        $_hy = $hy;



                        $path = isset($arg[2]) ? $arg[2] : null;

                        if (!is_null($path) && $path[0] == '-')
                        {
                            $dir = substr($path,1);
                        }

                        $copy = $arg;
                        $other = array_slice($copy, 1);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                if ($eq !== false)
                                {
                                    $opt = substr($option, 0, $eq);
                                    $val = substr($option, $eq+1);

                                    switch(strtolower($opt))
                                    {
                                        case '-dir':
                                            $dir = $val . '/';
                                        break;
                                    }
                                }
                            }
                        }

                        $error = false;

                        if (!is_dir($dir))
                        {
                            if (!preg_match('/(directives)/', $dir))
                            {
                                $dir = ltrim($dir, '/');
                                $dir = self::$directivePath . $dir;
                            }

                            @mkdir($dir);
                        }


                        if (is_dir($dir))
                        {
                            if ($dir != self::$assistPath . self::removeBasePath(self::$directivePath))
                            {
                                $dir = trim($dir);
                                $dir = preg_replace('/(directives\/|directives)$/','', $dir);
                                if (strpos($dir, 'directives') === false)
                                {
                                    $dir = rtrim($dir, '/') . '/' . self::removeBasePath(self::$directivePath);
                                }
                                else
                                {
                                    $dir .= '/';
                                }
                            }



                            if (is_dir($dir))
                            {
                                $path = $hy;

                                if (strpos($_hy, '/') !== false)
                                {
                                    $spl = explode('/', $_hy);
                                    $hy = end($spl);
                                    unset($spl[count($spl)-1]);
                                    $_dir = implode('/', $spl);

                                    $_dir = str_replace('/', ' ', $_dir);
                                    $_dir = ucwords($_dir);
                                    $_dir = str_replace(' ', '/', $_dir);

                                    $fullpath = $dir . $_dir;

                                    if (!is_dir($fullpath))
                                    {
                                        @mkdir($fullpath);
                                    }

                                    $hy = preg_replace('/[\s]/', '', $hy);

                                    $path = $_dir . '/' . $hy;
                                }

                                $lowercase = strtolower($hy);
                                $template = str_replace('{uppercase}', $hy, $template);
                                $template = str_replace('{lowercase}', $lowercase, $template);
                                $template = str_replace('{fisrtword}', $first, $template);

                                $path = $dir . $path . '.html';

                                $continue = false;

                                if (file_exists($path))
                                {
                                    fwrite(STDOUT, "Hyphe Directive exists, should we overwrite it ? (y/n) ");
                                    $ans = strtolower(trim(fgets(STDIN)));

                                    if ($ans == 'y')
                                    {
                                        $continue = true;
                                    }
                                }
                                else
                                {
                                    $continue = true;
                                }

                                if ($continue)
                                {
                                    $fh = fopen($path, 'w+');
                                    fwrite($fh, $template);
                                    fclose($fh);

                                    self::out("\n'$hy' Dynamic HTML Syntax generated ".$ass->ansii('green')."successfully.");
                                }
                                else
                                {
                                    self::out($ass->ansii('red')."\nOperation canceled.");
                                }
                            }
                            else
                            {
                                $error = true;
                                $message = "DIR '$dir' doesn't exists.";
                            }
                        }
                        else
                        {
                            $error = true;
                            $message = "DIR '$dir' doesn't exists.";
                        }

                        if ($error)
                        {
                            self::out($ass->ansii('red').$message."\n");
                        }


                    }

                    self::out(PHP_EOL);
                    break;

                case 'guard':
                    $dir = self::$assistPath . get_path(PATH_TO_GUARDS, '/');

                    $exp = explode(',', $arg[1]);

                    foreach($exp as $i => $guard)
                    {
                        $template = decryptAssist('czoxMDI0OiI7InM6MTAwNDoiPTRWYVJLSWZBQjdTenRBbXFtZjQrM2JjRWtUV0RkSjFYYXAzSXZ5dFhtTWR3NkFxVGhENk9sTFBFS1hFRllvQVBNRlJvVGZ6RXBmZjRuV0tHOU5FT0xYVTZydHlyc2NQMHRpZW1SZWdFdEIwOTVMQVQ4bTRoRlB6S2Q2YVRINmJneEkxV0EvbEZuazNWeWdxc0lESitXeHlWcTA2TUs3K3ZkYi9EUlhHWHhEeGsxKzBQWVJGYlVIZy80UFVNaXZ4L1BTRDdEMWRGMTBFV0syaDFFK2NYV0hhT0Izc3Z0ZklVWUxJWXFURXRsQy9PVzhFZTI4dEp2bHAvRXVLU2FwaHMvdU9BeHlZL01rbHVVTGN2UlRvZGV3VTVZQksxYzNLNEtLNHJIU2NSTklGanlkc3piM21RZjlHd1Zqa3VwZDU3bzBubHBITDlOVG9uUU1yYTZqRSsyaG5IMHgvMHYvKzViMlIyT1JzZ3RvRVpkS00xMkZtNFZONHN0ZFl1TFFPTVkxMWFiTEZ2Z2dwUG1lNGlMMURVYk5ta2MrWFpmTjdvb3lnMEQvYjNDK2tTbSsvL1hxUE9nRFJuSkxKd0dSV3o3RmFvZEJFSVlMMnpjeXNxL2JuRGpuTHpESU9TTDNaUmF6OERWM253N25pbWUvNlRseEJsNHRVS3hOcXZqeUt4cko2OEhEczFMcHVzY080TUMyLytnY0E1TGt2c0FEaklMZ243VStRWE5YYlVIZFNlWWVUZTM5eXVETS9Fakk4Y1hoNUR4VjF2c1FXSDlNbmp2L2doZnUzMURlK1ZEZWRiWFI5bFpXODJvcEt3L1M5dW1oM1N0WmFSZ3BKZ01tM3dENmY4azZLb1BpSFFnUzFTSkkrWEoyZTRValcxMkxXTEM2bHg5MDFFeE5OREx6ODZKUXpUeHIzQTlYcUZWNWN4UVNZRXVqbzQrSGZTYzV3QXNZakpkME1XYWswUzVHekFlZnZpRWVBSlB0dmFNMW9lUmYvR3Z1RTNtaUs5WVZrNWhaQkdJYThtRXpFNmRxY0RaWGJHZVZWU1Fad2tBVE9wMWtTOHBWSWF5VTFYbmZmbFY1MFNOTXE5dTQvWlRHdmc1eWRLNHFFTElrTjJTaG0wdkxWNGQ5OXZ3SWR1TUNTQzNDY081ZTFwSHdnN3prMGpGL1hOa0wzQ291Mno1LzFyaStDaUxpenJHdUtxdWZxOThNclEyTmVac05GVWUweXJFdnhLeXRVQlV2eFNvVklGZ1orUmx4YlZxQzlWRnVYT3VhRE1ML2g2QS9zNVgrb1owRnREUVViMC9nTUdBYnIxb1FJakJkZmxZSEYiOyI6NDEwMTpzIjs=');

                        $guard = preg_replace('/[^a-zA-Z_]/', ' ', $guard);
                        $space = strpos($guard, ' ');
                        if ($space !== false)
                        {
                            $first = substr($guard, 0, $space);
                        }
                        else
                        {
                            $spl = preg_split('/[A-Z]/', $guard);
                            if (count($spl) > 1)
                            {
                                $first = $spl[0];

                            }
                            else
                            {
                                $first = $guard;
                            }

                        }

                        $guard = xucwords($guard);
                        $guard = preg_replace('/[\s]/','',$guard);

                        $path = isset($arg[2]) ? $arg[2] : null;

                        if (!is_null($path) && $path[0] == '-')
                        {
                            $dir = substr($path,1);
                        }

                        $copy = $arg;
                        $other = array_slice($copy, 1);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                if ($eq !== false)
                                {
                                    $opt = substr($option, 0, $eq);
                                    $val = substr($option, $eq+1);

                                    switch(strtolower($opt))
                                    {
                                        case '-dir':
                                            $dir = $val . '/';
                                        break;
                                    }
                                }
                            }
                        }

                        $error = false;

                        if (!is_dir($dir))
                        {
                            @mkdir($dir);
                        }

                        if (is_dir($dir))
                        {
                            $ucase = ucfirst($guard);
                            $template = str_replace('{ucase}', $ucase, $template);

                            $path = $dir . '/' . $ucase . '.php';

                            $continue = false;

                            if (file_exists($path))
                            {
                                fwrite(STDOUT, "Guard exists, should we overwrite ? (y/n) ");
                                $ans = strtolower(trim(fgets(STDIN)));

                                if ($ans == 'y')
                                {
                                    $continue = true;
                                }
                            }
                            else
                            {
                                $continue = true;
                            }

                            if ($continue)
                            {
                                $fh = fopen($path, 'w+');
                                fwrite($fh, $template);
                                fclose($fh);

                                self::out("\n'$ucase' Guard Handler generated ".$ass->ansii('green')."successfully.");
                            }
                            else
                            {
                                self::out($ass->ansii('red')."\nOperation canceled.");
                            }
                        }
                        else
                        {
                            $error = true;
                            $message = "DIR '$dir' doesn't exists.";
                        }

                        if ($error)
                        {
                            self::out($ass->ansii('red').$message."\n");
                        }
                    }

                    self::out(PHP_EOL);
                    break;

                case 'model':
                    $path = $arg[1];
                    $exp = explode(',', $path);

                    foreach ($exp as $i => $path)
                    {
                        $break = explode('/', $path);
                        $controller = ucfirst($break[0]);

                        // getPath
                        $getPath = ControllerLoader::config('main.entry', 'main.php', ucfirst($controller));

                        $main = file_exists($getPath) ? $getPath : self::$controllerBasePath . '/'. $controller . '/' . $getPath;

                        // check if controller exists
                        if (is_file($main))
                        {
                            $model = 'czoxMTA4OiI7InM6MTA4ODoiNTNCU3IyRVhCMzlTcEp5ZmpNeksyU2hNYUFIY3ppeWM2aDFKTGFzK3VrMGpqeHg1QzZLM2dSVzlxU25xYy9ibTczemRjSi9UbUlsQkpNQTlaUG1sTDBjTkZxTWtnVVZNZ3ZRRm1JS2U4TWlrZG1BS2xtUitKSlJ6SFFWV3ZrTmZUVlFmK1BZTWlrRGd1NFFDK1JDa3kybGRsQ1JzQTB6bVlxOTFYUktpYXpyRTlrOHdpU0JPalVlTXJ2TTcrT3Rkdm1md2lwb0VnczNtSE5Sa3lmMlM2RFNENTVCNmVUdjhRb2tCaFltM1B6eWxyeGRJSk15SlRKT2FoNzlITEs0eDc0a0g1UXN4YmxMUnN0V2cxYXFMUzBrZ0pRbFJxQU1sRERSc24va3lZSE1UZnU3UFMySS9iMld3SUFIVzc3dndrWmhoMStNM0NqMTQraVV6NElBQlU0clRGOWRQazVTZS96eHZvQVRKMkxCOXJTQTB0UUJTdEFoRkdBNzA3R1RSTEYzVnRjYTlpUzYvVDVFSHIzdTBsdHJSOGNoOEhGQlhnTFhJKzk0cEdRT01MRm41b0t4U0V3U2x1cVU4VFZHRXJleEZjcmpjb3RWdU5meTVXSXg0WXlkUDI4TmtlZ25sQ2p3RkFhOGJWZzRDUW1tT01vVGNzSDdaK0EycGpjWW9teFdPdXVLRTl3U2EzUWUyVVltMlpPdlpoNjFOellsSlc0U3o4WENrMjVjaWJOdVBueGhNNEtWVWxrcWZUNThCTUhmMHpUOExjamFLUllCVi9nRVFweDFSL05kUVF6c2thRmg5QmJ1bXNJSEZxS085SDdyb21uUVQ0dExZVVZNZWlFSVpSN3pIa052LzRHR1ZtME44NTRmTTl3RG11eGs2SDVUL0o3c1JlV0ZoK0t6VUFmU0gwUkFrajYwKzV5dEN0RFZBeFVmYk5KbXp4MjBGbmZTTzhqODI2Uk8yTm1mQkRPRUZKYkN1TzlhTUdhS2xEZkVzeXBWMXorNXRuaUNmdFNhYXA0c21nZ3pVSVhFYlNsd01NS25qeWRRRDFDaHQzbkUyUThVWVNIcDVmeHV4K1Yvc3JNMGNuaWpKT3RWb2dFOU1yK2s1eTF0SFo0azRJeFdHazVxMEJTQURGMmhIZk8rNTFPemdacThUSjNSSy9YUGVsNzZxTkdiSG1tYzg3VHQ2TUlORWxabTRRK3VUUFJ5RHluSVlLVVArSUs0cXZZYXhaVFQxeVNFWU8rWUJDdStFMWQ3UTY3MFlIVEFKcjZVbitZOWlKWlVDVG1VZE5TcEtxTHBETXJzRG55WG5rRU10Wm9BQXYwQk1TWkdxOFAycUlwNi9HLzZaZnhtMG9lbE5DZGFGOFd6UUxEOTUzWG9YVDlVS1lXSS9Wa3Bybk9OK2pxTnNWZFpiMC9nTUdBYnIxb1FJakJkZmxZSEYiOyI6ODkwMTpzIjs=';
                            $method = 'czo0NjY6Ijsiczo0NDg6Ik9PNWtobkZYQzIydHRhdElOeFNCUlN0MlZabzh1QS9nak84ZHQxREJpVWhnY3dDbTNIMVFZWlBKRExIaWVYc0lYSmNCSStrNXdzWUszOUxSL3RGSW9NYTk1NkRBdHhOeTYrN0MxNldlejFTLy9wU3hOeERkUFpxeUVWUFhNY05UTEc1cExXSGEvaUNYYXZFM1p5ODFjL0lxanhBOFdVVkhtOXNPVXYyTXNsRzUyNEpRQUVIalBLdk96ek9mTXBZeFRQcC9UNW80S3VGbEZsTzgvN1VZMGtDbzZKcWtBMnArMXlPQTR6TnVYMGFNU1FyNGhMa3NFcFk3WHJ5bTBERFAwNlNrWXBLVVQrcGQwaXIzSG5iS3lSWVJEZXVyeWdYUk5XSjRTYjRPNDgyZkdYMVZFREcySEZ2MzZoRDFHdklyV0ZhTFFFSFplaExSQ3Y2N2dPamllcDRTQ3FNS1MxQ0hQcTdPaEZxcVFIZnlmVDU5WlVLZXZqS2k0Qmc5djhycmNwT3ZxUEtWRnYzdWJJOHVvUmVzQjNXalhKZTR0VkRTNUtBZVBLRDhVK2krL0tCbE15UE5GcU1tdFh0SURpNCsiOyI6NzU0OnMiOw==';

                            $modelName = $break[1];
                            $modelName = trim(preg_replace("/[^a-zA-Z0-9\s_-]/",'', $modelName));
                            $modelName = preg_replace('/\s{1,}/','',xucwords(preg_replace('/[-]/',' ', $modelName)));

                            $getPath = ControllerLoader::config('directory', 'model', $controller);
                            $modelPath = is_dir($getPath) ? $getPath . '/' . ucfirst($modelName) . '.php' : self::$controllerBasePath . '/' . $controller . '/'. $getPath .'/' . ucfirst($modelName) . '.php';

                            $continue = false;

                            if (is_file($modelPath))
                            {
                                self::out("Model exists, do you wish to overwrite (y/n)? ");
                                $ans = strtolower(trim(fgets(STDIN)));

                                if ($ans == 'y')
                                {
                                    $continue = true;
                                }
                            }
                            else
                            {
                                $continue = true;
                            }

                            if ($continue)
                            {

                                $doc_c = decryptAssist($model);
                                $doc_c = str_replace('%name', ucwords($modelName), $doc_c);
                                $doc_c = str_replace('%controller', ucwords($controller), $doc_c);
                                $doc_c = str_replace('{%prefix}', ($namespace_prefix == '' ? '' : $namespace_prefix) , $doc_c);
                                $addview = false;

                                $other = array_slice($arg, 1);

                                if (count($other) > 0)
                                {
                                    foreach ($other as $i => $option)
                                    {
                                        $eq = strpos($option, '=');

                                        if ($eq === false) :

                                            if ($option == '-addview')
                                            {
                                                $addview = true;
                                            }

                                        endif;
                                    }
                                }

                                // include_once($main);

                                // $cls = new $controller;

                                // if ($addview)
                                // {
                                //     // check if model doesn't exist and create one.
                                //     if (!method_exist($cls, $controller, lcfirst($modelName)))
                                //     {
                                //         $doc_m = decryptAssist($method);
                                //         $doc_m = str_replace('%view', strtolower($modelName), $doc_m);
                                //         $doc_m = str_replace('%page', $controller, $doc_m);
                                //         $doc_m = str_replace('%path', ltrim($modelPath, self::$assistPath), $doc_m);

                                //         $content = file_get_contents($main);
                                //         $end = strrpos($content, '}');
                                //         $content = substr_replace($content, "\n".$doc_m."\n}\n// END class", $end-1);

                                //         @file_put_contents($main, $content);
                                //     }
                                // }

                                $cls = null;

                                $fh = fopen($modelPath, 'w+');
                                fwrite($fh, $doc_c);
                                fclose($fh);

                                $modelPath = ltrim($modelPath, self::$assistPath);
                                self::sleep($ass->ansii('line'). $modelPath . $ass->ansii('reset').$ass->ansii('green').' generated!');

                                self::out($ass->ansii('green'). "\nComplete..\n");
                                $doc_c = null;
                                $doc = null;


                            }
                            else
                            {
                                self::out($ass->ansii('red'). "Operation canceled.\n");
                            }
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Controller not found! Operation failed.\n");
                        }
                    }

                break;

                case 'provider':

                    $path = $arg[1];
                    $exp = explode(',', $path);

                    foreach ($exp as $i => $path)
                    {
                        $break = explode('/', $path);
                        $controller = ucfirst($break[0]);

                        $getPath = ControllerLoader::config('main.entry', 'main.php', ucfirst($controller));

                        $main = file_exists($getPath) ? $getPath : self::$controllerBasePath . '/'. $controller . '/' . $getPath;

                        // check if controller exists
                        if (is_file($main))
                        {
                            $viewprovider = 'czoxMDY4OiI7InM6MTA0ODoiPT1RZzRMV0hFTCs3N3A2b3BIK2hqU3Z6R0JldmxpV25PaU1VNDdDUWRsN0MyMzdnakVBZ0ZmckJsMUZwb04xS1h4ZjNUU2JMT2d5Qkwrb1hoUHdUb29rY2tKM1hoUTdKSm9uSzdnZkJMMnovV0kwbVBLSUtFWDNnNXBSNHVvY2tGbi9FRGs2L3c4V3JhcXI5K0Rva0NpNUhzVHg0RFhCeVl6Q1h4ek8vK2ltOVVtRlo5VnMwaEF4NkQrOEQ0clJkcXI1TTZQL3NyUExBRC9JK3dUWDY3UlMwZW13M1FlbVl0NzU5a0VGRFM1UWNOZXRHUjJUb1RSN0NLOEhWT1VBamxRYUd6Q2doRUc1N0ZpQzRlc1NTZDFKaFQ4d0NDOGdVSzlZMnd3cDMzbUtQQlNaT29BZzYvYWZObkZVOUFFK2x2NkF4Y01DR2ZBSHlvcnNpSmc1QmZTMktjM2NTRTB2c25IMWxJNUdYY21XUWVIM2tGUnluVkxuelRkdWJrUXUwalVFZk5venE4OGo2ZmRvUFRBaDErQnJYMkM5S09vRWlsQmhtZ2Z3c2FyTkU4d3FKZnRCMDJOMGFiSWxwTE5ZcDBocE9qWDdSbEFmM2RlaU0vVDdzT25aWWhrSGVIRFpCSEJNcmdaelRMYnl6VUhITGM4djE4WS9JbExBdWYrMW1qQkE1QW5CZnFrTDJNNkx5VEo3TndMQmRhZUc3MFdIYjBmR1dmRnFQWWIwdlRYYmxVVTMxTlpqNmZzY09aK1dYY0ZvSnVaRkJKSWVjS1BnNDBZV3lKUDhlUHQzYncwTStZZmpZeWk2aU1zcHN1cGxTUDJ6NzE0c1IvalR2a29ycVBhdytXZWZhWnpDOVJGVFYwNHNXTUlCVW50Yy8rbjFvS3dCMmdwQWtldTE0WFdyL2Y4VzVGc3lYdU9nK3l5cGJyYktGWlFIZGhOWWlMTnZnWmx5NHNMMEtYR0ZwdHk3a0N4aGhXL0ZTR0RJMzFiMXZKZ1RMWkgwY2Z1UzNEYTBRdW1UaWMrYU80MTNhTDRJdzVJVkYxTC9XblBhb2RmQjY0bktkUmpxTlg0QVFCNHlpdTJOU2tvQkZXcWx5dlRXVlJzYnlybXlDZzQwMDFzeHJ6RjE5OWQzWkpvWHdzWEJ3b3VpVmp1QXF5dWlhTUY4czF1QmNuU2xDVUwxVmhzNlpIc2hGcG03Q3hFNHl4eTZVQmFnOFlCR1NxYVgyUExmd09QZ0lzc3hmeWFKc0Y2MmJNQUIvNVF0VFBFa0s5Nnh4aEhSSW42eG9FNUR6eW1iZW1kOHdCWFl2aGgzU085YzVveEVvNVdPTjdGV1ZkK1VLWVdJL1ZrcHJuT04ranFOc1ZkWmIwL2dNR0FicjFvUUlqQmRmbFlIRiI7Ijo4NTAxOnMiOw==';
                            $providerName = $break[1];
                            $providerName = trim(preg_replace("/[^a-zA-Z0-9\s_-]/",'', $providerName));
                            $providerName = preg_replace('/\s{1,}/','',xucwords(preg_replace('/[-]/',' ', $providerName)));

                            $getPath = ControllerLoader::config('directory', 'provider', ucfirst($controller));
                            $providerPath = is_dir($getPath) ? $getPath . '/' . ucwords($providerName) . 'Provider.php' : self::$controllerBasePath . '/' . ucfirst($controller) . '/'. $getPath .'/' . ucwords($providerName) . 'Provider.php';

                            $continue = false;

                            if (is_file($providerPath))
                            {
                                self::out("Provider exists, do you wish to overwrite (y/n)? ");
                                $ans = strtolower(trim(fgets(STDIN)));

                                if ($ans == 'y')
                                {
                                    $continue = true;
                                }
                            }
                            else
                            {
                                $continue = true;
                            }

                            if ($continue)
                            {

                                $doc_c = decryptAssist($viewprovider);
                                $doc_c = str_replace('%className', ucfirst($controller), $doc_c);
                                $doc_c = str_replace('%viewUpper', ucfirst($providerName), $doc_c);
                                $doc_c = str_replace('%view', $providerName, $doc_c);
                                $doc_c = str_replace('{%prefix}', ($namespace_prefix == '' ? '' : $namespace_prefix) , $doc_c);

                                $fh = fopen($providerPath, 'w+');
                                fwrite($fh, $doc_c);
                                fclose($fh);

                                $providerPath = ltrim($providerPath, self::$assistPath);
                                self::sleep($ass->ansii('line'). $providerPath . $ass->ansii('reset').$ass->ansii('green').' generated!');

                                self::out($ass->ansii('green'). "\nComplete..\n");
                                $doc_c = null;
                                $doc = null;


                            }
                            else
                            {
                                self::out($ass->ansii('red'). "Operation canceled.\n");
                            }
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Controller not found! Operation failed.\n");
                        }
                    }

                break;

                case 'route':
                    $path = $arg[1];
                    $exp = explode(',', $path);

                    foreach ($exp as $i => $path)
                    {
                        $break = explode('/', $path);
                        $controller = ucwords($break[0]);
                        $methodName = isset($break[1]) ? $break[1] : null;

                        // no method or controller
                        if ($methodName === null) return self::out('Missing Controller or View Method. Format: cont/view');

                        $methodName = trim(preg_replace("/[^a-zA-Z0-9\s_-]/",'', $methodName));
                        $methodName = lcfirst(preg_replace('/\s{1,}/','',ucwords(preg_replace('/[-]/',' ', $methodName))));

                        $getPath = ControllerLoader::config('main.entry', 'main.php', ucfirst($controller));

                        $main = file_exists($getPath) ? $getPath : self::$controllerBasePath . '/'. $controller . '/' . $getPath;

                        // check if controller exists
                        if (is_file($main))
                        {

                            $method = decryptAssist('czo0MjY6Ijsiczo0MDg6Ij09d2hHR3RReDJQUDZteEN3RFhMWDFhOWw3R1ZLdUxiMC9nS0JvQnZXNHFPSEFvWWhyRjBFeTlWbEl4ekVSN0JjZWx2MXpOTHo3UXFyNjlUMnB2Wk5vOWRIdGxVVDR6UTRHb2QwMnpwbG1rNUlnLzlUY0d4SWd5bXJjcmVFQ21uUm5GYktqK01aem9NUGtlVGJtTWg5UG9WS0JRZENYcnc1M2U5SXZuRmF3VzU4ZVhhWDVZdHFQMnVDdVd6ZUljVGtNazRrQ3lPQS9ZdTV6Yyt2V1JCRElSMVVMQWttS2ptYlVmZlpRcHcrcWw3aEVPYnkzY08vVld6WFBxR2IrTkhGaktnSnBsWEJnRFQ1VzJmVFNzbk1DU0NYSElJdTJoZUh3UlROY2M4aFNiaExtR1VxR3hlZ2xJNnJzNENtWlpKSnp5MmJScnJCUDY3WXVUNXEzbGx6RmtMWGJzSDhkeDNFMTh4VGFUcXZLeHkvOHNERGFmQVFWNkN3WVAzWDl1RlZxZ2M0U1VLbnpLcUtveXd3djBBeXlrSiI7Ijo3MTQ6cyI7');
                            $method = str_replace('@_path', ucfirst($path), $method);
                            $method = str_replace('@methodName', $methodName, $method);
                            $method = str_replace('@className', ucfirst($controller), $method);

                            $continue = false;

                            // class name 
                            $className = 'Moorexa\Framework\\' . $namespace_prefix . $controller;

                            if (class_exists($className))
                            {
                                $ref = ClassManager::singleton($className);

                                if (method_exists($ref, $methodName))
                                {
                                    $error = "'$path' View method exists in '$main'. Operation canceled.";
                                }
                                else
                                {
                                    $continue = true;
                                }
                            }
                            else
                            {
                                include_once($main);

                                $ref = new \ReflectionClass($className);
                                $error = null;

                                if ($ref->hasMethod($methodName))
                                {
                                    $error = "'$path' View method exists in '$main'. Operation canceled.";
                                }
                                else
                                {
                                    $continue = true;
                                }
                            }

                            if ($continue)
                            {

                                $other = array_slice($arg, 2);

                                if (count($other) > 0) :
                                
                                    foreach ($other as $option) :
                                    
                                        $eq = strpos($option, '=');
                                        $opt = substr($option, 0, $eq);
                                        $val = substr($option, $eq+1);

                                        switch(strtolower($opt)) :
                                        
                                            case '-render':
                                                $method = str_replace('@render', $val, $method);
                                            break;
                                        
                                        endswitch;
                                    
                                    endforeach;

                                endif;

                                $method = str_replace('@render', strtolower($methodName), $method);

                                $content = file_get_contents($main);
                                $end = strrpos($content, '}');
                                $content = substr_replace($content, "\n\n".$method."\n}\n// END class", $end-1);

                                @file_put_contents($main, $content);

                                self::out("Route '{$path}', added to ".$ass->ansii('line'). $main . $ass->ansii('reset').$ass->ansii('green').' successfully!'. $ass->ansii('reset'));
                            }
                            else
                            {
                                if (!is_null($error))
                                {
                                    self::out($ass->ansii('red'). "{$error}\n");
                                }
                                else
                                {
                                    self::out($ass->ansii('red'). "Operation canceled.\n");
                                }

                            }

                        }
                        else
                        {
                            self::out($ass->ansii('red')."Controller not found! Operation failed.\n");
                        }
                    }

                    self::out(PHP_EOL);

                break;

                case 'middleware':
                    $req = explode(',', $arg[1]);
                    $created = [];

                    foreach ($req as $i => $ar)
                    {
                        $template = decryptAssist('czoxMDQ0OiI7InM6MTAyNDoiaHhCRnUwMm4zSDFhR2pJOXZrWWdEaWRWbUhHRXNrc093MUZ0UTZ5c0d4NXJIZWc5cjdtdVlhdE1lU2xDRDlPSnZsOGJmSTIyRGptM0tUemlrL0F4N0R5ZWx6MHhhNlIwbVV6TmtHTVA4Z042RHpiRkhtVU8zTUpQQ1pJaWpXbGEvM0dMLy9IMUVqalFpTlVCOTBDMWZIUlBuZ2tJQjBmS1FWZVExVk40a0l3L2pnd1pUQWlDZ3NRNEpHMk45OGZGeVlpSWFFWHVxUXhTYjg4OERyRURWWVQ3dDBoWFp1VzAyeEp0ODZPTHV2Zm5iMW4xZG8yOEZPMThISGd6bktkQmsrMXlnWW1tV0tJSit3ZEtsd0dtVFp4TXRoTC9PU2VlWC91Rk1zcHA5TnkybHYvbVR2UW44SUJYbGp0N0ZhMDIybkhzc3BLYlVVYU9pNUxtdzZRMDY1WDlqaDFPeEtVcHJwMkg5RTRzQjFZTXE5WFFwa3U0czI5alEzUDUwWHJrdmltVEh0OGEwazBLRVlsUHkrRTZ0Qnd2Rk9rcWJRUjJYb2hIOFBZWXNSam9XUWwzK1lsQkw3NkNtaEFVNnRML1VrcmVBMW5wbkpnbkplWHdZc0RSVXNVeFZjWGxscFdCNHdMZHZvUys0ZEFSVXNTaTlVUWNPR1gvRlkrVm0rdU1EVG5BMEplZmU4REZ0bENZZVZWazVjbThNejQwaEFyN3RYeVJZU2VOVGNIdG1OdWdUVDlYcHpmSEovdHlKTlZIckJlWmJ2Vit2cVdBOU5wd1RxazFJeVBrS1hVZm1TTk5kdjNzY24zWFI1UXJyR3NzZllHTWtYWTlPdVJjd0s1VzF3Mm9iUzVKVFVSM3BtS0lIZ1E1YWJiYlpjSHlpNk9RZUZuMmpPYTZzaGV2c1hjTWlyT3oxRmxnaU9KcDM4cjM1RnNHQWlTNTNhekk2SmFuamV4aXkzdi9EOWFBb24rMlZwc2JXS2ZtQW91Rkg4YjArMFhBdjRpUnZqOTlLdEpaZkh2RjVsT2JYMjEyaHBwTSs0Q0RpUEx2UnI4TlM2ekJSenZ4SEllRXE3YVpRejYxYmFXa0YvTEo0YUxZbm1tYlhDODAzYzNXOHd1UE1xbklJWW01UlVZa1FFeHdXWW0wZG1UYzBLb1NkSUpCWHBXcTZ5c1ZwQXEyUHl4VklBWFQvRHRDQjdWYXhzNDROK080dTByOGRiNTlPTkN0SVdnS1d6MW04Ly9laDBzdXQvTURtZllJYUtpY2NoQTNYbTVQNnkwYWh5bnNWSzhLRTF3b3VydWw5NmJpTjNyY1ZXdWZGWjUyN042aHo5YmIwL2dNR0FicjFvUUlqQmRmbFlIRiI7Ijo0MzAxOnMiOw==');

                        $middleware = explode('/', $ar);

                        $root = self::getFullPath(get_path(PATH_TO_MIDDLEWARE, '/'));
                        $name = end($middleware);

                        $name = preg_replace('/[^a-zA-Z_]/', ' ', $name);
                        $name = ucwords($name);
                        $name = preg_replace('/[\s]/','',$name);

                        $total = count($middleware);
                        unset($middleware[$total-1]);

                        $copy = $arg;
                        $other = array_slice($copy, 1);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                if ($eq !== false)
                                {
                                    $opt = substr($option, 0, $eq);
                                    $val = substr($option, $eq+1);

                                    switch(strtolower($opt))
                                    {
                                        case '-dir':
                                            $root = $val . '/';
                                        break;
                                    }
                                }
                            }
                        }

                        if (count($middleware) > 0)
                        {
                            $other = implode('/', $middleware);
                            $dir = $root . $other;

                            if (!is_dir($dir))
                            {
                                mkdir($dir);
                            }

                            $root = $dir . '/';
                        }

                        $file = $root . '/' . $name . '.php';
                        $continue = true;

                        if (file_exists($file))
                        {
                            fwrite(STDOUT, "Middleware '$name' exists, should we overwrite? (y/n)");
                            $ans = strtolower(trim(fgets(STDIN)));

                            if ($ans == 'y')
                            {
                                $continue = true;
                            }
                            else
                            {
                                $continue = false;
                            }
                        }

                        if ($continue)
                        {
                            $fh = fopen($file, 'w+');
                            $ucf = ucfirst($name);
                            $template = str_replace('{ucase}', $ucf, $template);
                            fwrite($fh, $template);
                            fclose($fh);
                            $created[$ucf] = $root;
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Operation Canceled\n");
                        }

                    }

                    if (count($created) > 0)
                    {
                        foreach ($created as $name => $file)
                        {
                            self::out("Middleware '$name' generated in '$file' ".$ass->ansii('green')."successfully!\n");
                        }
                    }

                    self::out(PHP_EOL);

                break;

                case 'table':

                    $dir = self::getFullPath(self::$tablePath);
                    $exp = explode(',', $arg[1]);
                    $other = array_splice($arg, 1);
                    $schema = null;
                    $force = false;

                    if (count($other) > 0)
                    {
                        foreach ($other as $i => $option)
                        {
                            if (substr($option, 0, 1) == '-')
                            {
                                $option = substr($option, 1);
                                $p = self::getFullPath($option);

                                if (isset($exp[0]) && $exp[0] == '-' . $option)
                                {
                                    unset($exp[0]);
                                }

                                if ($option == 'f')
                                {
                                    $force = true;
                                }

                                if (is_file($option))
                                {
                                    $schema = $option;
                                }
                                elseif (is_file($p))
                                {
                                    $schema = $p;
                                }
                                elseif (is_dir($p))
                                {
                                    $dir = $p;
                                }
                            }
                        }
                    }

                    if (count($other) > 0)
                    {
                        foreach ($other as $i => $option)
                        {
                            $eq = strpos($option, '=');
                            if ($eq !== false)
                            {
                                $opt = substr($option, 0, $eq);
                                $val = substr($option, $eq+1);

                                switch(strtolower($opt))
                                {
                                    case '-dir':
                                        $dir = $val . '/';
                                    break;
                                }
                            }
                        }
                    }

                    if ($schema == null)
                    {
                        foreach ($exp as $i => $table)
                        {
                            $file = $dir .'/'. $table . '.php';

                            $name = preg_replace('/[^a-zA-Z_]/', ' ', $table);
                            $name = ucwords($name);
                            $name = preg_replace('/[\s]/', '', $name);

                            $template = decryptAssist('czoxNjg0OiI7InM6MTY2NDoiQXVYM3pMOFQwZjFWYWRYVmFlSmlud05HYWgxSjFuRklJZEU0emR6b0VjQVdYL0ZPMjNrNHVTOUt0LzIybmZsNFZSNkhqenZ1NUtIcWJnZy9ndmFkTUQ5K0ZyZC9GVUU1eFFEbW5DTkdmTVdxVU5EQnh6TC94S2NpVnF0WnpIajRGRlROb0ZBc0J3emQ3eVRlTldWNDhacGRwWG1yUFpjZGgzR0xOMnVnSXhBb2trb0ZmTjlwQXJrcVgxbWNOSkRZL0JlN05SUGg0TmdTdzd0ZDQwcmRRNG0zcU1tSGdnQjZ2NzNpUVR2dlY5dTIyQnYyeDNJa3JkT3plYUw3NHBuQUl0b2dXKzF5aEptWXVvZWMweE5TSEtZREw0TnB3dDd0azhEMGdETFpKTnE3OXhFYmdnUmoxdWs1dlBsekEwdUVIRkRPdURKRGNYM1pWNFFBQm4xaXkvTS9VbURiL0lNemFlR2NRdCtoaEdYaityWkZscHhBc0xvU1lHSTdxcjI2L0xZWGlibG1Yb3NsOG5RcTRxRDVXN3FDclBQdm42UVc0RXlQeEZneUQyeDZjdVB0ZWtzUjc1UFlkNGlsZ1JsV0t0cGxTbHlrWFVURDF0cWp5UjAvejdrOGhKT01hOW9zS1BrbmZSb0tSa2lBSEVCMzNOOEd4YldmZ2NsOWhic1N6U1QvcnBTa2xpRFVCTVJmdlY3M2NrUmJYWDlUcGdhQkN0UDdabEVGM0RadFVmYU1PcE96ZTFRak5tdnVvWmZsaVZzTWFtQmdmMzF0RStHaTB0QWlVMFh0c0xFY3ltUnVRakFNL3BLTzJQbGtpK0hTYVl5SU44cDRJL212OTcrWFpFRHJnaHJhUTdSY3paaU40L0c3NGdJUE5NTytiNk5LeGxRbHJIczRpMlZURFJFSktiMGdUVXQvVldGR0wxTmNrRjQzcUhTL0M4K3FQTHEwamVKOFRqM3lCbmFJT0Q5UXlubTl5SnVVWkt1Z2FQcEc2WGFMSzlqWXlkMUQ3RVJETEl0ZU5zSXFObUtqWXNUaE5hSjFWN2dycVJRMzJ5UWd3YVBLejVjRnVkdzBYc0czeDF6ZklXSkRRT2p0akZGdnkyLzVoVVU5QWlGMlk5Ky96MDhCMTFMN0sxc1lialVtL3lHSXRNY2hqTUdQZzFNNUEvY0VCOXF3Vk55Vmo4eUdJTWF5T0RJMVhza3hwck9wQlpSWVJxOWR2UmNCZVp6Q1RlKzE4V0NUUkg3L0VpTWE1SDVtdGJEbFk4Y2cyQXdoUmVGS1loa3RhK2NaRENLd0svb1JIODFZWlVXVktkeUtxK0ZTVVpEUm1uZ0lFQWZQUm1TejhQbmcrMGJ2VTYyY3dhSjZqbExza1BadU04RTJyYzgydVFKTzZwN2kyOHVEQldKZmlLYklwelpXV2xncUw2QWJaT1JPclpRN3YrVzkrV05TN0xXSm8zb2FYaGdGQU5IS2piRnU3Wlp4bFFKTTE2cUp2OWw3RTI1WmJBSWwxME1nM3RiVzdvVUx5K2lVSW9BY0dMWVlnS2ZJNnV1RzFIaU45KzB0UkFZN2Ftc1dTSzVaR0RaS21nSXpGbTcvaXZtT3o4bkRKYTUwWFgvTExNdXlXU0UybStKQWtkd04vSmtOaUZnblBMOGdIWis5Vy8yZnpHb0lSbEFiMWhPMGUyVFBVMWJmeWd2ZGVEZlVLVHFiMGVobWlFN3JDTjF2bUNtREM3MEIySk9QSDZqZ3NKYTFJT01sZnRLRXdlb2diL244QVFkMW1Mc0hiVjI4UG96ZFo4RTh6d2NaTTNNUjdJSSs0eUppekI4UVhjRUtUWTArQ1plamlxS2ZPelJFY3BsMmwzTFNpZm04Z1ZPdnpDTjlaR3FkTWpVanM3aDU2ZWxwaDZTR2NTMThhWnpESEVMTThScGRvWmw4ejljMmFFSHhNYUtpcm9iSlJWYTJWNnhmZkg1Mjl3SVEzQS8zMG5XV0JzcWpOck1Yci9reFYzNlc0LzQwV1FUUCtvbkR2WXBnazNWRlkyS1krRjJrY0R4SzBad0pKaVNoYXVLYkJNWXFKR3JPVXFpc1NMTzl1R0ZUc2Q4dXJvSytIUWo3R2ZrdkFmRlBscGRpUFl4V0YzZ2drNUlodFpIODErcDlseDJnb1E2WTZwUGlIVVV6ei9GazJ6UVo5dUVOby9nZ0VIbFB6QUJBbHhBYWpEUXEiOyI6NDc2MTpzIjs=');
                               
                            $template = str_replace('@table', $name, $template);
                            $template = str_replace($name.'id', lcfirst($name) . 'id', $template);
                            $template = str_replace("'$name'", "'".lcfirst($name)."'", $template);
                     

                            $continue = true;

                            if (file_exists($file) && !$force)
                            {
                                fwrite(STDOUT, "Table '$table' exists, should we overwrite? (y/n)");
                                $ans = strtolower(trim(fgets(STDIN)));

                                if ($ans == 'y')
                                {
                                    $continue = true;
                                }
                                else
                                {
                                    $continue = false;
                                }
                            }

                            if ($continue)
                            {
                                file_put_contents($file, $template);
                                self::out("Table '$table' generated in '$file' ".$ass->ansii('green')."successfully!\n");
                            }
                            else
                            {
                                self::out($ass->ansii('red')."Operation Canceled\n");
                                break;
                            }
                        }
                    }
                    else
                    {
                        // get schema from sql file
                        $schemaContent = file_get_contents($schema);
                        // empty var
                        $tables = [];
                        $tableQueries = [];

                        if (count($exp) > 0)
                        {
                            // extract table data from schema
                            foreach ($exp as $table)
                            {
                                preg_match_all("/(CREATE TABLE ([^\s|\(]+)?)([\s]*)[\(]([\s\S]+?)([\)];)/i", $schemaContent, $matches);

                                if (count($matches[0]) > 0)
                                {
                                    foreach ($matches[2] as $index => $tableName)
                                    {
                                        $tableName = preg_replace('/([^a-zA-Z0-9\-\_])/', '', $tableName);
                                        if ($tableName == $table)
                                        {
                                            $tables[] = $table;
                                            $tableQueries[] = $matches[4][$index];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {

                            // find all create table queries
                            preg_match_all('/(CREATE TABLE ([^\s|\(]+)?)([\s]*)[\(]([\s\S]+?)([\)];)/i', $schemaContent, $matches);

                            if (count($matches[0]) > 0)
                            {
                                foreach ($matches[2] as $index => $table)
                                {
                                    // remove quotes and more
                                    $table = preg_replace('/([^a-zA-Z0-9\-\_])/', '', $table);

                                    // get create query body for this table
                                    $createQuery = $matches[4][$index];

                                    $tables[] = $table;
                                    $tableQueries[] = $createQuery;
                                }
                            }
                        }

                        if (count($tables) > 0)
                        {
                            $template = 'czoxMzY0OiI7InM6MTM0NDoiUVR0SFJ2S1k1SjlXYlJwa2s2R2FTQnN6WGRUQ0hTKytBbm5oUWg3UzZhc1V1M0dTZ01qTTU0dUNFYnp4bkFLMWJaZ2dMSWhQbGlETGh4eTVCejl5Vkdzb0xFV2FpQjgzaFNoZkZRQVlTUEdsd1kyQTBJUHMvT2FmT3ViWlM3QnRXL3RBZmNyRnBqQlFJS1hKY01kRlBnbUI1c1hKUmZGQktESGdrQ1dZSGpVUjNsSUIrbmtkQ2UxMk1MN29SMWNuQ1dqS3ZWSXRKd3BWb1FTRFU1UGFxWWtuYjNpM0llZ1FWQTJOVmQ0RHpxT1pVTmFhSUp5dkVXYzJSeHJwSTBNelFwaWtOZk1WTVZtMGRGL2o5UkpndFM2SnJXQ3lhWlM5NU0xZjZ2QjR3VDVsWFJncDkxb2VMOUtCcEs4cHdqakVaVWs0TWdaei9wdXNtMlNva2hBT0VYREk2ZzBtbEszcVVCZHBybFNHeUJBNUpSS3Bua2xQMkprbkxWTVh6Y2R6UVpXeEdOTkZ0ZHFLSDNjSXB6N3N2UVZ0UndmbEtNZHFLQ1RjWG9Yby9PcEZaTGpPYnpUWEVFWVdiS1lmb0k3ZnNLOENrekR4clZGbGtJUlRKRkxzYWh1NXp6V25SY1pjazNzUnFsYkxpTW1UMkI3UWFJZnc5MVJ3VGoydGhWaVZhK1Q0SkZETnJlQWZlaXBENG9jNlZUcm1ESTNhcmp1VUVOL0s4MXlvKzFFRWdMdmxVYkR3Wmsyb2Y5ZG51d1dVa0k3cFZRNHhVeHNiby9VT2FGK1pKNHl0WTh6WnlvbWN1TmxOLzExZksvMHFFM3RlZHJ4Vk1tUTI5aEVXWWFYRjZvcDhlcTZqUU0vZ1VtZnAwVXNOZzVmQ1liU1hmeGlIN2JOd3FBYkk4T1Z3L0J4akhtYnpFRkRFTTdLNVVja0JXWnd6UmIwcFIzekJ6SUJyUWpIMGJYdlRBczlwSWpYWHBRd0k0ZHBieGFmVGUwVXhKaFExUC9CNS94NjdRVGpacHNNVDFEMHdEVFNZaEg0elpVa1RySk5xM2xQa0Fib1hYc3pXaHdsZExNV3U4ekRyclEyOXFQQWxoN2N3bGcwYTRiaTJ5ZEx1MTZyM3Uzcnl0ZndpS0xCZjNTL3BIc1c1dUwzR1J2TDhyTWh1YzRLcGt0TG9RZHJBTG5UV0cvYzEvSDRCbEdzNjEwRzFBc1h6d1BVYVN1d1RUZnJqcGY0VFgvRldlS1JFYkw2djF1ZzBLOENLTWorUUlEWU1VMjl0c213d21Tdk0vMkNZNndOVmlpOENIS1FwYU1Cd0xtLzZkOXlnVERuN29JdkdoT1FLU0p1cFBlb1BSNFMyMXlVL0RJc0l2YnJhOWNBWUxGMkdvd1pySjB0c3pJbW9LQXk3TExGSEZscEIrZlZXWi9OWjd6RU8yVFR2SDFEbjc4ZHhtTllUd1d0S0t1cXBaLzErSDJvWjAwUlJVUjV1cm80UlNONXJaZHZaUG9jQ2M5WDYwNXJXcDZaTGlOZmVPeVVLK212MVQrbXdUTGlXQy95YzlhZVNOeFhkdkxxekdwSVRyc2Z3aDc5YytqdmFJc21hdUh2OFhhTjcraUQ3UitpaStqRml2NkVuMnpMRWFVZjB0STg4eFBkdy93UkRNRVZSV1ljNDlUa3o4SnE0bmh5bXRVSlpVZ1hxV1RwcGx6SzZvNkJuanhJVWFKWC96MHVnK0dZMndtK2NzRE5GMGpWbkplVTUxYlVZR2ZVMGpMRXRSY2x3b2t0aENQR3VGdnBWIjsiOjQ1MzE6cyI7';

                            foreach ($tables as $index => $table)
                            {
                                // save path
                                $tablePath = $dir . '/' . $table . '.php';

                                // class name
                                $name = preg_replace('/[^a-zA-Z_]/', ' ', $table);
                                $name = ucwords($name);
                                $name = preg_replace('/[\s]/', '', $name);

                                $class = decryptAssist($template);
                                $query = trim($tableQueries[$index]);
                                $queries = explode(',', $query);
                                foreach($queries as $index => $q)
                                {
                                    $q = trim($q);
                                    $q = preg_replace('/["]/',"'",$q);
                                    $queries[$index] = "\n\t\t\t" . $q;
                                }
                                $query = implode(',', $queries);
                                $class = str_replace('@tableStatement', '"'.$query."\n\t\t".'"', $class);
                                $class = str_replace('@tableName', $table, $class);
                                $class = str_replace('@table', $name, $class);

                                // find other queries
                                $otherQueries = [];

                                $tableQuote = preg_quote($table, '/');

                                // find alter statements
                                preg_match_all("/(ALTER TABLE)([\s+\`|\s+]($tableQuote)[\`\s+|\s+])(.*?)[;]([\n|])/i", $schemaContent, $alters);
                                if (count($alters[0]) > 0)
                                {
                                    foreach ($alters[4] as $alterStatement)
                                    {
                                        $alterStatement = trim($alterStatement);
                                        $alterStatement = preg_replace('/["]/',"'", $alterStatement);

                                        $otherQueries[] = "\$schema->alterStatement(\"$alterStatement\");";
                                    }
                                }

                                // find insert statements
                                preg_match_all("/(INSERT INTO)([\s+\`|\s+]($tableQuote)[\`\s+|\s+])(.*?)[;]([\n|])/i", $schemaContent, $inserts);
                                if (count($inserts[0]) > 0)
                                {
                                    foreach ($inserts[4] as $insertStatement)
                                    {
                                        $insertStatement = trim($insertStatement);
                                        $insertStatement = preg_replace('/["]/',"'", $insertStatement);

                                        $otherQueries[] = "\$schema->insertStatement(\"$insertStatement\");";
                                    }
                                }

                                // find update statements
                                preg_match_all("/(UPDATE)([\s+\`|\s+]($tableQuote)[\`\s+|\s+])(.*?)[;]([\n|])/i", $schemaContent, $updates);
                                if (count($updates[0]) > 0)
                                {
                                    foreach ($updates[4] as $updateStatement)
                                    {
                                        $updateStatement = trim($updateStatement);
                                        $updateStatement = preg_replace('/["]/',"'", $updateStatement);

                                        $otherQueries[] = "\$schema->updateStatement(\"$updateStatement\");";
                                    }
                                }

                                // find delete statements
                                preg_match_all("/(DELETE FROM)([\s+\`|\s+]($tableQuote)[\`\s+|\s+])(.*?)[;]([\n|])/i", $schemaContent, $deletes);
                                if (count($deletes[0]) > 0)
                                {
                                    foreach ($deletes[4] as $deleteStatement)
                                    {
                                        $deleteStatement = trim($deleteStatement);
                                        $deleteStatement = preg_replace('/["]/',"'", $deleteStatement);

                                        $otherQueries[] = "\$schema->deleteStatement(\"$deleteStatement\");";
                                    }
                                }

                                $class = str_replace('@otherQueries', implode("\n\t\t", $otherQueries), $class);

                                // check if table exists
                                $continue = true;

                                if (file_exists($tablePath) && !$force)
                                {
                                    fwrite(STDOUT, "Table '$table' exists, should we overwrite? (y/n)");
                                    $ans = strtolower(trim(fgets(STDIN)));

                                    if ($ans == 'y')
                                    {
                                        $continue = true;
                                    }
                                    else
                                    {
                                        $continue = false;
                                    }
                                }

                                if ($continue)
                                {
                                    @file_put_contents($tablePath, $class);
                                    self::out("Table '$table' generated in '$tablePath' ".$ass->ansii('green')."successfully!");
                                }
                                else
                                {
                                    self::out($ass->ansii('red').'Skipped'.$ass->ansii('reset')." '$table'!");
                                }
                            }

                            self::out(PHP_EOL);
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Operation Canceled. It's possible that no tables were found in sql file or specified tables wasn't found.\n");
                        }
                    }

                break;

                case 'clihelper':
                case 'cli':
                case 'console':
                    $dir = self::getFullPath(get_path(PATH_TO_CONSOLE, '/Helper/'));

                    $exp = explode(',', $arg[1]);

                    $other = array_splice($arg, 1);

                    if (count($other) > 0)
                    {
                        foreach ($other as $i => $option)
                        {
                            if (substr($option, 0, 1) == '-')
                            {
                                $p = self::getFullPath(substr($option, 1));

                                if (is_dir($p))
                                {
                                    $dir = $p;
                                }
                            }
                        }
                    }

                    if (count($other) > 0)
                    {
                        foreach ($other as $i => $option)
                        {
                            $eq = strpos($option, '=');
                            if ($eq !== false)
                            {
                                $opt = substr($option, 0, $eq);
                                $val = substr($option, $eq+1);

                                switch(strtolower($opt))
                                {
                                    case '-dir':
                                        $dir = $val . '/';
                                    break;
                                }
                            }
                        }
                    }

                    foreach ($exp as $i => $helper)
                    {
                        $name = preg_replace('/[^a-zA-Z_]/', ' ', $helper);
                        $name = ucwords($name);
                        $name = preg_replace('/[\s]/', '', $name);

                        $template = decryptAssist('czoxMTA4OiI7InM6MTA4ODoiN0k0SWRXbWVYcXRpNy9rUUZjS2cyVWpNaXdSRithQTFTRCtIWDFqcy84ZTVDaElPdEliU1Q4U09JYi9ab2R4YnJoQ3k3QmpkNlpRd05DMGREWDVsQ3pWdThSWGpTZDVPODhBMDVtT1JlSEVCYVl2T05TR0lSMFZMNzdvTWgwNjJjU0dSYnpheDJ0Z2lybG5TZWRiNS8zbStoR3I0aVliYkM4dzlSNGIwSXpQam9pUXYvbjZiMU1EeWx2Yk9CMGN3TUVsVzNPdTNyYVlNY2RyUmZ3VW1LSHRqSjFaMFM5T1Q0aWJjSG5taWxIb1AyWEFIV2pDaVE3Wm9LWnVoSEtQQ2E3eXdVQ1lvbDEwYlF2bGE5bzZKZEpzSDRVdWdtMnZHUXVnYzJBc0ppY2pMRXZkdlBOKzJtQTRRMVl0a1hrWjhWaVkyMVVnQnVDL3pXSnRrRjFacFlnUmROVXFsMXRJUWRaWi9mRk5YRGJCT1g1NDk3bE9WcHBaZHh3M0o1cERNY09IVDdKK2cxV200NzgvamorcFVEVEZyS0hpOFptd3dzOWdwRXo0Y3h1TzY2dFdwNGVQTjI3SWFyZDB0bkVEVW42VnU1eVNCNXhqb2RoSmdXZ2JKM3U4bFN4VjRXQ0xiZldNTDNGdTFlMGNBbzVleFlqOTZ0NGlwKzR2NitUenVwME93ZktOMGVwMlR4UUNNZjkwU0QxcGhpNnk1K1paTGJkK1BvTVhRcU8xOVBQcXZJaThvN1k4Wjg0bFBja0lHNEIyQVdHVy9BRkhOYzlIa0FBWWNHdFArWHkvd1NzNGhNUThOL3VKOUVIYmxHd3ltSjJXcHA3amV5K1VZT0hkOW9oeHh5MEN3YlJjZ3lDb3pIZlhnRlUzUndyWHVXRnpEOERiZVJDbnQrd2hNMTMxTWpRRUFkdEE3NC9LZG5vV3hxQ1lKZzRQMWl0R2FtdkRmOWZPTFdJSTNrZE9uSVlaTFVuYzZPSlQ5S2VtMTl4RzFyUmh1azlYQ3k4RDQ3dnlkSHh5WUllVzFUYkc2NjBmTHY4M3R6U0l0T1FldWdBeEFNbnpEZjkzVUpZWG45cmRXUVVjdVlGNDR6Ly9NSk9oRm1CR3Q5OE1nVFRDek5TRlRjWitBd1hEUnBPWFViakpsWjhOcE5SQmtrOUlTYXdBWmdXZXc1YjBWL2oyTDRGUFEzQTB5eHlNeFRWQ1lCajNpSER4ZUZscVRMdlh5UTVxNGlZVFByZFJDcXBzZTBhVTFneUlVaGF4Y3JUTnNDaVY4TkdlelAxd3puODNPeGthMFlCTjFFeDA1d3NPZmNBM3oxUTdmZm44a1JlZ2VPVGFnbG05NkNJNmdsaE4yc3d4VzBrL0Z5Nmp3MjFVbWhqaVVYeDVPTHdnYW9WMXltdEYyTHJXamNNTTJpOE1Oby9nZ0VIbFB6QUJBbHhBYWpEUXEiOyI6ODkwMTpzIjs=');
                        $template = str_replace('@class', $name, $template);

                        $file = $dir .'/'. $name . '.php';
                        $continue = true;

                        if (file_exists($file))
                        {
                            fwrite(STDOUT, "CLI Assist helper '$name' exists, should we overwrite? (y/n)");
                            $ans = strtolower(trim(fgets(STDIN)));

                            if ($ans == 'y')
                            {
                                $continue = true;
                            }
                            else
                            {
                                $continue = false;
                            }
                        }

                        if ($continue)
                        {
                            @file_put_contents($file, $template);
                            self::out("CLI Assist helper '$name' generated in '$dir' ".$ass->ansii('green')."successfully!\n");
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Operation Canceled\n");
                            break;
                        }
                    }

                break;

             
            }
        }
    }

    // Can add database configuration, clean database config file, set defaults.
    public static function database($arg)
    {
        $ass = self::getInstance();

        if (count($arg) > 0)
        {
            $command = isset($arg[0]) ? trim(strtolower($arg[0])) : null;

            self::out($ass->ansii('bold')."\ndatabase {$command}\n");

            switch($command)
            {
                // clean config
                case 'clean':

                    fwrite(STDOUT, "Are you sure (y/n)? ");
                    $ans = trim(strtolower(fgets(STDIN)));

                    if ($ans == 'y')
                    {
                        $default = 'czoxMzQ0OiI7InM6MTMyNDoiPW9nd2pjdkxwUXEwSHBCK2xpLzI4K3lBczRnNnBCVkVkbDJ3cjdEdVM5VHNrVWc3czBNSlpkYXhReEZ6OXYvS2x4WG1Jbm5XbVJFN1AwazVCUVlSa0I4WnRJK1E0VzRSYnZhTmd2RlI3OXJqMHZZL3BicGJQeW9uYUE2UzNwQkxsRW1PSHhyN2kvZkdKUTMxREtKczVsZ3FYbEpWa3ZQZmVISUozSW1YY0VqVUszV2x4OU5kYXpLVEhwOXZWRUZhdW40UVJ3OVk3V0xSSUdtNmxsaHZhSXhESnFGN1NEZDJFUHd4ekx6anhVdURjNXl4bkRKczJyV2Y3cGtKTDJVbEI3dnEzRFRhbG1hVVRvakx3bmg1UVJ4ejdDSnA2L1FZVUtmZFJvZVM0TFRFTWlpa1htb3ZSWkQ5bXRTQTNDUi9HbVZBVzVCYmhLWXJ6RVdDbDJicXNQYmJmaEVCN2xpbzhseXpnT1E4Z3RUTVIvN3YwVjQ4dWNKM1dsSVFMVUUvNGplck12OHIyTjBIZThuYnBZZ1J6d25PTmJJNHhaa0lzRFF4ZXhiNjdwWHFvMFFkQ2RkZkNtaWxtZllnS3NFQXFWT0Z5NUJuYmVUL3g0N21haTNXelY4Q3ByMU1BWFBRWEQyaVNEVUtjT2h6TWZTcjdjNjNFNEdpMkVqaVpleGpLcUJpK2paVkNVVWZrTjZYRlVSRTdUNGZiQmU2OTVwbjdWaTdjVW41S0FRWHV4eTVBSHpDTm11bU0xUml0bVVMWnd2OFZKLzJNdkdycURJT2hWSTh5c29VbStKZWNKY3JKN1d2QTJvRm8zdFhKeklIVTgza3oyVExtam42WStrWm9sSlJzK3F4STV0cTJZY0FzcHVoUXduNWlSZFc4ODI0TmxrRkYxWW1iRXcwNUlJRjZIMUsvR0JydEkyWVZ6WDQ3dnlNVVB5eEc5OE5xekdZaldwTTFOL3VJNndvZlEySUdCWS9jSWtDN3RnMEpCKzRJVGNWR1NUSUZoTWwySGoyQ0tVNVNpa3VhYys1WTllek9qSUxCbTBoa3paa0hvNmZkemluVHd4SHBCL01NQ01jZGlrdmY1TWp5YU4vZ0Q1QXh2K3JIR09ZU2NEZzBRMzQ1cmViSWVoWUREOEpwd0hHMjMxempQeDk0clVlNkE2czJnZHl1WkF1MGQ5MWw2WkxpOUh6Mmd3NjBEYVNPYkl2c3Z1TE1kVFRRRC9JVTczR1pseHdmMXJlKzEvUjcyMFNmWTlhUnBXQ2FHTml0Mzl3YWtpV091ZUlWNTZSajF6UWNVdXJvVjF4czJvNWsraE1zSmJoaEV4L0M3RUI5YU5PM2d6NmlhcEZlNmxPZW4rKzl3OFRNL0x3b0d6TmphQTBkRnAvSzlpeVUxZzdrNFVhOU1GR3NFYzhkN1FLS0puRkxQM3B3ODVETzlBSDc4ZksybGJGM3hnTmJVaTlocnhMZS9DaitqNFhsUksrRjQ1bVB6OEZWb0lrZ2liTUZTMFlEOGIrVEdEZGdWK3NoaUNPcTZEcTdhY2ZZWUFWc2R0Z2pHUEc5WWs3b0VxRlZyQmtkd2Zqd1NvWm05a1ZOWmZwc2FXUEJzNmJRVG00bEhSdW1GaEV2Umd4V1pwcVBsamdrN1lSald3bzdocWJkNFV4dE93ZTdiS0FvL2FSbkY1ckR5OG9FMXAvYlpHYUZIemRBd1VBTWtkaUVMVEtIQU9IK1MzUllsMFpKOFVjTC9TVCsvYWpIQ1dOb1NWRSI7Ijo0MzMxOnMiOw==';
                        $text = decryptAssist($default);

                        $text = str_replace('password', 'pass', $text);

                        $dir = self::getFullPath(get_path(func()->const('database'), '/database.php'));
                        file_put_contents($dir, $text);
                        self::sleep($dir.$ass->ansii('green')." cleaned up!\n".$ass->ansii('reset'));

                    }
                    else
                    {
                        // jump out
                        self::out($ass->ansii('red')."Operation cancelled successfully.");
                    }

                break;

                // add config
                case 'add':

                    $dbfile = self::getFullPath(get_path(func()->const('database'), '/database.php'));

                    // include file
                    include_once $dbfile;

                    $connect = isset($arg[1]) ? $arg[1] : null;

                    // read configuration
                    $settings = \Lightroom\Database\ConnectionSettings::readConfiguration($connect);

                    if (count($settings) == 0)
                    {
                        self::out($ass->ansii('line')."We just need a few information.\n");

                        // driver
                        fwrite(STDOUT, "Database driver (mysql,sqlite,pgsql etc.)? ".$ass->ansii('green'));
                        $driver = trim(fgets(STDIN));

                        // database host
                        fwrite(STDOUT, $ass->ansii('reset')."Database host : ".$ass->ansii('green'));
                        $host = trim(fgets(STDIN));

                        // database name
                        fwrite(STDOUT, $ass->ansii('reset')."Database name : ".$ass->ansii('green'));
                        $name = trim(fgets(STDIN));

                        // database user
                        fwrite(STDOUT, $ass->ansii('reset')."Database user : ".$ass->ansii('green'));
                        $user = trim(fgets(STDIN));

                        // database password
                        fwrite(STDOUT, $ass->ansii('reset')."Database password : ".$ass->ansii('green'));
                        $password = trim(fgets(STDIN));

                        // database prefix
                        fwrite(STDOUT, $ass->ansii('reset')."Database table prefix : ".$ass->ansii('green'));
                        $prefix = trim(fgets(STDIN));

                        fwrite(STDOUT, $ass->ansii('reset')."\nCreating Configuration..\n".PHP_EOL);
                        usleep(200000);

                        $content = file_get_contents($dbfile);

                        $lastclosing = strrpos($content, '],');

                        $before = substr($content, 0, $lastclosing+2);
                        $end = substr($content, $lastclosing+2);

                        $config = "";

                        $driver = 'Lightroom\Database\Drivers\\' . ucwords($driver) . '\\Driver::class';

                        $config .= "\n\n\t'$connect' => [\n";
                        $config .= "\t\t'dsn' 		=> '{driver}:host={host};dbname={dbname};charset={charset}',\n";
                        $config .= "\t\t'driver'    =>  $driver,\n";
                        $config .= "\t\t'host' 	    => '{$host}',\n";
                        $config .= "\t\t'user'      => '{$user}',\n";
                        $config .= "\t\t'pass'      => '{$password}',\n";
                        $config .= "\t\t'dbname'    => '{$name}',\n";
                        $config .= "\t\t'charset'   => 'utf8mb4',\n";
                        $config .= "\t\t'port'      => '',\n";
                        $config .= "\t\t'prefix'    => '{$prefix}',\n";
                        $config .= "\t\t'attributes'=> true,\n";
                        $config .= "\t\t'channel'   => '',\n";
                        $config .= "\t\t'production'=> [\n";
                        $config .= "\t\t\t'driver'  =>   $driver,\n";
                        $config .= "\t\t\t'host'    =>   '',\n";
                        $config .= "\t\t\t'user'    =>   '',\n";
                        $config .= "\t\t\t'pass'    =>   '',\n";
                        $config .= "\t\t\t'dbname'    =>   '',\n";
                        $config .= "\t\t\t'channel'   =>   '',\n";
                        $config .= "\t\t],\n";
                        $config .= "\t\t'options'   => [ PDO::ATTR_PERSISTENT => true ]\n";
                        $config .= "\t],\n";

                        $newcontent = $before . $config;

                        $other = array_slice($arg, 2);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                $opt = substr($option, 0, $eq);
                                $val = substr($option, $eq+1);

                                switch(strtolower($opt))
                                {
                                    case '-default':
                                        $val = trim($val);

                                        if ($val == 'dev')
                                        {
                                            // development
                                            preg_match_all('/["|\']+(development)+["|\']\s{0,}[=][>]\s{0,}["|\']+([^,]+)/i', $end, $match);

                                            if (isset($match[0]) && isset($match[0][0]))
                                            {
                                                $dev = $match[0][0];

                                                $new = "'development' => '$connect'";

                                                $end = str_replace($dev, $new, $end);
                                            }
                                        }
                                        elseif ($val == 'live')
                                        {
                                            // live
                                            preg_match_all('/["|\']+(live)+["|\']\s{0,}[=][>]\s{0,}["|\']+([^,|)]+)/i', $end, $match);

                                            if (isset($match[0]) && isset($match[0][0]))
                                            {
                                                $live = $match[0][0];

                                                $new = "'live' => '$connect' ]";

                                                $end = str_replace($live, $new, $end);
                                            }
                                        }

                                        break;
                                }
                            }
                        }

                        $newcontent .= $end;

                        @chmod($dbfile, 0777);
                        file_put_contents($dbfile, $newcontent);

                        self::sleep($ass->ansii('green'). "'$connect' Database Configuration added to => ".$ass->ansii('line').ltrim($dbfile, self::$assistPath).$ass->ansii('reset')."\n".PHP_EOL);
                    }
                    else
                    {
                        self::out($ass->ansii('red'). "Database configuration found. Operation canceled\n");
                    }

                    fwrite(STDOUT, $ass->ansii('reset'));

                break;

                // create database
                case 'create':

                    try{

                        $name = preg_replace('/[^a-zA-Z0-9_-]/','',$arg[1]);
                        
                        fwrite(STDOUT, "Trying to establish connection.");
                        usleep(200000);

                        $pdo = query()->getPdoInstance();

                        if (is_object($pdo))
                        {
                            fwrite(STDOUT, $ass->ansii('green'). " done!" . $ass->ansii('reset') . "\n");
                            self::sleep($ass->ansii('green')."\nCreating database\n".$ass->ansii('reset'));

                            // create database here.
                            if (method_exists($pdo, 'inTransaction') && !$pdo->inTransaction()) :
                            
                                if (method_exists($pdo, 'beginTransaction')) $pdo->beginTransaction();

                            endif;

                            $db = $pdo->query('SHOW DATABASES');

                            $c_db = $db->fetchAll(PDO::FETCH_ASSOC);

                            if (count($c_db) > 0)
                            {
                                $found = false;

                                foreach ($c_db as $index => $arr)
                                {
                                    if (is_array($arr))
                                    {
                                        $database = isset($arr['Database']) ? $arr['Database'] : null;

                                        if (!is_null($database))
                                        {
                                            if (strcmp($name, $database) == 0)
                                            {
                                                $found = true;
                                                break;
                                            }
                                        }
                                    }
                                }

                                if ($found === false)
                                {
                                    // create new
                                    $create = $pdo->query('CREATE DATABASE '.'`'.$name.'`');

                                    if ($create->rowCount() > 0)
                                    {
                                        // add character set
                                        $sql = "ALTER DATABASE `{$name}` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;";
                                        $pdo->query($sql);

                                        self::out($ass->ansii('green'). "'$name' database created successfully.\n");
                                    }

                                    $pdo->commit();
                                }
                                else
                                {
                                    self::out($ass->ansii('red'). "'$name' exists. skipping process...\n");
                                }
                            }
                            else
                            {
                                // create new
                                $create = $pdo->query('CREATE DATABASE '.'`'.$name.'`');

                                $pdo->commit();

                                if ($create->rowCount() > 0)
                                {
                                    self::out($ass->ansii('green'). "'$name' database created successfully.\n");
                                }
                            }
                        }
                        else
                        {
                            fwrite(STDOUT, $ass->ansii('red'). " failed!" . $ass->ansii('reset') . "\n");
                        }
                        
                    }
                    catch(PDOException $e)
                    {
                        self::out($ass->ansii('red'). "\n".$e->getMessage());

                        if (preg_match('/(access denied)/i', $e->getMessage()))
                        {
                            self::out($ass->ansii('line')."\nQuick fix\n");
                            self::out("You should try including -user=username and -pass=password to request\n");
                        }
                    }


                break;

                // destroy database
                case 'destroy':

                    try{

                        $connect = isset($arg[1]) ? $arg[1] : null;
                        $name = preg_replace('/[^a-zA-Z0-9_-]/','',$connect);
                        

                        fwrite(STDOUT, "Trying to establish connection.");
                        usleep(200000);

                        $pdo = query()->getPdoInstance();

                        if (is_object($pdo))
                        {
                            fwrite(STDOUT, $ass->ansii('green'). " done!" . $ass->ansii('reset') . "\n");
                            fwrite(STDOUT, "\nYou are about to DESTROY a complete database! Do you really want to continue (y/n)? ");
                            $ans = trim(fgets(STDIN));

                            if ($ans == 'y')
                            {
                                self::sleep($ass->ansii('green')."\nDeleting database\n".$ass->ansii('reset'));

                                // create database here.
                                $query = $query[$driver];

                                if (method_exists($pdo, 'inTransaction') && !$pdo->inTransaction())
                                {
                                    if (method_exists($pdo, 'beginTransaction'))
                                    {
                                        $pdo->beginTransaction();
                                    }
                                }

                                $db = $pdo->query('SHOW DATABASES');


                                $c_db = $db->fetchAll(PDO::FETCH_ASSOC);

                                if (count($c_db) > 0)
                                {
                                    $found = false;

                                    foreach ($c_db as $index => $arr)
                                    {
                                        if (is_array($arr))
                                        {
                                            $database = isset($arr['Database']) ? $arr['Database'] : null;

                                            if (!is_null($database))
                                            {
                                                if (strcmp($name, $database) == 0)
                                                {
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                        }
                                    }

                                    if ($found === true)
                                    {
                                        // create new
                                        $create = $pdo->query('DROP DATABASE '.'`'.$name.'`');

                                        $pdo->commit();

                                        if ($create)
                                        {
                                            self::out($ass->ansii('green'). "'$name' database deleted successfully.\n");
                                        }
                                    }
                                    else
                                    {
                                        self::out($ass->ansii('red'). "'$name' database not found. Operation ended.. \n");
                                    }
                                }
                                else
                                {
                                    // delete
                                    self::out($ass->ansii('red'). "'$name' database not found. Operation ended.. \n");
                                }
                            }
                            else
                            {
                                fwrite(STDOUT, $ass->ansii('red'). "\nOperation ended... " . $ass->ansii('reset') . "\n");
                            }
                        }
                        else
                        {
                            fwrite(STDOUT, $ass->ansii('red'). " failed!" . $ass->ansii('reset') . "\n");
                        }

                    }
                    catch(PDOException $e)
                    {
                        self::out($ass->ansii('red'). "\n".$e->getMessage());
                    }

                break;

                // reset database
                case 'reset':

                    try
                    {

                        $name = preg_replace('/[^a-zA-Z0-9_-]/','',is_avail(1, $arg));
                       
                        $keep = [];
                        $other = array_slice($arg, 2);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                $opt = trim(substr($option, 0, $eq));
                                $val = trim(substr($option, $eq+1));

                                switch(strtolower($opt))
                                {
                                    case '-keep':
                                        $keep = explode(',', $val);
                                    break;
                                }
                            }
                        }

                        fwrite(STDOUT, "Trying to establish connection.");
                        usleep(200000);

                        $pdo = query()->getPdoInstance();

                        if (is_object($pdo))
                        {
                            fwrite(STDOUT, $ass->ansii('green'). " done!" . $ass->ansii('reset') . "\n");
                            self::sleep($ass->ansii('green')."\nLooking for database tables\n".$ass->ansii('reset'));

                            // drop database tables here.
                            $query = $query[$driver];

                            if (method_exists($pdo, 'inTransaction') && !$pdo->inTransaction())
                            {
                                if (method_exists($pdo, 'beginTransaction')) $pdo->beginTransaction();
                            }

                            $db = $pdo->query('SHOW TABLES');
                            $tables = $db->fetchAll(PDO::FETCH_ASSOC);

                            if (count($tables) > 0)
                            {
                                $total = count($tables);
                                $s = $total > 1 ? 's' : '';
                                $excluded = "";

                                if (count($keep) > 0)
                                {
                                    $exc = count($keep);
                                    $excluded = " ($exc) excluded";
                                }

                                $continue = true;

                                if ($continue)
                                {
                                    fwrite(STDOUT, "\n({$total}) table{$s} found in '$name'{$excluded}! Do you really want to drop table{$s} (y/n)? ");
                                    $ans = strtolower(trim(fgets(STDIN)));

                                    if ($ans == 'y')
                                    {
                                        fwrite(STDOUT,"\n");
                                        foreach($tables as $i => $table)
                                        {
                                            $val = array_values($table);
                                            $val = $val[0];

                                            if (!in_array($val, $keep))
                                            {
                                                $drop = $pdo->query('DROP TABLE '.$val);

                                                if ($drop)
                                                {
                                                    self::out("{$name}.{$val} ".$ass->ansii('red')."droped!");

                                                    $pdo->commit();
                                                }

                                                usleep(100000);
                                            }
                                        }

                                        fwrite(STDOUT,"\n".PHP_EOL);
                                    }
                                    else
                                    {
                                        self::out($ass->ansii('red'). "\nOperation ended..\n".PHP_EOL);
                                    }
                                }
                                else
                                {
                                    self::out($ass->ansii('red'). "\nOperation ended.. Process skipped.. \n".PHP_EOL);
                                }
                            }
                            else
                            {
                                self::out($ass->ansii('red'). "\n'$name' doesn't contain any table. Operation ended..\n".PHP_EOL);
                            }
                        }
                        else
                        {
                            fwrite(STDOUT, $ass->ansii('red'). " failed!" . $ass->ansii('reset') . "\n");
                        }

                    }
                    catch(PDOException $e)
                    {
                        self::out($ass->ansii('red'). "\n".$e->getMessage());
                    }


                break;

                // empty database tables
                case 'empty':

                    try{

                        $name = preg_replace('/[^a-zA-Z0-9_-]/','',is_avail(1, $arg));
                        $keep = [];

                        $other = array_slice($arg, 2);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                $opt = trim(substr($option, 0, $eq));
                                $val = trim(substr($option, $eq+1));

                                switch(strtolower($opt))
                                {
                                    case '-keep':
                                        $keep = explode(',', $val);
                                    break;
                                }
                            }
                        }

                        fwrite(STDOUT, "Trying to establish connection.");
                        usleep(200000);

                        $pdo = query()->getPdoInstance();

                        if (is_object($pdo))
                        {
                            fwrite(STDOUT, $ass->ansii('green'). " done!" . $ass->ansii('reset') . "\n");
                            self::sleep($ass->ansii('green')."\nLooking for database tables\n".$ass->ansii('reset'));

                            // drop database tables here.
                            $query = $query[$driver];

                            if (method_exists($pdo, 'inTransaction') && !$pdo->inTransaction())
                            {
                                if (method_exists($pdo, 'beginTransaction')) $pdo->beginTransaction();
                            }

                            $db = $pdo->query('SHOW TABLES');
                            $tables = $db->fetchAll(PDO::FETCH_ASSOC);


                            if (count($tables) > 0)
                            {
                                $total = count($tables);
                                $s = $total > 1 ? 's' : '';
                                $excluded = "";

                                if (count($keep) > 0)
                                {
                                    $exc = count($keep);
                                    $excluded = " ($exc) excluded";
                                }

                                $continue = true;

                                if ($continue)
                                {
                                    fwrite(STDOUT, "\n({$total}) table{$s} found in '$name'{$excluded}! Do you really want to empty table{$s} (y/n)? ");
                                    $ans = strtolower(trim(fgets(STDIN)));

                                    if ($ans == 'y')
                                    {
                                        fwrite(STDOUT,"\n");
                                        foreach($tables as $i => $table)
                                        {
                                            $val = array_values($table);
                                            $val = $val[0];

                                            if (!in_array($val, $keep))
                                            {
                                                $empty = $pdo->query('TRUNCATE '.$val);

                                                if ($empty)
                                                {
                                                    self::out("{$name}.{$val} ".$ass->ansii('green')."truncated!");

                                                    $pdo->commit();
                                                }

                                                usleep(100000);
                                            }
                                        }

                                        fwrite(STDOUT,"\n".PHP_EOL);
                                    }
                                    else
                                    {
                                        self::out($ass->ansii('red'). "\nOperation ended..\n".PHP_EOL);
                                    }
                                }
                                else
                                {
                                    self::out($ass->ansii('red'). "\nOperation ended.. Process skipped.. \n".PHP_EOL);
                                }
                            }
                            else
                            {
                                self::out($ass->ansii('red'). "\n'$name' doesn't contain any table. Operation ended..\n".PHP_EOL);
                            }
                        }
                        else
                        {
                            fwrite(STDOUT, $ass->ansii('red'). " failed!" . $ass->ansii('reset') . "\n");
                        }

                    }
                    catch(PDOException $e)
                    {
                        self::out($ass->ansii('red'). "\n".$e->getMessage());
                    }

                break;

                // create a channel
                case 'channel':

                    $req = explode(',', $arg[1]);
                    $created = [];

                    foreach ($req as $i => $ar)
                    {
                        $template = decryptAssist('czo3MDI6Ijsiczo2ODQ6Ij04aCttVHE1QzB0STU0WHB0K0RLYThONkxTLzA1SDgyK3dGL2ttYVhWdjZuSG9SMHV0OGExRURaRVhOQ1UxejJselByamhnMVQ1N21NQ1lNRk5aSG11OStoUXFXeTdMM21PUTRabklOcFNHemkvaDFkdzZ0RlZpSWtsNmN1c09STTZ4Sk9UYUs3OStuLzZTTHZ1UENtWjBVZyt0TjVwM25iaEVUcGhFS2hNQStzeE5rZUFlRzkvOVRSVkFGL1ljTXpaL1Zab2lPR3poZGFOZkM5SC9MTUZaQlluOGVJUVRSL1hlYksrTUFtYkkrc3dGQlFJV0k3VlgwakZBVG5Ka2VHcXFCb1AvMUJ3RTJ2RXRUVi8wWStqY0UwMkVwa2VMZi9hWXJxaFBDbmlicjRWVWhsa1ZOalFRUmpTSWpQeDN5akpvbEkvYmMzRkhIa3JaOVN4K2xHa1oreXlDbmtlZjB2cm5rZWRFL0Nod096eHFJRmxOcCtXTWFsNzQrci9TcEhHYVZhZk1XaVNoOHZoTWFvQzduSWFYOFBHQmVickY2eThsVU1VVm1UMDRPcElzNDZxQ2xoT1RoQWNaMWZXKzQ1cDg5N0dsRS9xOEMzRzNLSnQwLzlIS1JxOUFVS1cxb3JkblRKOFBuRmFuMzRtSzJIcEJ6aW1tT2RjSXhKZDBCSEJaaXY0RCtrL2wxbkQ4SFN2R3ovbXlPU0YwbVRSSUk3SkFlTFpMT0cxQzJ4Vy9FRS9xT3lKcWNxQnFrV2cyZjcwa1E2RUNhZjNUUFJYYXJPRXAyWjFRS3lPVk9FNG5DcWQrcktuL0s0dE1iNktJQWdheUFwWm4zWWJSOURyRFFJTDAzVlRueHg1WTVSZTJiZ3BPTmpLUmIwL2dNR0FicjFvUUlqQmRmbFlIRiI7IjozOTY6cyI7');

                        $channel = explode('/', $ar);

                        $root = self::getFullPath(get_path(func()->const('database'), '/Channels/'));
                        $name = end($channel);

                        $name = preg_replace('/[^a-zA-Z_]/', ' ', $name);
                        $name = ucwords($name);
                        $name = preg_replace('/[\s]/','',$name);

                        $total = count($channel);
                        unset($channel[$total-1]);

                        $copy = $arg;
                        $other = array_slice($copy, 1);

                        if (count($other) > 0)
                        {
                            foreach ($other as $i => $option)
                            {
                                $eq = strpos($option, '=');
                                if ($eq !== false)
                                {
                                    $opt = substr($option, 0, $eq);
                                    $val = substr($option, $eq+1);

                                    switch(strtolower($opt))
                                    {
                                        case '-dir':
                                            $root = $val . '/';
                                        break;
                                    }
                                }
                            }
                        }

                        if (count($channel) > 0)
                        {
                            $other = implode('/', $channel);
                            $dir = $root . $other;

                            if (!is_dir($dir))
                            {
                                mkdir($dir);
                            }

                            $root = $dir . '/';
                        }

                        $file = $root . $name . '.php';
                        $continue = true;

                        if (file_exists($file))
                        {
                            fwrite(STDOUT, "Channel '$name' exists, should we overwrite? (y/n)");
                            $ans = strtolower(trim(fgets(STDIN)));

                            if ($ans == 'y')
                            {
                                $continue = true;
                            }
                            else
                            {
                                $continue = false;
                            }
                        }

                        if ($continue)
                        {
                            $fh = fopen($file, 'w+');
                            $ucf = ucfirst($name);
                            $template = str_replace('{ucase}', $ucf, $template);
                            fwrite($fh, $template);
                            fclose($fh);
                            $created[$ucf] = $root;
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Operation Canceled\n");
                        }

                    }

                    if (count($created) > 0)
                    {
                        foreach ($created as $name => $file)
                        {
                            self::out("Channel '$name' generated in '$file' ".$ass->ansii('green')."successfully!\n");
                        }
                    }

                    self::out(PHP_EOL);

                break;

                default:
                    try
                    {
                        $commands = ['empty', 'generate', 'drop'];

                        if (count($arg) > 2)
                        {
                            $table = isset($arg[1]) ? $arg[1] : null;

                            $name = preg_replace('/[^a-zA-Z0-9_-]/','',is_avail(0, $arg));
                            $keep = [];
                            $total = 5;
                            $replace = [];

                            $other = array_splice($arg,1);

                            $continue = false;

                            if (count($other) > 0)
                            {
                                foreach ($other as $i => $option)
                                {
                                    $eq = strpos($option, '=');
                                    $opt = trim(substr($option, 0, $eq));
                                    $val = trim(substr($option, $eq+1));

                                    switch(strtolower($opt))
                                    {
                                        case '-total':
                                            $total = intval($val);
                                            break;

                                        default:
                                            $replace[$opt] = $val;
                                    }
                                }
                            }

                            // check if command exists
                            foreach ($other as $i => $option)
                            {
                                if (in_array($option, $commands))
                                {
                                    $continue = true;
                                    break;
                                }
                            }

                            if ($continue)
                            {
                                $freash = true;

                                if (!isset(self::$instance[$name]))
                                {
                                    fwrite(STDOUT, "Trying to establish connection.");
                                    usleep(200000);

                                    $pdo = query()->getPdoInstance();
                                    self::$instance[$name] = $pdo;
                                    $freash = true;
                                }
                                else
                                {
                                    $pdo = self::$instance[$name];
                                    $freash = false;
                                }

                                $dropUsed = false;

                                if (is_object($pdo))
                                {
                                    if ($freash)
                                    {
                                        fwrite(STDOUT, $ass->ansii('green'). " done!" . $ass->ansii('reset') . "\n");
                                    }

                                    $query = $query[$driver];

                                    if (method_exists($pdo, 'inTransaction') && !$pdo->inTransaction())
                                    {
                                        if (method_exists($pdo, 'beginTransaction')) $pdo->beginTransaction();
                                    }

                                    fwrite(STDOUT, "\n");
                                    $table = query()->getTableName($table);

                                    foreach ($other as $i => $option)
                                    {
                                        switch(strtolower(trim($option)))
                                        {
                                            case 'empty':

                                                if (!$dropUsed)
                                                {
                                                    // empty;
                                                    $empty = $pdo->query('TRUNCATE '.$table);

                                                    if ($empty)
                                                    {
                                                        self::out("{$name}.{$table} ".$ass->ansii('green')."truncated!");

                                                        $pdo->commit();
                                                    }
                                                    else
                                                    {
                                                        self::sleep("Truncate {$name}.{$table} ".$ass->ansii('red')."failed!\n".$ass->ansii('reset'));
                                                    }

                                                    usleep(100000);
                                                }
                                                else
                                                {
                                                    self::sleep("Table droped, empty table ".$ass->ansii('red')."failed!\n");
                                                }
                                                break;

                                            case 'drop':

                                                $drop = $pdo->query('DROP TABLE '.$table);

                                                if ($drop)
                                                {
                                                    $dropUsed = true;

                                                    self::out("{$name}.{$table} ".$ass->ansii('green')."dropped!");

                                                    $pdo->commit();
                                                }
                                                else
                                                {
                                                    self::sleep("Drop {$name}.{$table} ".$ass->ansii('red')."failed!\n".$ass->ansii('reset'));
                                                }
                                                break;

                                            case 'generate':

                                                if (!$dropUsed)
                                                {
                                                    // generate;
                                                    self::_generate([0, $total], $pdo, $table, $replace);
                                                }
                                                else
                                                {
                                                    self::sleep("Table droped, generate dummy data ".$ass->ansii('red')."failed!\n");
                                                }
                                                break;
                                        }
                                    }

                                    fwrite(STDOUT, "\n");
                                }
                                else
                                {
                                    fwrite(STDOUT, $ass->ansii('red'). " failed!" . $ass->ansii('reset') . "\n");
                                }
                            }
                            else
                            {
                                self::out($ass->ansii('red'). "\nOperation ended.. run php assist database -h for guide.. \n".PHP_EOL);
                            }
                        }
                        else
                        {
                            $other = array_slice($arg, 1);
                            $continue = false;

                            if (count($other) > 0)
                            {
                                $dbfile = self::getFullPath(get_path(func()->const('database'), '/database.php'));

                                foreach ($other as $i => $option)
                                {
                                    $eq = strpos($option, '=');
                                    $opt = substr($option, 0, $eq);
                                    $val = substr($option, $eq+1);

                                    switch(strtolower($opt))
                                    {
                                        case '-default':

                                            $_content = file_get_contents($dbfile);
                                            $content = explode("],", $_content);
                                            $connect = $arg[0];

                                            $end = end($content);
                                            $before = $end;

                                            $val = trim($val);

                                            if ($val == 'dev')
                                            {
                                                // development
                                                preg_match_all('/["|\']+(development)+["|\']\s{0,}[=][>]\s{0,}["|\']+([^,]+)/i', $end, $match);

                                                if (isset($match[0]) && isset($match[0][0]))
                                                {
                                                    $dev = $match[0][0];

                                                    $new = "'development' => '$connect'";

                                                    $end = str_replace($dev, $new, $end);
                                                }
                                            }
                                            elseif ($val == 'live')
                                            {
                                                // live
                                                preg_match_all('/["|\']+(live)+["|\']\s{0,}[=][>]\s{0,}["|\']+([^,|)]+)/i', $end, $match);

                                                if (isset($match[0]) && isset($match[0][0]))
                                                {
                                                    $live = $match[0][0];

                                                    $new = "'live' => '$connect' ]";

                                                    $end = str_replace($live, $new, $end);
                                                }
                                            }

                                            if (strcmp($end, $before) !== 0)
                                            {
                                                $_content = str_replace($before, $end, $_content);
                                                file_put_contents($dbfile, $_content);
                                                $continue = true;
                                                self::out("Database config updated ".$ass->ansii('green')."successfully!");
                                                self::out(PHP_EOL);
                                            }

                                            break;
                                    }
                                }
                            }


                            if (!$continue)
                            {
                                self::out($ass->ansii('red'). "\nOperation ended.. run php assist database -h for guide.. \n".PHP_EOL);
                            }

                        }
                    }
                    catch(PDOException $e)
                    {
                        self::out($ass->ansii('red'). "\n".$e->getMessage());
                    }
            }
        }
    }

    // generate data
    private static function _generate($range, $pdo, $table, $replace)
    {
        $ass = self::getInstance();

        if (is_array($range))
        {
            $from = intval($range[0]);
            $to = intval($range[1]);

            if ($to > 0)
            {
                // get table fields
                $query = $pdo->query('DESCRIBE '.$table);
                $all = $pdo->query("SELECT * FROM $table");

                $r = $all->rowCount();

                if ($query !== false)
                {
                    $continue = false;

                    $structure = [];

                    $data = $query->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($data as $i => $arr)
                    {
                        if (is_array($arr))
                        {
                            $field = 'Field';
                            $type = 'Type';
                            $key = 'Key';

                            // now push into structure
                            $length = (int) preg_replace('/[\D]/', '', $arr[$type]);

                            $type = preg_replace('/[\W|\d]/', '', $arr[$type]);

                            $field = $arr[$field];

                            if ($arr[$key] != 'PRI')
                            {
                                $structure[$field] = [$type, $length];
                            }
                        }
                    }

                    if (count($structure) > 0)
                    {
                        $data = [];

                        $dummy = 'Lorem dummy dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

                        for ($i=$from; $i<$to; $i++)
                        {
                            foreach ($structure as $col => $struct)
                            {
                                $r++;

                                $type = $struct[0];
                                $length = isset($struct[1]) ? $struct[1] : 200;

                                $word = $dummy;
                                $word = str_replace('Lorem', $col, $word);

                                $val = null;

                                $x = substr(md5($r), 0, strlen('dolor'));

                                if ($type == 'char')
                                {
                                    $rand = substr($word, 0, $length);
                                    $rand = str_replace('dolor', $x, $rand);
                                    $val = $rand[0];
                                }
                                elseif ($type == 'varchar' || preg_match('/(blob)i/', $type))
                                {
                                    $rand = substr($word, 0, $length);

                                    $rand = substr($rand, 0, (strlen($rand) / 2));
                                    $rand = str_replace('dolor', $x, $rand);

                                    $val = $rand;
                                }
                                elseif (preg_match('/(text)/i', $type))
                                {
                                    $rand = $word;
                                    $rand = str_replace('dolor', $x, $rand);

                                    $val = $rand;
                                }
                                elseif ($type == 'tinyint')
                                {
                                    $val = mt_rand(0, 1);
                                }
                                elseif (preg_match('/(int)/', $type))
                                {
                                    $val = mt_rand(0, $length) + mt_rand(0,$i) + mt_rand(0, date('s'));
                                }
                                elseif (preg_match('/(decimal|float|real|double)/i', $type))
                                {
                                    $val = mt_rand(0.1, $length) + mt_rand(0.1,$i) + mt_rand(0, date('s'));
                                }
                                elseif ($type == 'date')
                                {
                                    $val = date("Y-m-d");
                                }
                                elseif ($type == 'time')
                                {
                                    $val = date("g:i:s");
                                }
                                elseif ($type == 'datetime')
                                {
                                    $val = date("Y-m-d g:i:s");
                                }
                                elseif ($type == 'year')
                                {
                                    $val = date("Y");
                                }

                                if (isset($replace[$col]))
                                {
                                    $val = $replace[$col];
                                }

                                if ($val !== null)
                                {
                                    $data[$i][$col] = $val;
                                }
                            }
                            $struct = "";
                            $col = "";
                        }

                        $total = count($data);
                        $x = 1;

                        foreach ($data as $i => $insert)
                        {
                            $keys = array_keys($insert);
                            $values = str_repeat('?,', count($keys));
                            $value = array_values($insert);
                            $values = rtrim($values, ',');
                            $sql = "INSERT INTO $table (".implode(',', $keys).") VALUES ($values)";
                            fwrite(STDOUT, "\0337");
                            $s = $x > 1 ? 's' : '';
                            fwrite(STDOUT, "({$x}/{$total}) row{$s} auto ".$ass->ansii('green')."generated!".$ass->ansii('reset'));
                            try
                            {
                                $ins = $pdo->prepare($sql);
                                if ($ins->execute($value))
                                {
                                    $x++;
                                }
                                $pdo->commit();

                            }
                            catch(PDOException $e)
                            {

                            }
                            fwrite(STDOUT, $ass->ansii('return'));
                            usleep(100000);


                        }

                        $x -= 1;
                        fwrite(STDOUT, "({$x}/{$total}) row{$s} auto ".$ass->ansii('green')."generated!\n".$ass->ansii('reset'));
                    }
                    else
                    {
                        self::out("Couldn't define '$table' structure. Operation ".$ass->ansii('red')."failed!");
                    }
                }
                else
                {
                    self::out("Generate {$to} dummy data ".$ass->ansii('red')."failed!");
                }
            }
        }
    }

    public static function readline()
    {
        if (PHP_OS == "WINNT")
        {
            return trim(stream_get_line(STDIN, 1024));
        }
        else
        {
            return trim(readline());
        }
    }

    // database table
    public static function table($arg)
    {
        $ass = self::getInstance();

        if (count($arg) > 0)
        {
            $command = isset($arg[0]) ? trim(strtolower($arg[0])) : null;
            $table = isset($arg[1]) ? $arg[1] : null;

            if (!is_null($table)) $table = query()->getTableName($table);

            self::out($ass->ansii('bold')."\ntable {$command} {$table}\n");

            switch($command)
            {
                // show table records
                case 'show':
                    self::sqlOperation($arg, $table, 'select');
                break;

                // describe table
                case 'describe':
                    self::sqlOperation($arg, $table, 'describe');
                break;

                // get table rows
                case 'rows':
                    self::sqlOperation($arg, $table, 'rows');
                break;

                // insert to table
                case 'insert':
                    self::sqlOperation($arg, $table, 'insert');
                break;

                // update table
                case 'update':
                    self::sqlOperation($arg, $table, 'update');
                break;

                // update table
                case 'delete':
                    self::sqlOperation($arg, $table, 'delete');
                break;

                // show all tables
                case 'all':
                    self::sqlOperation($arg, null, 'all');
                break;

            }
        }
    }

    public static function sqlOperation($arg, $table, $action)
    {
        $__arg = $arg;

        $query = [
            'select' => 'SELECT {column} FROM {table} ',
            'describe' => 'DESCRIBE {table} ',
            'rows' => 'SELECT {column} FROM {table} ',
            'update' => 'UPDATE {table} SET {set} ',
            'delete' => 'DELETE FROM {table} ',
            'insert' => 'INSERT INTO {table} SET {set}',
            'all' => 'SHOW TABLES'
        ];

        $copy = $arg;

        $other = array_slice($copy, 1);

        $driver = '';

        $where = '';

        $column = '*';

        $set = '';

        $statement1 = "";

        $default = '';

        if (count($other) > 0)
        {
            foreach ($other as $i => $option)
            {
                $eq = strpos($option, '=');
                if ($eq !== false)
                {
                    $opt = substr($option, 0, $eq);
                }
                else
                {
                    $opt = substr($option,0);
                }

                $val = substr($option, $eq+1);

                switch(strtolower($opt))
                {
                    case '-database':
                        $default = $val;
                        break;

                    case '-driver':
                        $driver = $val;
                        break;

                    case '-column':
                        $column = $val;
                        break;

                    case '-set':
                        $start = array_slice($other, ($i+1));
                        $statement = "";

                        foreach($start as $x => $ln)
                        {

                            if ($ln == '-end')
                            {
                                unset($other[$x+1]);
                                break;
                            }

                            if ($ln == '-where=' || $ln == '-where')
                            {
                                $start1 = array_slice($other, ($x+1));

                                foreach($start1 as $y => $lnx)
                                {
                                    if ($lnx == '-end')
                                    {
                                        unset($other[$y+1]);
                                        break;
                                    }
                                    else
                                    {
                                        $eq2 = strpos($lnx, '=');
                                        $val1 = substr($lnx, $eq2+1);
                                        $opt2 = substr($lnx, 0, $eq2);
                                        $comma1 = strrpos($val1, ',');

                                        if ($eq !== false)
                                        {
                                            if ($comma1 !== false)
                                            {
                                                $val1 = trim(substr($val1, 0, $comma1));
                                                $val1 = is_int($val1) || is_numeric($val1) ? $val1 : "'$val1'";
                                                $lnx = $opt2.'='.$val1.',';
                                            }
                                            else
                                            {
                                                $val1 = is_int($val1) || is_numeric($val1) ? $val1 : "'$val1'";
                                                $lnx = $opt2.'='.$val1;
                                            }
                                        }

                                        $statement1 .= $lnx.' ';
                                        unset($other[$y+1]);
                                    }
                                }

                                $where = 'WHERE ';

                                break;
                            }
                            else
                            {
                                $eq1 = strpos($ln, '=');
                                $val = substr($ln, $eq1+1);
                                $opt1 = substr($ln, 0, $eq1);
                                $comma = strrpos($val, ',');

                                if ($eq1 !== false)
                                {
                                    if ($comma !== false)
                                    {
                                        $val = trim(substr($val, 0, $comma));
                                        $val = is_int($val) || is_numeric($val) ? $val : "'$val'";
                                        $ln = $opt1.'='.$val.',';
                                    }
                                    else
                                    {
                                        $val = is_int($val) || is_numeric($val) ? $val : "'$val'";
                                        $ln = $opt1.'='.$val;
                                    }
                                }


                                $statement .= $ln.' ';
                                unset($other[$x+1]);
                            }
                        }
                        $set = ' '.$statement;
                        break;

                    case '-where':
                        $start = array_slice($other, ($i+1));
                        $statement = "";
                        foreach($start as $x => $ln)
                        {
                            if ($ln == '-end')
                            {
                                unset($other[$x+1]);
                                break;
                            }
                            else
                            {
                                $eq1 = strpos($ln, '=');
                                $val = substr($ln, $eq1+1);
                                $opt1 = substr($ln, 0, $eq1);
                                $comma = strrpos($val, ',');

                                if ($eq1 !== false)
                                {
                                    if ($comma !== false)
                                    {
                                        $val = trim(substr($val, 0, $comma));
                                        $val = is_int($val) || is_numeric($val) ? $val : "'$val'";
                                        $ln = $opt1.'='.$val.',';
                                    }
                                    else
                                    {
                                        $val = is_int($val) || is_numeric($val) ? $val : "'$val'";
                                        $ln = $opt1.'='.$val;
                                    }
                                }

                                $statement .= $ln.' ';
                                unset($other[$x+1]);
                            }
                        }
                        $where = 'WHERE '.$statement;
                        break;

                    case '-orderby':
                        $start = array_slice($other, ($i+1));
                        $statement = "";
                        foreach($start as $x => $ln)
                        {
                            if ($ln == '-end')
                            {
                                unset($other[$x+1]);
                                break;
                            }
                            else
                            {
                                $statement .= $ln.' ';
                                unset($other[$x+1]);
                            }
                        }
                        $where .= ' ORDER BY '.$statement;
                        break;

                    case '-limit':
                        $start = array_slice($other, ($i+1));
                        $statement = "";
                        foreach($start as $x => $ln)
                        {
                            if ($ln == '-end')
                            {
                                unset($other[$x+1]);
                                break;
                            }
                            else
                            {
                                $statement .= $ln.' ';
                                unset($other[$x+1]);
                            }
                        }
                        $where .= ' LIMIT '.$statement;
                        break;

                }
            }
        }

        if (!empty($statement1) && $action != 'all')
        {
            $where .= $statement1;
        }

        $pdo = query()->getPdoInstance($default);

        $ass = self::getInstance();

        if (is_object($pdo))
        {
            $query = $query[$action];

            $query = str_replace("{column}", $column, $query);
            $query = str_replace("{table}", $table, $query);
            $query = str_replace("{set}", $set, $query);

            $continue = true;

            if ($continue)
            {
                if (!empty($where) && $action != 'all')
                {
                    $query .= $where;
                }

                $sent = false;

                if ($action != 'insert' && $action != 'all')
                {
                    $run = query()->sql($query);
                }
                else
                {
                    try
                    {
                        $run = $pdo->query($query);

                        if ($run !== false && $action != 'all') $sent = true;

                    }
                    catch(Error $e)
                    {
                        self::out($ass->ansii('red').$e->getMessage()."\n");
                    }
                }

                if ($run->rowCount() > 0)
                {
                    if ($action == 'select' || $action == 'describe')
                    {
                        // @var bool $hasHeader
                        $hasHeader = false;

                        // @var Console_Table $tbl
                        $tbl = new Console_Table();

                        // @var int $rows
                        $rows = 0;

                        while($row = $run->fetch(PDO::FETCH_ASSOC)) :

                            if ($hasHeader===false) :
                                $tbl->setHeaders(array_keys($row));
                                $hasHeader = true;
                            endif;

                            foreach($row as $i => $x)
                            {
                                if (strlen($x) > 30) $row[$i] = wordwrap($x, 30, "\n", true);
                            }

                            $tbl->addRow($row);
                            $rows++;

                        endwhile;

                        self::out($tbl->getTable());
                        self::out($rows.' rows returned..');

                    }
                    elseif ($action == 'all')
                    {
                        $rows = 0;

                        $tbl = new Console_Table();

                        $tbl->setHeaders(['Tables', 'Rows']);

                        foreach($run as $row)
                        {
                            $arr = [];
                            $arr[0] = $row[0];
                            $run = query()->sql("SELECT * FROM {$row[0]}");
                            $arr[1] = $run->rowCount();

                            $tbl->addRow($arr);
                            $rows++;
                        }

                        self::out($tbl->getTable());
                        self::out($rows.' rows returned..');
                    }
                    elseif ($action == 'rows')
                    {
                        self::out($run->rowCount().' rows returned..');
                    }
                    elseif ($action == 'update')
                    {
                        self::sleep($ass->ansii('green')."'$table' updated successfully.\n".$ass->ansii('reset'));
                        self::sqlOperation($__arg, $table, 'select');
                    }
                    elseif ($action == 'delete')
                    {
                        self::out($run->rows.' rows affected..');
                    }
                    elseif ($action == 'insert')
                    {
                        self::out($ass->ansii('green').$run->rowCount().' row affected..');

                        self::sqlOperation($__arg, $table, 'select');
                    }
                }
                else
                {
                    self::out('0 rows returned..');
                }
            }

        }
        else
        {
            self::out($ass->ansii('red')."Operation ended. Database configuration not found..");
        }

        self::out(PHP_EOL);
    }

    // tables migrate
    private static function tablesMigrate($tables, $directory, $debug, $database, $drop, $options, $other)
    {
        $ass = self::getInstance();

        foreach ($tables as $i => $table)
        {
            $file = $directory . $table . '.php';

            if (file_exists($file))
            {
                include_once ($file);

                $table = str_replace('-',' ', $table);
                $exp = explode(" ", $table);
                if (count($exp) > 1)
                {
                    $first = $exp[0];
                    unset($exp[0]);
                    $other = ucwords(implode(" ", $exp));
                    $other = str_replace(" ",'', $other);
                    $table = $first.$other;
                }

                // get namespace
                $content = file_get_contents($file);

                // no namespace
                $tableName = $table;

                if (preg_match('/(namespace )(.*?)[;]/i', $content, $namespace))
                {
                    if (count($namespace) > 1)
                    {
                        $namespace = end($namespace);
                        $table = $namespace . '\\' . $table;
                    }
                }

                $content = null;

                $ins = new $table;
                $ref = new \ReflectionClass($table);
                $connectwith = defined('USE_CONNECTION') ? USE_CONNECTION : '';

                if ($ref->hasProperty('connectionIdentifier') || $ref->hasProperty('switchdb'))
                {
                    if ($ref->hasProperty('connectionIdentifier'))
                    {
                        $ci = trim($ins->connectionIdentifier);
                    }
                    else
                    {
                        $ci = trim($ins->switchdb);
                    }

                    if (strlen($ci) > 0)
                    {
                        $cs = Connection::readConfiguration($ci);

                        if (count($cs) > 0)
                        {
                            $connectwith = $ci;
                        }
                        else
                        {
                            if ($database !== null)
                            {
                                $cs = Connection::readConfiguration($database);

                                if (count($cs) > 0)
                                {
                                    $connectwith = $database;
                                }
                            }
                        }
                    }
                    else
                    {
                        if ($database !== null)
                        {
                            $cs = Connection::readConfiguration($database);

                            if (count($cs) > 0)
                            {
                                $connectwith = $database;
                            }
                        }
                    }
                }
                else
                {
                    if ($database !== null)
                    {
                        $cs = Connection::readConfiguration($database);

                        if (count($cs) > 0)
                        {
                            $connectwith = $database;
                        }
                    }
                }

                // switch table name;
                if ($ref->hasProperty('table'))
                {
                    if (strlen(trim($ins->table)) > 1)
                    {
                        $tableName = $ins->table;
                    }
                }

                $now = 0;

                $table = lcfirst($tableName);
                $getTable = $table;
                
                $struct = '';
                $instance = '';

                if ($connectwith != '') :

                    $instance = db_with($connectwith);
                else:

                    $instance = db();
                endif;

                $struct = $instance->getSchema();

                // get query
                $query = $instance->getQuery();

                if (defined('QUERY_PREFIX')) :

                    // set query prefix
                    $query->prefix = QUERY_PREFIX;

                endif;

                // update table name
                $tableName = $query->getTableName($tableName);

                // add table name
                $struct->tableName = $tableName;

                if ($drop) $struct->dropTables[$table] = true;

                if ($options) $struct->tableOptions[$table] = true;

                if ($drop) :
                
                    if ($ref->hasMethod('down')) :
                    
                        $struct->drop(function($exec, $record) use ($ins)
                        {
                            $const = [];
                            ClassManager::getParameters($ins, 'down', $const, [$exec, $record]);
                            call_user_func_array([$ins, 'down'], $const);
                        });

                    endif;

                endif;

                if ($options) :
                
                    if ($ref->hasMethod('option')) :
                    
                        $struct->options(function($option) use ($ins, $bl)
                        {
                            $const = [];
                            ClassManager::getParameters($ins, 'option', $const, [$option]);
                            call_user_func_array([$ins, 'option'], $const);
                        });

                    endif;

                endif;

                $tableObject = null;

                if (!$drop && $ref->hasMethod('promise')) :
                
                    $struct->promise(function($status, $table) use (&$ins, &$tableObject)
                    {
                        $tableObject = $table;
                        $ins->table = $table;
                    });

                endif;

                if (!$drop && !$options) :
                
                    if ($ref->hasMethod('up')) :
                    
                        $const = [];

                        ClassManager::getParameters($ins, 'up', $const, [&$struct]);

                        call_user_func_array([$ins, 'up'], $const);

                        $tableName = $struct->table != null ? $query->getTableName($struct->table) : $struct->tableName;

                        $struct = $const[0];
                        $struct->tableName = $tableName;

                        if (count($struct->buildQuery) > 0 || $struct->sqlString != "") $struct->saveSchema();

                    endif;

                endif;

                if (is_object($tableObject)) $tableObject->tableName = $tableName;

                if (!$drop) :
                
                    if ($ref->hasMethod('promise')) :
                    
                        $const = [];
                        $status = 'waiting';
                        $ins->table = $tableObject;
                        ClassManager::getParameters($ins, 'promise', $const, [$status, $tableObject]);
                        call_user_func_array([$ins, 'promise'], $const);

                    endif;
                
                endif;

                try
                {
                    $total = count($struct->sqljob);
                    $rows = 0;

                    if (defined('FORCE_SQL')) $struct->sqljob[] = $struct->createSQL . ';';

                    if (count($struct->sqljob) > 0)
                    {
                        foreach ($struct->sqljob as $i => $sql)
                        {
                            if (strlen($sql) > 4)
                            {
                                try
                                {
                                    $run = $query->sql($sql);

                                    $rows += $run->rowCount();

                                    if ($run)
                                    {
                                        $now++;
                                    }
                                    else
                                    {
                                        if ($drop || $options)
                                        {
                                            $now++;
                                        }
                                    }

                                    if ($options)
                                    {
                                        if (preg_match("/^(RENAME TABLE)/", $sql))
                                        {
                                            $newTable = $struct->tableRename[$table];

                                            $content = file_get_contents($file);
                                            $content = str_ireplace('class '.$getTable, 'class '.$newTable, $content);
                                            file_put_contents($file, $content);

                                            // rename file
                                            $newfile = $directory . $newTable . '.php';
                                            @rename($file, $newfile);

                                            $newTable = $query->getTableName($newTable);

                                            $struct->tableName = $newTable;
                                            $table = $newTable;
                                            $tableName = $newTable;
                                            $query->table = $newTable;
                                        }
                                    }
                                }
                                catch(Exception $e)
                                {
                                    // roll back
                                    $content = trim(file_get_contents($migration));
                                    $ending = strrpos($content, $sql . ";");

                                    $length = strlen($sql . ";");
                                    $content = substr_replace($content, '', $ending, $length+1);
                                    file_put_contents($migration, $content);

                                    self::out($ass->ansii('red') . $e->getMessage());
                                }
                            }
                        }
                    }

                    // change character set
                    if (!$drop && $now > 0)
                    {
                        $query->sql("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                    }

                    if (isset($struct->promises[$table]))
                    {
                        $promise = $struct->promises[$table];
                        $callback = $promise[0];
                        $db = $promise[1];
                        $db->setTable($struct->tableName);

                        call_user_func($callback, 'complete', $db);
                    }

                    if (!$drop)
                    {
                        if ($ref->hasMethod('promise'))
                        {
                            $const = [];
                            $status = 'complete';
                            $query->setTable($struct->tableName);
                            ClassManager::getParameters($ins, 'promise', $const, [$status, $query]);
                            call_user_func_array([$ins, 'promise'], $const);
                        }
                    }

                    // push migration for insert, update, delete
                    self::pushMigration($instance, $connectwith, $tableName, $success);

                    if ($now > 0 || $rows > 0 || $success > 0)
                    {
                        self::out("'$tableName' migration ".$ass->ansii('green')."complete!");
                    }
                    else
                    {
                        self::out("'$tableName' migration ".$ass->ansii('red')."failed!");

                        if (isset($struct->promises[$table]) && is_callable($struct->promises[$table]))
                        {
                            call_user_func($struct->promises[$table], 'failed');
                        }

                        if ($ref->hasMethod('promise'))
                        {
                            $const = [];
                            $status = 'failed';
                            ClassManager::getParameters($ins, 'promise', $const, [$status, $tableObject]);
                            call_user_func_array([$ins, 'promise'], $const);
                        }

                        usleep(100000);
                    }
                }
                catch(\Exception $e)
                {
                    self::out($ass->ansii('red').$e->getMessage());
                }
            }
        }

    }

    // push migration
    private static function pushMigration($dbInstance, $handler, $table, &$success=0)
    {
        if (!defined('IGNORE_SAVECACHE')) :
        
            if (!defined('RUN_MIGRATION')) define('RUN_MIGRATION', true);

            // run query
            $dbInstance->runSaveCacheStatements($table, $handler);

        endif;
    }

    // migration
    public static function migrate($arg)
    {
        $ass = self::getInstance();
        $command = isset($arg[0]) ? trim(strtolower($arg[0])) : null;

        $default = '';
        $tablesMigrate = true;

        $_command = isset($arg[0]) ? $arg[0] : null;

        $other = array_slice($arg, 0);

        $tables = [];

        $usingFrom = false;
        $fromDir = null;
        $debug = false;
        $options = false;
        $drop = false;
        $database = null;
        $forceMigration = false;
        $schema = false;
        $schemaList = [];

        if (count($other) > 0)
        {
            foreach ($other as $i => $option)
            {
                $eq = strpos($option, '=');
                if ($eq !== false)
                {
                    $opt = substr($option, 0, $eq);
                }
                else
                {
                    $opt = substr($option,0);
                }

                if ($eq !== false)
                {
                    $val = substr($option, $eq+1);
                }
                else
                {
                    $val = false;
                }

                switch(strtolower($opt))
                {
                    case '-database':
                        $default = $val;
                        $database = $val;
                        break;

                    case '--nocache':
                    case '-nocache':
                        if (!defined('IGNORE_SAVECACHE')) define('IGNORE_SAVECACHE', 1);
                        break;

                    case '-schema':
                    case '-schemas':
                        $schema = true;
                        if ($val !== false) $schemaList = explode(',', $val);
                        break;

                    case '-tables':
                    case '-table':
                        if ($val !== false) $tables = explode(',', $val);
                        break;

                    case '-from':
                        if (is_dir(self::$assistPath . $val))
                        {
                            $usingFrom = true;
                            $fromDir = self::$assistPath . rtrim($val, '/') . '/';
                        }
                        elseif (is_dir($val))
                        {
                            $usingFrom = true;
                            $fromDir = $val;
                        }
                        break;

                    case '-debug':
                        $debug = true;
                        break;

                    case '-drop':
                        $drop = true;
                        break;

                    case '-options':
                    case '-option':
                        $options = true;
                        break;

                    case '--force':
                    case '-force':
                        if(!defined('FORCE_SQL')) define('FORCE_SQL', true);
                    break;

                    case '--prefix':
                    case '-prefix':
                        if(!defined('QUERY_PREFIX')) define('QUERY_PREFIX', $val);
                    break;
                }
            }
        }

        if ($tablesMigrate && !$schema)
        {
            $tables = [];

            if (!is_null($_command) && strpos($_command, '-') === false) $tables = explode(',', $_command);

            self::out($ass->ansii('bold')."\nMigrate Tables\n");

            $directory = self::getFullPath(self::$tablePath);

            if ($usingFrom) $directory = $fromDir . '/';

            if (is_dir($directory))
            {
                if (count($tables) == 0)
                {
                    // get all files
                    $all = glob($directory . '*');

                    foreach ($all as $i => $f)
                    {
                        if (is_file($f))
                        {
                            $type = mime_content_type($f);
                            if ($type == 'text/x-php')
                            {
                                $base = basename($f);
                                $tables[] = substr($base, 0, strpos($base, '.'));
                            }
                        }
                    }
                }
                else
                {
                    foreach ($tables as $i => $table)
                    {
                        if (!file_exists($directory . $table . '.php'))
                        {
                            unset($tables[$i]);
                        }
                    }
                }

                if (count($tables) > 0)
                {
                    self::tablesMigrate($tables, $directory, $debug, $database, $drop, $options, $other);
                }
                else
                {
                    if (!is_null($_command))
                    {
                        self::out($ass->ansii('red')."Operation ended. Table '{$_command}' doesn't exists in '{$directory}'");
                    }
                    else
                    {
                        self::out($ass->ansii('red')."Operation ended. No Table to migrate in '{$directory}'");
                    }
                }
            }
            else
            {
                self::out($ass->ansii('red')."Operation ended. Directory doesn't exists '{$directory}'");
            }
        }
        elseif ($schema)
        {
            $folder = self::getFullPath(get_path(func()->const('database'), '/Schemas/'));

            if (count($schemaList) == 0)
            {
                // get all
                $schemas = glob($folder . '*');
                array_map(function($s) use (&$schemaList){
                    if ($s != '.' && $s != '..')
                    {
                        // check directory
                        if (is_dir($s))
                        {
                            // get all files inside directory
                            $dr = getAllFiles($s);

                            $single = reduce_array($dr);

                            // only return file with (.sql) extension
                            if (count($single) > 0)
                            {
                                foreach ($single as $index => $file)
                                {
                                    $ext = stripos(basename($file), '.sql');

                                    if ($ext !== false && $ext == strlen(basename($file))-4)
                                    {
                                        $schemaList[time().'_'.basename($file)] = file_get_contents($file);
                                    }
                                }
                            }
                        }
                    }
                }, $schemas);
            }
            else
            {
                $newList = [];
                array_map(function($s) use (&$newList, $folder)
                {
                    if (is_file($s))
                    {
                        $ext = stripos(basename($s), '.sql');

                        if ($ext !== false && $ext == strlen(basename($s))-4)
                        {
                            $newList[time().'_'.basename($s)] = file_get_contents($s);
                        }
                    }
                    elseif (is_dir($s))
                    {
                        // read all files from dir
                        $schemas = glob(rtrim($s, '/') . '/*');
                        array_map(function($s) use (&$newList){
                            if ($s != '.' && $s != '..')
                            {
                                // check directory
                                if (is_dir($s))
                                {
                                    // get all files inside directory
                                    $dr = getAllFiles($s);

                                    $single = reduce_array($dr);

                                    // only return file with (.sql) extension
                                    if (count($single) > 0)
                                    {
                                        foreach ($single as $index => $file)
                                        {
                                            $ext = stripos(basename($file), '.sql');

                                            if ($ext !== false && $ext == strlen(basename($file))-4)
                                            {
                                                $newList[time().'_'.basename($file)] = file_get_contents($file);
                                            }
                                        }
                                    }
                                }
                            }
                        }, $schemas);
                    }
                    else
                    {
                        $scan = $folder . '/' . $s;
                        if (file_exists($scan))
                        {
                            $newList[time().'_'.basename($s)] = file_get_contents($scan);
                        }
                    }
                }, $schemaList);
                // replace
                $schemaList = $newList;
            }

            // migrate table
            self::schemaMigrate($schemaList, $database);
        }

        self::out(PHP_EOL);
    }

    // schema migration
    private static function schemaMigrate($schemaList, $database='')
    {
        $ass = self::getInstance();

        $connectwith = '';

        if ($database !== null)
        {
            $cs = Connection::readConfiguration($database);

            if (count($cs) > 0)
            {
                $connectwith = $database;
            }
        }

        self::out($ass->ansii('bold')."\nMigrate Schemas\n");

        try
        {
            ini_set('memory_limit', '-1');

            $instance = '';

            if ($connectwith != '') :

                $instance = db_with($connectwith);
            else:

                $instance = db();
            endif;

            // get query
            $query = $instance->getQuery();

            // get all rows
            $rowsBefore = $query->sql('SHOW TABLES');
            $rows = $rowsBefore->rowCount();
            $quaries = [];

            foreach ($schemaList as $key => $sql) :

                $sql = str_ireplace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $sql);
                $sql = str_ireplace('CREATE TABLE IF NOT EXISTS IF NOT EXISTS', 'CREATE TABLE IF NOT EXISTS', $sql);
                // get tables
                preg_match_all('/(NOT EXISTS\s+)([A-Z0-9a-z_-]+)\s+/', $sql, $matches);
                $sql = preg_replace('/(ALTER TABLE\s+)([A-Z0-9a-z_-]+)\s+/', '$1`$2` ', $sql);
                $sql = preg_replace('/(NOT EXISTS\s+)([A-Z0-9a-z_-]+)\s+/', '$1`$2` ', $sql);
                preg_match_all('/(DEFAULT\s+)([a-z0-9A-Z_-]+)/', $sql, $m);
                if (count($m[0]) > 0)
                {
                    foreach ($m[2] as $i => $const)
                    {
                        $rep = $m[0][$i];
                        if (preg_match('/([a-z]+)/', $const))
                        {
                            $sql = str_replace($rep, "DEFAULT '{$const}'", $sql);
                        }
                    }
                }  

                if (count($matches[0]) > 0)
                {
                    foreach ($matches[2] as $index => $table)
                    {
                        $sql .= "\n"."ALTER TABLE `{$table}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"."\n";
                    }
                }

                // run migration
                $run = $query->sql($sql);

                $quaries[$key] = $run->rowCount();

            endforeach;

            $rowsNow = $query->sql('SHOW TABLES');
            $rowsUpdated = $rowsNow->rowCount();

            foreach ($quaries as $key => $run)
            {
                $_rows = $run > 0 ? $ass->ansii('green').'('.$run.')'.$ass->ansii('reset') : $ass->ansii('red').'(0)'.$ass->ansii('reset');

                $string = (($rowsUpdated > $rows) ? $ass->ansii('green'). "(". ($rowsUpdated - $rows) . ")". $ass->ansii('reset') ." New tables, " : $ass->ansii('red').'(0)'.$ass->ansii('reset').' New Tables, ') . $_rows . ' Rows affected.';

                self::out($key . ' migration complete ..' . $string . "\n");
            }
        }
        catch(Exception $e)
        {
            self::out($ass->ansii('red').$e->getMessage());
        }
        catch(PDOException $e)
        {
            self::out($ass->ansii('red').$e->getMessage());
        }
    }

    // get deploy zip file
    private function getDeployZipFile(&$zipfiles, &$haslognew, &$log, &$allfiles=[], $dir='')
    {
        error_reporting(E_ALL);

        if (is_dir($dir))
        {
            $data = glob(rtrim($dir, '/') .'/{,.}*', GLOB_BRACE);

            foreach ($data as $i => $f)
            {
                if (basename($f) != '.' && basename($f) != '..')
                {
                    if (is_file($f) && !$this->exclude($f) && strpos($f, '.git') === false)
                    {
                        if ($dir == HOME)
                        {
                            if (!isset($log[$f]))
                            {
                                $zipfiles[] = $f;
                                $log[$f] = filemtime($f);
                            }
                            else
                            {
                                // check filemtime
                                $fmtime = filemtime($f);
                                if ($log[$f] != $fmtime)
                                {
                                    $zipfiles[] = $f;
                                    $log[$f] = $fmtime;
                                }
                            }
                        }
                        else
                        {
                            $zipfiles[] = $f;
                            $log[$f] = filemtime($f);
                        }

                        $allfiles[] = $f;
                    }

                    elseif (is_dir($f) && basename($f) != 'backup' && !$this->exclude($f) && strpos($f, '.git') === false)
                    {
                        $dr = getAllFiles($f);

                        $single = reduce_array($dr);

                        if (count($single) > 0)
                        {
                            foreach ($single as $z => $d)
                            {
                                if ($dir == HOME)
                                {
                                    if (!isset($log[$d]) && !$this->exclude($d))
                                    {
                                        $zipfiles[] = $d;
                                        $log[$d] = filemtime($d);
                                    }
                                    else
                                    {
                                        // check filemtime
                                        $fmtime = filemtime($d);
                                        if (isset($log[$d]) && $log[$d] != $fmtime && !$this->exclude($d))
                                        {
                                            $zipfiles[] = $d;
                                            $log[$d] = $fmtime;
                                        }
                                    }
                                }
                                elseif (!$this->exclude($f))
                                {
                                    $zipfiles[] = $d;
                                    $log[$d] = filemtime($d);
                                }

                                $allfiles[] = $d;
                            }
                        }

                    }
                }
            }
        }
        elseif (is_file($dir) && !$this->exclude($dir) && strpos($dir, '.git') === false)
        {
            $zipfiles[] = $dir;
            $log[$dir] = filemtime($dir);
            $allfiles[] = $dir;
        }
    }

    // exclude file
    private function exclude($fileOrDir)
    {
        $exclude = isset(self::$storage['exclude']) ? self::$storage['exclude'] : null;

        if ($exclude !== null)
        {
            foreach ($exclude as $i => $path)
            {
                $quote = preg_quote($path, '/');

                if (preg_match("/($quote)/", $fileOrDir) == true)
                {
                    return true;
                }
            }
        }

        return false;
    }

    // get deploy zipfile
    public function saveZipFile(&$zip, &$zipfile, &$logfile, &$haslognew, &$log, &$other, &$_allfiles, $dir = HOME)
    {
        if (file_exists($logfile))
        {
            $log = (array) (json_decode(file_get_contents($logfile)));

            $zipfiles = [];
            $allfiles = [];
            $del = [];

            if (count($log) > 0)
            {
                // generate zip file
                $this->getDeployZipFile($zipfiles, $haslognew, $log, $allfiles, $dir);

                $allfiles = array_flip($allfiles);

                foreach ($log as $f => $mt)
                {
                    if (!isset($allfiles[$f]))
                    {
                        $del[$mt] = $f;
                    }
                }
            }

            $_allfiles = substr(md5(implode('|', array_values((array) json_encode($log)))), 0, 5);

            if (count($zipfiles) > 0)
            {
                file_put_contents($logfile, json_encode($log, JSON_PRETTY_PRINT));

                // Create zip file
                if ($zip->open($zipfile, \ZipArchive::CREATE) === true)
                {
                    foreach ($zipfiles as $i => $f)
                    {
                        $zip->addFile($f);
                    }

                    $zip->close();
                }

                $haslognew = true;
            }
        }
        else
        {
            $log = [];

            $zipfiles = [];
            $allfiles = [];

            // generate zip file
            $this->getDeployZipFile($zipfiles, $haslognew, $log, $allfiles, $dir);

            if (count($zipfiles) > 0)
            {
                file_put_contents($logfile, json_encode($log, JSON_PRETTY_PRINT));

                // Create zip file
                if ($zip->open($zipfile, \ZipArchive::CREATE) === true)
                {
                    foreach ($zipfiles as $i => $f)
                    {
                        $zip->addFile($f);
                    }

                    $zip->close();
                }

                $haslognew = true;

                $_allfiles = substr(md5(implode('|', array_values((array) json_encode($log)))), 0, 5);
            }
        }
    }

    // deploy to production server
    public static function deploy($arg)
    {
        $ass = self::getInstance();

        $option = isset($arg[0]) ? $arg[0] : 'deploy';

        if ($option[0] == '-') $option = 'deploy';

        self::out($ass->ansii('bold')."\n".ucfirst($option)." Production server\n");

        $config = Environment::getEnv('deploy');
        $url = $config['url'];
        $kernel = PATH_TO_KONSOLE;
        $kernel = ltrim($kernel, HOME);

        include_once (self::$assistPath . get_path($kernel, '/deploy.php'));

        $deploy = new \DeployProject();
        $address = strlen($deploy->remote_address) > 4 ? $deploy->remote_address : $url;
        $requestID = strlen($deploy->requestID) > 4 ? $deploy->requestID : $config['token'];
        $requestHeader = $deploy->requestHeader;
        $uploadName = $deploy->uploadName;

        if (filter_var($address, FILTER_VALIDATE_URL))
        {
            switch($option)
            {
                case 'deploy':
                    $files = [];

                    $zip = new \ZipArchive();

                    $url = $address;

                    $hash = md5($url);

                    $zipfile = self::getFullPath(get_path(PATH_TO_STORAGE, '/Tmp/Deploy'.time().'.zip'));

                    $logfile = self::getFullPath(get_path(PATH_TO_STORAGE, '/Logs/Deploy/deploylog'.$hash.'.json'));

                    $other = null;
                    $haslognew = false;
                    $notrack = false;

                    // get options.
                    $options = array_splice($arg, 0);

                    if (count($options) > 0)
                    {
                        foreach($options as $index => $val)
                        {
                            if (substr($val, 0, 1) == '-')
                            {
                                if (preg_match('/^(-exclude|--except)/', $val) == true)
                                {
                                    if (strpos($val, '=') !== false)
                                    {
                                        $file = substr($val, strpos($val, '=')+1);
                                        self::$storage['exclude'] = explode(',', $file);
                                    }
                                }
                                else
                                {
                                    $val = ltrim($val, '-');
                                    // ensure directory exists
                                    $dir = self::$assistPath . $val;

                                    if (is_dir($dir))
                                    {
                                        // save zip file.
                                        $ass->saveZipFile($zip, $zipfile, $logfile, $haslognew, $log, $other, $allfiles, $dir);
                                    }
                                }
                            }
                        }
                    }

                    $copyLogFile = null;

                    if (file_exists($logfile))
                    {
                        $copyLogFile = file_get_contents($logfile);
                    }

                    // create zip file
                    if (count($options) > 0)
                    {
                        foreach($options as $index => $val)
                        {
                            if (substr($val, 0, 1) == '-')
                            {
                                if ($val == '--notrack')
                                {
                                    $notrack = true;
                                }
                                else
                                {
                                    // convert to array
                                    $dirArray = explode(',', $val);

                                    // loop through
                                    foreach ($dirArray as $i  => $dr)
                                    {
                                        $dr = trim(ltrim($dr, '-'));

                                        // ensure directory or file exists
                                        $dir = self::$assistPath . $dr;

                                        if (is_dir($dir) || file_exists($dir))
                                        {
                                            // save zip file.
                                            $ass->saveZipFile($zip, $zipfile, $logfile, $haslognew, $log, $other, $allfiles, $dir);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!file_exists($zipfile))
                    {
                        $ass->saveZipFile($zip, $zipfile, $logfile, $haslognew, $log, $other, $allfiles);
                    }

                    $rollback = true;

                    if (file_exists($zipfile))
                    {
                        self::out($ass->ansii('green')."[POST]"." Autheticating with Remote Server\n");

                        if (filter_var($url, FILTER_VALIDATE_URL))
                        {
                            $url = rtrim($url, 'deploy.php');
                            $url = rtrim($url, '/') . '/deploy.php';

                            // if ($haslog)
                            // {
                            //     $url .= '?mode=add-replace';
                            // }

                            $url .= '?size='.$allfiles;

                            if ($notrack) $url .= '&notrack=true';

                            $url .= $other;
                            $mime = mime_content_type($zipfile);

                            if (class_exists('CURLFile'))
                            {
                                $cfile = new \CURLFile(realpath($zipfile));
                            }
                            elseif (function_exists('curl_file_create'))
                            {
                                $cfile = curl_file_create(realpath($zipfile), $mime, basename($zipfile));
                            }
                            else
                            {
                                $cfile = '@'.realpath($zipfile).';type='.$mime.';filename='.basename($zipfile);
                            }

                            $post = array (
                                $deploy->uploadName => $cfile
                            );

                            $parse = parse_url($url);
                            $host = $parse['host'];

                            $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:68.0) Gecko/20100101 Firefox/68.0';

                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                            curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: multipart/form-data',
                                "{$deploy->requestHeader}: {$requestID}",
                                'Accept: text/html,'.$mime.',application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                                'Accept-Language: en-US,en;q=0.5',
                                'Cache-Control: max-age=0',
                                'Connection: keep-alive',
                                'Host: '.$host,
                                'Upgrade-Insecure-Requests: 1',
                                'User-Agent: '.$agent));
                            curl_setopt($ch, CURLOPT_TIMEOUT, 86400);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

                            $size = filesize($zipfile);
                            $filesize = convertToReadableSize($size);

                            self::sleep($ass->ansii('green')."[POST]".$ass->ansii('reset')." Uploading ($filesize)..\n");
                            $run = curl_exec($ch);

                            if (curl_errno($ch)) {

                                $msg = curl_error($ch);
                            }

                            // delete zip file
                            unlink($zipfile);

                            $data = json_decode($run);

                            if (is_object($data))
                            {
                                if ($data->status == 'success')
                                {
                                    $rollback = false;

                                    self::out($ass->ansii('green')."Complete! ".$ass->ansii('reset').$data->message);

                                    if ($haslognew)
                                    {
                                        if (count($log) > 0)
                                        {
                                            $log = json_encode($log, JSON_PRETTY_PRINT);
                                            file_put_contents($logfile, $log);
                                        }
                                    }
                                }
                                else
                                {
                                    self::out($ass->ansii('red')."Failed! ".$ass->ansii('reset').$data->message);
                                }
                            }
                            else
                            {
                                $run = strip_tags($run);
                                $msg = isset($msg) ? $msg : null;
                                self::out($ass->ansii('red')."Operation canceled. An error occured." . " $msg". ' '.$run);
                            }
                        }
                        else
                        {
                            self::out($ass->ansii('red')."Invalid Remote Address '{$url}/'");
                        }
                    }
                    else
                    {
                        self::out($ass->ansii('red').'Operation ended. Couldn\'t generate project zip file.');
                    }

                    // rollback
                    if ($rollback)
                    {
                        if (!is_null($copyLogFile)) file_put_contents($logfile, $copyLogFile);
                    }

                    break;

                case 'rollback':
                    // validate address
                    if (filter_var($address, FILTER_VALIDATE_URL))
                    {
                        $url = rtrim($address, 'deploy.php');
                        $url = rtrim($url, '/') . '/deploy.php';

                        $post = ['option' => 'rollback'];

                        if (isset($option[1]))
                        {
                            $post['deploy'] = $option[1];
                        }

                        $parse = parse_url($url);
                        $host = $parse['host'];

                        $agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:68.0) Gecko/20100101 Firefox/68.0';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                        curl_setopt($ch, CURLOPT_HTTPHEADER,array(
                            "{$deploy->requestHeader}: {$deploy->requestID}",
                            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language: en-US,en;q=0.5',
                            'Cache-Control: max-age=0',
                            'Connection: keep-alive',
                            'Host: '.$host,
                            'Upgrade-Insecure-Requests: 1',
                            'User-Agent: '.$agent));
                        curl_setopt($ch, CURLOPT_TIMEOUT, 86400);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));

                        $run = curl_exec($ch);

                        if (curl_errno($ch))
                        {
                            $msg = curl_error($ch);
                        }

                        $data = json_decode($run);

                        if (is_object($data))
                        {
                            if ($data->status == 'success')
                            {
                                self::out($ass->ansii('green')."Complete! ".$ass->ansii('reset').$data->message);
                            }
                            else
                            {
                                self::out($ass->ansii('red')."Failed! ".$ass->ansii('reset').$data->message);
                            }
                        }
                        else
                        {
                            $run = strip_tags($run);
                            self::out($ass->ansii('red')."Operation canceled. An error occured." . " $msg". ' '.$run);
                        }
                    }
                    else
                    {
                        self::out($ass->ansii('red').'Invalid Remote Address ('.$address.')');
                    }
                    break;
            }
        }
        else
        {
            self::out($ass->ansii('red')."Invalid Remote Address '{$address}/'");
        }

        self::out(PHP_EOL);
    }

    public function iswin()
    {
        if (strtolower(PHP_SHLIB_SUFFIX) == 'dll')
        {
            return true;
        }

        return false;
    }

    // cache
    public static function cache($arg)
    {
        $ass = self::getInstance();

        $command = isset($arg[0]) ? $arg[0] : null;
        $dir = isset($arg[1]) ? $arg[1] : null;

        self::out($ass->ansii('bold')."\nCache $dir\n");

        if (!is_null($dir)) $dir = '/'.$dir;

        switch ($command)
        {
            case 'clean':

                $files = getAllFiles(self::getFullPath(get_path(func()->const('storage'), '/Caches/'. $dir)));
                $reduce = reduce_array($files);
                

                foreach ($reduce as $path) :
                
                    $pathArray = explode('.', basename($path));

                    $extension = strtolower(end($pathArray));

                    if ($extension == 'json' || $extension == 'php') :
                    
                        // empty file
                        file_put_contents($path, '');
                    
                    else:
                    
                        @unlink($path);
                    
                    endif;

                endforeach;

                // clean directory cache
                $directoryCache = get_path(func()->const('system'), '/Common/directory.cache.json');

                // empty file
                if (file_exists($directoryCache)) file_put_contents($directoryCache, '');

                // clean autoload cache
                $autoloadCache = get_path(func()->const('system'), '/Packager/Moorexa/autoload.cache.json');

                // empty file
                if (file_exists($autoloadCache)) file_put_contents($autoloadCache, '');

                // clean happy caches
                $happyCaches = get_path(func()->const('system'), '/Templates/Happy/Web/Caches/');

                // continue if folder exists
                if (is_dir($happyCaches)) :

                    // Check if cached.json exists
                    if (file_exists($happyCaches . '/cached.json')) file_put_contents($happyCaches . '/cached.json', '');

                    // delete all files within tmp
                    $happyCacheTmp = $happyCaches . 'Tmp/';

                    // continue if temp file exists
                    if (is_dir($happyCacheTmp)) :

                        $files = getAllFiles(self::getFullPath($happyCacheTmp));
                        $reduce = reduce_array($files);

                        foreach ($reduce as $path) @unlink($path);

                    endif;

                endif;

                self::out("Cache system cleared".$ass->ansii('green'). " successfully");

            break;

            default:

                self::out($ass->ansii('red')."Invalid Cache command '$command'. Please try cache clear");
        }

        self::out(PHP_EOL);
    }

    // serve
    public static function serve($arg)
    {
        $ass = self::getInstance();

        $port = mt_rand(5121,9999);

        if (isset($arg[0]))
        {
            if (is_numeric($arg[0]))
            {
                $port = $arg[0];
            }
        }

        $other = array_slice($arg, 0);

        $notab = false;
        $goto = '';

        if (count($other) > 0)
        {
            foreach ($other as $i => $option)
            {
                if (trim($option) == '-notab')
                {
                    $notab = true;
                }

                if (strpos($option, '-goto=') !== false)
                {
                    $getval = explode('=', $option);
                    $goto = '/'.$getval[1];
                    $notab = false;
                }
            }
        }

        self::out($ass->ansii('bold')."\nServe\n");
        self::out($ass->ansii('line')."http://localhost:{$port}".$ass->ansii('reset').$ass->ansii('green')." running... CTRL + C | close | end | press Enter to terminate server\n");

        function bgExec($cmd)
        {
            $ass = ClassManager::singleton(Assist::class);

            if ($ass->iswin()) return pclose(popen( "start /B ". $cmd, "w"));

            return shell_exec($cmd . " > /dev/null &");
        }
        

        bgExec("php -S localhost:{$port} ". ' ' . constant('PATH_TO_UTILITY') . '/Classes/CLI/Router.php');

        if (!$notab) :
        
            self::sleep("Opening ".$ass->ansii('green')."http://localhost:{$port}".$ass->ansii('reset')." on your browser..");
        
        else:
        
            self::sleep($ass->ansii('green')."http://localhost:{$port}".$ass->ansii('reset')." Serving on your browser..");

        endif;

        if (!$notab) :
        
            if (!$ass->iswin()) :
            
                bgExec("open http://localhost:{$port}{$goto}");
            
            else:
            
                bgExec("start http://localhost:{$port}{$goto}");

            endif;
            
        endif;

        self::out(PHP_EOL);

        $reached = false;

        while($command = trim(fgets(STDIN)))
        {
            switch($command)
            {
                case 'close':
                case 'cancel':
                case 'end':

                    if (!$ass->iswin())
                    {
                        shell_exec('pkill -9 php');
                    }
                    else
                    {
                        shell_exec('taskkill /IM php.exe /F');
                    }
                    self::out($ass->ansii('green')."Server closed successfully...\n");
                    $reached = true;
                    return 0;
                    break;

                default:
                    fwrite(STDOUT, "> ");
            }
        }

        self::out(PHP_EOL);

        if ($reached === false)
        {
            if (!$ass->iswin())
            {
                shell_exec('pkill -9 php');
            }
            else
            {
                shell_exec('taskkill /IM php.exe /F');
            }

            self::out($ass->ansii('green')."Server closed successfully...\n");
            return 0;
        }
    }

    // version
    public static function version()
    {
        $ass = self::getInstance();

        self::out($ass->ansii('bold')."\nVersion\n");

        $version = self::$version;

        self::out($ass->ansii('green')."Moorexa v0.0.1,".$ass->ansii('reset')." Release Date: ".date('F jS Y'));
        self::out($ass->ansii('green')."Assist Manager v{$version},".$ass->ansii('reset')." Release Date: ".date('F jS Y'));

        $config = simplexml_load_file('config.xml');
        self::out($ass->ansii('green')."Production v".$config->versioning->production);
        self::out($ass->ansii('green')."Development v".$config->versioning->development);
        $config = null;

        self::out(PHP_EOL);

        self::out($ass->ansii('bold')."\nPackages/Services\n");

        $exclude = ['lab/backup', 'utility/version'];

        $data = glob(self::$assistPath .'*');
        $dirs = [];
        foreach ($data as $i => $f)
        {
            if (is_dir($f))
            {
                $dirs[] = $f;
            }
            else
            {
                $type = mime_content_type($f);
                if ($type == 'text/x-php')
                {
                    $dirs[] = $f;
                }
            }
        }

        $flip = array_flip($dirs);

        foreach ($exclude as $i => $ex)
        {
            if (isset($flip[$ex]))
            {
                unset($dirs[$flip[$ex]]);
            }
        }

        foreach ($dirs as $y => $f)
        {
            $dr = getAllFiles($f);
            $single = reduce_array($dr);

            if (count($single) > 0)
            {
                foreach ($single as $z => $d)
                {
                    foreach ($exclude as $m => $ms)
                    {
                        if (strpos($d, $ms) !== false)
                        {
                            unset($single[$z]);
                            $d = null;
                        }
                    }

                    if (is_file($d))
                    {
                        $type = mime_content_type($d);
                        $path =& $d;

                        if ($type == 'text/x-php')
                        {
                            $content = file_get_contents($path);

                            if (preg_match('/[\/][\*]{2}([\s\S]*?)(\@package)/i', $content, $match))
                            {

                                $data = $match[0];
                                $begin = strstr($content, $data);
                                $begin = substr($begin, strlen($data), 100);

                                preg_match('/([^\n]+)/', $begin, $package);

                                $package = isset($package[0]) ? trim($package[0]) : '';

                                $package = ucfirst($package);

                                // get version
                                preg_match('/[\/][\*]{2}([\s\S]*?)(\@version)/i', $content, $match);
                                $data = $match[0];
                                $begin = strstr($content, $data);
                                $begin = substr($begin, strlen($data), 100);

                                preg_match('/([^\n]+)/', $begin, $version);

                                $version = isset($version[0]) ? trim($version[0]) : null;

                                // get author
                                preg_match('/[\/][\*]{2}([\s\S]*?)(\@author)/i', $content, $match);
                                $data = $match[0];
                                $begin = strstr($content, $data);
                                $begin = substr($begin, strlen($data), 100);

                                preg_match('/([^\n]+)/', $begin, $author);

                                $author = isset($author[0]) ? trim($author[0]) : null;

                                $version = !is_null($version) ? ' v'.$version : '';
                                $author  = !is_null($author) ? ' Author: '.$author : '';

                                self::out($ass->ansii('green')."{$package}{$version},".$ass->ansii('reset').$author);

                            }

                            $content = null;
                        }
                    }
                }
            }
        }

        $flatten = null;
        $path = null;
        $files = null;

        self::out(PHP_EOL);
    }

    // page (refactor)
    public static function page($arg)
    {
        $ass = self::getInstance();

        $page = isset($arg[0]) ? $arg[0] : null; // page name
        self::out($ass->ansii('bold')."\nPage $page".PHP_EOL);

        // check if page exists
        if (is_dir(self::$controllerBasePath . '/' . ucfirst($page)))
        {
            $command = isset($arg[1]) ? $arg[1] : null;

            $other = array_slice($arg, 1);

            if (count($other) > 0)
            {
                foreach ($other as $i => $option)
                {
                    $eq = strpos($option, '=');
                    if ($eq !== false)
                    {
                        $opt = substr($option, 0, $eq);
                    }
                    else
                    {
                        $opt = substr($option,0);
                    }

                    $val = substr($option, $eq+1);
                }
            }

            switch($command)
            {
                // generate routes
                case 'routes':

                    $getPath = ControllerLoader::config('main.entry', 'main.php', ucfirst($page));

                    $main = file_exists($getPath) ? $getPath : self::$controllerBasePath . '/'. ucfirst($page) . '/' . $getPath;

                    if (is_file($main))
                    {
                        include_once($main);

                        // include routes.php
                        include_once get_path(func()->const('services'), '/routes.php');

                        $called = Router::$routesCalled;

                        // find all closure
                        $findAll = function(array $config, &$array) use ($called)
                        {
                            $getList = function($called, $config, $request, &$array, $extra = '')
                            {
                                $base = $config['page'] . '/' . $config['method'];
                                // check called
                                $current = $called[$request];
                                $quote = preg_quote($base, '/');

                                foreach ($current as $route)
                                {
                                    if (preg_match("/^($quote)/i", $route))
                                    {
                                        $array[] = $extra . $route;
                                    }
                                }
                            };

                            if ($config['request'] == 'OTHER')
                            {
                                // get keys
                                $keys = array_keys($called);
                                
                                foreach ($keys as $key)
                                {
                                    if ($key != 'GET' && $key != 'POST' && $key != 'DELETE' && $key != 'PUT')
                                    {
                                        $getList($called, $config, $key, $array, "($key) ");
                                    }
                                }
                            }

                            if (isset($called[$config['request']]))
                            {
                                $getList($called, $config, $config['request'], $array);
                            }
                        };

                        $controllerClassName = 'Moorexa\Framework\\' . ucfirst($page);

                        if (class_exists($controllerClassName))
                        {
                            $methods = get_methods($controllerClassName);

                            $console = new Console_Table();
                            $headers = ['Route', 'GET', 'POST', 'DELETE', 'PUT', 'OTHER'];
                            $console->setHeaders($headers);

                            $listed = [];

                            foreach($methods as $i => $meth)
                            {
                                $row = [];
                                $row[] = $page.'/'.$meth;

                                $listed[] = $row[0];

                                $get = [];
                                $post = [];
                                $delete = [];
                                $put = [];
                                $other = [];

                                $model = self::$controllerBasePath . '/' . ucfirst($page).'/'.ControllerLoader::config('directory', 'model', ucfirst($page)).'/'.$meth.'.php';
                                
                                if (file_exists($model))
                                {
                                    include_once($model);

                                    // model class name
                                    $modelClassName = 'Moorexa\Framework\\' . ucfirst($page) . '\Models\\' . ucfirst($meth);

                                    $modelMethods = get_methods($modelClassName);

                                    if (count($modelMethods) > 0)
                                    {
                                        foreach($modelMethods as $x => $mm)
                                        {
                                            if (preg_match('/^(get)([\S]+)/', $mm, $ln))
                                            {
                                                $end = strtolower(end($ln));
                                                $get[] = $page.'/'.$meth.'/'.($end != $meth ? $end : 'get');

                                                $findAll([
                                                    'page' => $page,
                                                    'method' => $meth,
                                                    'request' => 'GET'
                                                ], $get);
                                            }

                                            if (preg_match('/^(post)([\S]+)/', $mm, $ln))
                                            {
                                                $end = strtolower(end($ln));
                                                $post[] = $page.'/'.$meth.'/'.($end != $meth ? $end : 'post');
                                            }

                                            if (preg_match('/^(put)([\S]+)/', $mm, $ln))
                                            {
                                                $end = strtolower(end($ln));
                                                $put[] = $page.'/'.$meth.'/'.($end != $meth ? $end : 'put');
                                            }

                                            if (preg_match('/^(delete)([\S]+)/', $mm, $ln))
                                            {
                                                $end = strtolower(end($ln));
                                                $delete[] = $page.'/'.$meth.'/'.($end != $meth ? $end : 'delete');
                                            }
                                        }
                                    }
                                }

                                foreach ($headers as $index => $request)
                                {
                                    if ($index > 0)
                                    {
                                        $lowercase = strtolower($request);

                                        $findAll([
                                            'page' => $page,
                                            'method' => $meth,
                                            'request' => $request
                                        ], ${$lowercase});
                                    }
                                }

                                $row[] = implode("\n",$get);
                                $row[] = implode("\n",$post);
                                $row[] = implode("\n",$delete);
                                $row[] = implode("\n",$put);
                                $row[] = implode("\n",$other);

                                $console->addRow($row);
                            }

                            $listed = array_flip($listed);

                            foreach ($called as $requestMethod => $routes)
                            {
                                foreach ($routes as $route)
                                {
                                    if (preg_match("/^($page)[\/]([\S]+)/", $route))
                                    {
                                        $routeArray = explode('/', $route);
                                        // get controller and view
                                        $newview = $routeArray[0] . '/' . $routeArray[1];

                                        if (!isset($listed[$newview]))
                                        {
                                            $row = [];
                                            $row[] = $routeArray[0] . '@hasreturn';

                                            $get = [];
                                            $post = [];
                                            $delete = [];
                                            $put = [];
                                            $other = [];

                                            foreach ($headers as $index => $request)
                                            {
                                                if ($index > 0)
                                                {
                                                    $lowercase = strtolower($request);

                                                    $findAll([
                                                        'page' => $routeArray[0],
                                                        'method' => $routeArray[1],
                                                        'request' => $request
                                                    ], ${$lowercase});
                                                }
                                            }

                                            $row[] = implode("\n",$get);
                                            $row[] = implode("\n",$post);
                                            $row[] = implode("\n",$delete);
                                            $row[] = implode("\n",$put);
                                            $row[] = implode("\n",$other);
                                            $console->addRow($row);
                                        }
                                    }
                                }
                            }

                            self::out($console->getTable());
                        }
                        else
                        {
                            self::out($ass->ansii('red')."\nClass '$page' not found in '$main'. Operation failed". PHP_EOL);
                        }
                    }
                    else
                    {
                        self::out($ass->ansii('red')."\nmain file not found in '$main'. Operation failed". PHP_EOL);
                    }
                    break;
            }
        }
        else
        {
            self::out($ass->ansii('red')."\nPage '$page' doesn't exists. Operation failed". PHP_EOL);
        }
    }

    public static function commands()
    {
        $ass = self::getInstance();

        self::out($ass->ansii('bold')."\nAssist CLI Commands".PHP_EOL);

        $tbl = new Console_Table();

        $tbl->setHeaders(['Command', 'Description']);

        foreach (self::$commandHelp as $key => $array)
        {
            $info = wordwrap($array['info'], 30) . "\n";

            $tbl->addRow(["php assist $key", $info]);
        }

        self::out($tbl->getTable());
        self::out(PHP_EOL);
    }

    // credits
    public static function credits()
    {
        $ass = self::getInstance();

        self::out($ass->ansii('bold')."\nWe give credits to these authors.\n");


        $exclude = [PATH_TO_LAB . 'backup', PATH_TO_UTILITY . 'version'];

        $data = glob(self::$assistPath .'*');
        $dirs = [];
        foreach ($data as $i => $f)
        {
            if (is_dir($f))
            {
                $dirs[] = $f;
            }
            else
            {
                $type = mime_content_type($f);
                if ($type == 'text/x-php')
                {
                    $dirs[] = $f;
                }
            }
        }

        $flip = array_flip($dirs);

        foreach ($exclude as $i => $ex)
        {
            if (isset($flip[$ex]))
            {
                unset($dirs[$flip[$ex]]);
            }
        }

        foreach ($dirs as $y => $f)
        {
            $dr = getAllFiles($f);
            $single = reduce_array($dr);

            if (count($single) > 0)
            {
                foreach ($single as $z => $d)
                {
                    foreach ($exclude as $m => $ms)
                    {
                        if (strpos($d, $ms) !== false)
                        {
                            unset($single[$z]);
                            $d = null;
                        }
                    }

                    if (is_file($d))
                    {
                        $type = mime_content_type($d);
                        $path =& $d;

                        if ($type == 'text/x-php')
                        {
                            $content = file_get_contents($path);

                            if (preg_match('/[\/][\*]{2}([\s\S]*?)(\@package)/i', $content, $match))
                            {

                                $data = $match[0];
                                $begin = strstr($content, $data);
                                $begin = substr($begin, strlen($data), 100);

                                preg_match('/([^\n]+)/', $begin, $package);

                                $package = isset($package[0]) ? trim($package[0]) : '';

                                $package = ucfirst($package);

                                // get author
                                preg_match('/[\/][\*]{2}([\s\S]*?)(\@author)/i', $content, $match);
                                if (isset($match[0]))
                                {
                                    $data = $match[0];
                                    $begin = strstr($content, $data);
                                    $begin = substr($begin, strlen($data), 100);

                                    preg_match('/([^\n]+)/', $begin, $author);

                                    $author = isset($author[0]) ? trim($author[0]) : null;
                                    $author  = !is_null($author) ? ' Author: '.$author : '';

                                    self::out($ass->ansii('green')."{$package},".$ass->ansii('reset').$author);
                                }

                            }

                            $content = null;
                        }
                    }
                }
            }
        }

        $flatten = null;
        $path = null;
        $files = null;

        self::out(PHP_EOL);
    }

    // optimize
    public static function optimize()
    {
        $ass = self::getInstance();

        self::out($ass->ansii('bold')."\nOptimize Application. Bundle CSS and JS\n");

        //read  bundler
        $bundler = json_decode(file_get_contents(get_path(PATH_TO_KERNEL, 'loadStatic.json')));

        // styles
        $styles = $bundler->stylesheet;

        // javascript
        $js = $bundler->scripts;

        // load assets
        $assets = new Moorexa\Assets();

        // skipped css files
        $skipped = ['css' => [], 'js' => []];

        // bundle
        $bundle = ['css' => [], 'js' => []];

        $hasCssBundle = false;
        $hasJsBundle = false;

        // read styles
        array_map(function($style) use (&$assets, &$skipped, &$bundle, &$hasCssBundle)
        {
            if (basename($style) != 'moobundle.css')
            {
                $stylePath = $assets->css[$style];

                // current directory
                $current_dir = HOME . basename(__FILE__);

                $stylePath = self::$assistPath . ltrim($stylePath, $current_dir);

                if (strlen($stylePath) > 5 && file_exists($stylePath))
                {
                    // read content. check for includes
                    $content = file_get_contents($stylePath);

                    // has absolute path
                    preg_match('/[\.]{1,}[\/]([^\?\)\'\"]*)/', $content, $match);

                    if (count($match) > 0)
                    {
                        $path = $match[0];

                        // check if path
                        $checkPath = self::$assistPath . PATH_TO_CSS . $path;

                        // file exists
                        if (file_exists($checkPath))
                        {
                            $bundle['css'][] = $stylePath;
                        }
                        else
                        {
                            $skipped['css'][] = $stylePath;
                        }
                    }
                    else
                    {
                        $bundle['css'][] = $stylePath;
                    }

                }
                else
                {
                    $skipped['css'][] = $style;
                }
            }
            else
            {
                $hasCssBundle = true;
            }

        }, $styles);

        // read javascripts
        array_map(function($script) use (&$assets, &$skipped, &$bundle, &$hasJsBundle)
        {
            if (basename($script) != 'moobundle.js')
            {
                $scriptPath = $assets->js[$script];

                // current directory
                $current_dir = HOME . basename(__FILE__);

                $scriptPath = self::$assistPath . ltrim($scriptPath, $current_dir);

                if (strlen($scriptPath) > 5 && file_exists($scriptPath))
                {
                    $bundle['js'][] = $scriptPath;
                }
                else
                {
                    $skipped['js'][] = $script;
                }
            }
            else
            {
                $hasJsBundle = true;
            }

        }, $js);


        $app = new Moorexa\View();


        if (count($bundle['css']) > 0)
        {
            $cssbundle = PATH_TO_CSS . 'moobundle.css';

            array_map(function($path) use ($cssbundle){
                $fh = fopen($cssbundle, 'a+');
                fwrite($fh, file_get_contents($path));
                fclose($fh);
            }, $bundle['css']);

            // SHRIKE CSS
            $shrinked = $app->minifycss(file_get_contents($cssbundle), false, false);
            // put
            file_put_contents($cssbundle, $shrinked);

            self::out($ass->ansii('green')."\nCSS Optimized Successfully.");
        }

        if (count($bundle['js']) > 0)
        {
            $jsbundle = PATH_TO_JS . 'moobundle.js';

            array_map(function($path) use ($jsbundle){
                $fh = fopen($jsbundle, 'a+');
                fwrite($fh, file_get_contents($path));
                fclose($fh);
            }, $bundle['js']);

            // SHRIKE JS
            $shrinked = $app->minifyjs(file_get_contents($jsbundle), true, true);
            // put
            file_put_contents($jsbundle, $shrinked);

            self::out($ass->ansii('green')."JS Optimized Successfully.");
        }

        self::sleep("\nFinalizing Optimization.");
        self::sleep("Adding bundles to kernel/loadStatic.json.\n");

        if (!$hasCssBundle)
        {
            // add bundle
            $bundler->stylesheet[] = 'moobundle.css';
        }

        if (!$hasJsBundle)
        {
            // add bundle
            $bundler->scripts[] = 'moobundle.js';
        }

        // load static
        $loadstatic = json_encode($bundler, JSON_PRETTY_PRINT);

        if (!$hasCssBundle || !$hasJsBundle)
        {
            file_put_contents(self::getFullPath(get_path(PATH_TO_KERNEL, 'loadStatic.json')), $loadstatic);
        }

        // generate lock file
        $lockdata = [
            'stylesheet' => $skipped['css'],
            'script' => $skipped['js']
        ];

        // save lock file
        file_put_contents(self::getFullPath(get_path(PATH_TO_KERNEL, 'loadStatic.lock')), json_encode($lockdata, JSON_PRETTY_PRINT));
        self::out("lock file generated in kernel/. Bundling complete..\n");
    }

    // documentation
    public static function doc($arg)
    {
        $ass = self::getInstance();

        self::out($ass->ansii('bold')."\nDocumentation Wizard\n");

        $command = $arg[0];

        // open api
        $command = explode(':', $command);

        switch($command[0])
        {
            case 'generate':
            case 'gen':
                $dir = $command[1];
                // get all directories
                $dirs = glob(self::$assistPath . $dir.'/*');

                // add watchman
                $watchman = self::$assistPath . $dir.'/watchman.json';

                // only create if it doesn't exists
                if (!file_exists($watchman))
                {
                    Moorexa\File::write('{}', $watchman);
                }

                // read watchman
                $watch = json_decode(trim(file_get_contents($watchman)));
                // convert to an array
                $watch = toArray($watch);

                foreach ($dirs as $i => $dr)
                {
                    if ($dr != '.' && $dr != '..')
                    {
                        if (is_dir($dr))
                        {
                            // has main
                            $main = $dr . '/main.php';

                            // get sidebar
                            $sidebar = [];

                            // check
                            if (file_exists($main))
                            {
                                // get content
                                $content = file_get_contents($main);
                                // extract doc
                                preg_match_all("/([@]doc-start[:]+([^\n]+))([\S\s]*?)(@doc-end)/", $content, $doc);
                                if (count($doc[0]) > 0)
                                {
                                    foreach ($doc[3] as $x => $data)
                                    {
                                        // get all comments
                                        preg_match_all('/(\/\*)([\s\S]*?)(\*\/)|(\/\/)\s*([^\n]+)|(\*)([\s\S]*?)(\*\/)/', $data, $match);
                                        if (count($match[0]) > 0)
                                        {
                                            $content = "\n";

                                            // get markdown content
                                            foreach ($match[0] as $index => $line)
                                            {
                                                if (preg_match('/^(\/\/)\s*([\s\S]*)/', $line, $single))
                                                {
                                                    // get content
                                                    if (trim($single[2][0]) == '@')
                                                    {
                                                        $content .= preg_replace('/^(@){1}/', '', $single[2]);
                                                    }
                                                }
                                                else
                                                {
                                                    // block comment
                                                    $line = preg_replace('/^(\/\*){1}|(\*\/)$/', "\n", $line);

                                                    $line = preg_replace("/([\s]+)[\*]\s*/", "\n", $line);
                                                    $line = preg_replace("/^([*]([^\s\S]*?)[\n])/", '', $line);

                                                    $content .= $line;
                                                }
                                            }


                                            // get doc-start
                                            $sidebarName = $doc[2];
                                            // get function declaration
                                            preg_match('/(function\s*)([\S]*?)[\(]/', $data, $func);
                                            $func = end($func);
                                            // get request method
                                            preg_match('/([a-z]*?)([A-Z0-9_]+)([\S]*)/', $func, $method);
                                            $meth = $method[1];
                                            $req = strtolower($method[2]) . end($method);
                                            $sidebar[$func] = [
                                                'title' => $sidebarName[$x],
                                                'method' => strtoupper($meth),
                                                'request' => '/'.$req
                                            ];

                                            $req = stripos('/'.$req, basename($dr)) === false ? basename($dr).'/'.$req : $req;

                                            // add method
                                            $a_x = '';
                                            switch($meth)
                                            {
                                                case 'get':
                                                    $a_x = '<span class="label label-big label-success"> GET / '.$req.' </span>';
                                                    break;

                                                case 'post':
                                                    $a_x = '<span class="label label-big label-primary"> POST / '.$req.' </span>';
                                                    break;

                                                case 'put':
                                                    $a_x = '<span class="label label-big label-warning"> PUT / '.$req.' </span>';
                                                    break;

                                                case 'delete':
                                                    $a_x = '<span class="label label-big label-danger"> DELETE / '.$req.' </span>';
                                                    break;
                                            }

                                            $content = $a_x . "\n" . $content;

                                            // generate sidebar
                                            ob_start();
                                            var_export($sidebar);
                                            $arr = '<?php'."\n";
                                            $arr .= 'return '. ob_get_contents() . ';'."\n";
                                            $arr .= '?>';
                                            ob_clean();

                                            // save inside documentation
                                            $documentDir = $dr . '/Documentation/';
                                            // create dir if it doesn't exists
                                            if (!is_dir($documentDir))
                                            {
                                                // make directory
                                                mkdir($documentDir, 0777);
                                            }

                                            // write sitemap
                                            Moorexa\File::write($arr, $documentDir . 'sitemap.php');
                                            Moorexa\File::write($content, $documentDir . $func.'.md');

                                            if (!isset($watch[$func]))
                                            {
                                                $watch[basename($dr).'/'.$func] = $documentDir . $func . '.md';
                                            }
                                            else
                                            {
                                                // get val
                                                $val = $watch[basename($dr).'/'.$func];
                                                $current = $documentDir . $func . '.md';

                                                if ($val != $current)
                                                {
                                                    $watch[basename($dr).'/'.$func] = $current;
                                                }
                                            }

                                            // save
                                            Moorexa\File::write(json_encode($watch, JSON_PRETTY_PRINT), $watchman);

                                            // return output
                                            self::out(strtoupper($meth) . ' /'.$req.' @'.basename($dr).' DOC Generated '.$ass->ansii('green').'Successfully'.$ass->ansii('reset')."\n");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                break;
        }

    }

    // system update
    public static function _system($arg)
    {
        $ass = self::getInstance();
        $command = strtolower($arg[0]);

        $guzzle = new GuzzleHttp\Client();

        $domain = 'http://updates.moorexa.com/';

        // cookie jar
        $jar = new GuzzleHttp\Cookie\CookieJar();

        // add request body
        $requestBody = [
            'debug' => false,
            'jar' => $jar
        ];

        switch ($command)
        {
            // check for update
            case 'check-update':
                self::sleep("Checking for system update...");
                $domain .= 'latest';
                // send request
                $path = 'utility/Storage/Tmp/version_update.zip';
                $resource = fopen($path, 'w');
                $requestBody['sink'] = $resource;
                $send = $guzzle->request('GET', $domain, $requestBody);
                if (file_exists($path))
                {
                    // extract zip file.
                    $zip = new \ZipArchive();
                    $zip->open($path);
                    $zip->extractTo(HOME);
                    $zip->close();

                    $dir = HOME . 'versions/';
                    $getdir = glob($dir.'{,.}*', GLOB_BRACE);
                    $dirs = [];
                    foreach ($getdir as $i => $gdir)
                    {
                        $base = basename($gdir);
                        if ($base != '.' && $base != '..')
                        {
                            if (is_dir($gdir))
                            {
                                $gdata = glob($gdir.'/{,.}*', GLOB_BRACE);
                                foreach ($gdata as $a => $da)
                                {
                                    $base = basename($da);

                                    if ($base != '.' && $base != '..')
                                    {
                                        if (is_dir($da))
                                        {
                                            $files = getAllFiles($da);
                                            $reduce = reduce_array($files);

                                            foreach ($reduce as $index => $filepath)
                                            {
                                                // remove base
                                                $basename = basename($filepath);
                                                $cpath = rtrim($filepath, $basename);

                                                $cpath = substr($cpath, strlen($gdir)+1);

                                                if (!is_dir($cpath))
                                                {
                                                    mkdir($cpath);
                                                }

                                                if (is_dir($cpath))
                                                {
                                                    copy($filepath, $cpath . $basename);
                                                    unlink($filepath);
                                                    $fp = rtrim($filepath, $basename);
                                                    $dirs[] = $fp;
                                                }
                                            }
                                        }
                                        elseif (is_file($da))
                                        {
                                            $basename = basename($da);
                                            $cpath = rtrim($da, $basename);

                                            $cpath = substr($cpath, strlen($gdir)+1);

                                            copy($da, $cpath . $basename);
                                            unlink($da);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $len = count($dirs);

                    for($i=$len; $i != -1; $i--)
                    {
                        $dir = $dirs[$i];

                        if (is_dir($dir))
                        {
                            deldir($dir);
                        }
                    }

                    if (is_dir($gdir))
                    {
                        deldir($gdir);
                    }

                    $base = basename($gdir);
                    deldir('versions');

                    self::out($ass->ansii('green'). $base .' installed successfully.');
                    unlink($path);
                }
                break;

            // update
            case 'update':
                self::sleep("Checking for system update..");

                break;
        }

        self::out("\n");
    }

    private static function updateXMLValue(&$xml, $tag, $value, $circle = false)
    {
        foreach($xml as $i => $tree)
        {
            if ($i == $tag)
            {
                $xml->{$tag} = $value;
                return $xml;
            }
            else
            {
                if (is_object($tree))
                {
                    self::updateXMLValue($tree, $tag, $value, true);
                }
            }
        }

        return $xml;
    }

    private static function updateXMLAttribute(&$xml, $tag, $attr, $value)
    {
        foreach($xml as $i => $tree)
        {
            if ($i == $tag)
            {
                $attributes = $tree->attributes();
                if (property_exists($attributes, $attr))
                {
                    $attributes->{$attr} = $value;
                }
                else
                {
                    $attributes->addAttribute($attr, $value);
                }
                return $xml;
            }
            else
            {
                if (is_object($tree))
                {
                    self::updateXMLAttribute($tree, $tag, $attr, $value);
                }
            }
        }

        return $xml;
    }

    // add running task helper
    private static function addRunningTaskHelper(string $script)
    {
        $ass = self::getInstance();

        // check for --hide-out
        if (strpos($script, '--hide-out') === false) :

            self::out($ass->ansii('bold')."====== (Running) ======");
            self::out($ass->ansii('bold')."$script".$ass->ansii('reset'));
            self::out($ass->ansii('bold')."==\n");

        endif;
    }

    private static function processAndLoadAssistManager($script, $target, $bashConfiguration, $method, $index)
    {
        if (stripos($script, 'php') === 0 && stripos($script, 'assist') !== false) :
        
            self::addRunningTaskHelper($script);

            // remove hide out
            $script = str_replace('--hide-out', '', $script);
            
            // get characters
            $script = preg_replace('/^(php\s*)/', '', $script);
            
            // convert to an array
            $scriptArray = explode(' ', $script);
            $copy = $scriptArray;
            $other = array_splice($scriptArray, 2);
            $method = $scriptArray[1];

            if ($index !== null) :
            
                if (isset($bashConfiguration[$method]) && is_array($bashConfiguration[$method][$target])) :
                
                    // remove index
                    unset($bashConfiguration[$method][$target][$index]);

                endif;

            endif;

            // load manager now
            self::loadAssistManager($copy, $other, $method, $bashConfiguration);
        
        else:
            
            
            self::addRunningTaskHelper($script);

            // remove hide out
            $script = str_replace('--hide-out', '', $script);

            // run script
            pclose(popen($script, "w"));
            self::out(PHP_EOL);

        endif;
    }

    private static function processAndLoadAssistManagerForClosure($method, $index, $target, $copy, $other, $script, $bashConfiguration)
    {
        // remove index
        if ($index !== null) unset($bashConfiguration[$method][$target][$index]);

        // convert other to string
        $otherString = implode(' ', $other);
        $script = call_user_func_array($script, [$otherString, $other]);

        if (is_string($script)) return self::processAndLoadAssistManager($script, $target, $bashConfiguration, $method, $index);

        // load manager now
        if ($script !== null) self::loadAssistManager($copy, $other, $method, $bashConfiguration);    
    }

    // load assist manager
    private static function loadAssistManager(array $copy, array $other, string $method, array $bashConfiguration) 
    {
        unset($copy[0]);

        switch ($method) :
        
            case '-h':
            case '-help':
                $method = 'help';
            break;

            case '-v':
            case '-version':
                $method = 'version';
            break;

        endswitch;

        // Assist manager for packages
        if (strpos($method, ':')) :
        
            $expl = explode(":", $method);
            $register = strtolower($expl[0]);
            $meth = $expl[1];
            $config = include_once(get_path(func()->const('konsole'), '/assist.php'));

            if (is_array($config) && count($config) > 0) :
            
                foreach($config as $cls => $arr) :
                
                    $cls = strtolower($cls);

                    if ($cls == $register) :
                    
                        $path = isset($arr['assist']) ? $arr['assist'] : null;
                        
                        if (isset($arr['path'])) :
                        
                            if (is_dir($arr['path'])) :
                            
                                $p = rtrim($arr['path'], '/') . '/';

                                self::$assistPath = $p;

                                // define path
                                if (!defined(strtoupper($cls) . '_PATH')) define(strtoupper($cls) . '_PATH', $p);

                            endif;

                        endif;

                        if (!is_null($path) && file_exists($path)) :
                        
                            include_once ($path);
                            $base = basename($path);
                            $className = substr($base, 0, strpos($base, '.'));

                            if (class_exists($className)) :
                            
                                $ref = new \ReflectionClass($className);

                                $meth = $meth == 'new' ? '_new' : $meth;

                                if ($ref->hasMethod($meth)) :
                                
                                    $meth = $ref->getMethod($meth);

                                    if ($meth->isPublic() || $meth->isProtected()) :
                                    
                                        $method = $expl[1];

                                        $method2 = $method == 'new' ? '_new' : $method;

                                        $full = trim($method2 .' '.(isset($other[0]) ? $other[0] : '') );

                                        $commands = self::$commands;

                                        if ($ref->hasProperty('commands')) :
                                        
                                            $commands = $ref->getStaticPropertyValue('commands');

                                            if (count($commands) > 0) self::$commands = $commands;

                                        endif;

                                        if ($ref->hasProperty('commandHelp')) :
                                        
                                            $commandHelp = $ref->getStaticPropertyValue('commandHelp');

                                            if (count($commandHelp) > 0) self::$commandHelp = $commandHelp;

                                        endif;

                                        $continue = false;

                                        if (isset($commands[$method]) || isset($commands[$full])) :
                                        
                                            $continue = true;
                                        
                                        else:
                                        
                                            if ($meth->class == $ref->getName()) :
                                            
                                                if ($meth->isPublic()) $continue = true;

                                            endif;

                                        endif;

                                        if ($continue) :
                                        
                                            $copy[1] = $method;

                                            if (self::hasOption($copy, $option, $command)) :
                                            
                                                self::generateQuickHelp($option, $command); return 0;
                                            
                                            else:
                                            
                                                if (isset($commands[$full])) :
                                                
                                                    $full = lcfirst(preg_replace('/[\s]/','',ucwords($full)));
                                                
                                                else:
                                                
                                                    $method = $method == 'new' ? '_new' : $method;

                                                    $full = $method;

                                                endif;

                                                $meth = $ref->getMethod($full);

                                                if ($meth->isPublic()) :
                                                
                                                    $loadBash = false;
                                                    if (isset($bashConfiguration[$full])) { $loadBash = true; }

                                                    // load bash
                                                    if ($loadBash) self::loadBashConfiguration($bashConfiguration, $full, $copy, $other, 'start');

                                                    call_user_func($className.'::'.$full, $other);

                                                    // load bash
                                                    if ($loadBash) self::loadBashConfiguration($bashConfiguration, $full, $copy, $other, 'finish');

                                                endif;
                                                
                                            endif;

                                        endif;
                                    
                                    else:
                                    
                                        fwrite(STDOUT,"'{$expl[1]}' Method is not public. Assist manager failed to continue.\n\n");

                                    endif;
                                
                                else:
                                
                                    if ($ref->hasProperty('commands')) :
                                    
                                        $commands = $ref->getStaticPropertyValue('commands');

                                        if (count($commands) > 0) self::$commands = $commands;

                                    endif;

                                    self::invalid($meth, $className);

                                endif;
                            
                            else:
                            
                                fwrite(STDOUT,"'$className' Class doesn't exists. Assist manager failed to continue.\n\n");

                            endif;

                        endif;

                        return false;

                    endif;

                endforeach;

            endif;

            fwrite(STDOUT,"'$register' not registered. Assist manager failed to continue.\n\n");
        
        else:
        

            $method2 = $method == 'new' ? '_new' : $method;
            $method2 = $method2 == 'system' ? '_system' : $method2;

            $full = trim($method2 .' '.(isset($other[0]) ? $other[0] : '') );

            if (isset(self::$commands[$method]) || isset(self::$commands[$full])) :
            
                if (self::hasOption($copy, $option, $command)) :
                
                    self::generateQuickHelp($option, $command); return 0;
                
                else:
                
                    if (isset(Assist::$commands[$full])) :
                    
                        $full = lcfirst(preg_replace('/[\s]/','',ucwords($full)));

                    else:
                    
                        $method = $method == 'new' ? '_new' : $method;
                        $method = $method == 'system' ? '_system' : $method;

                        $full = $method;

                    endif;

                    $loadBash = false;

                    if (isset($bashConfiguration[$full]))  $loadBash = true;

                    // load bash
                    if ($loadBash) self::loadBashConfiguration($bashConfiguration, $full, $copy, $other, 'start');

                    self::{$full}($other);

                    // load bash
                    if ($loadBash) self::loadBashConfiguration($bashConfiguration, $full, $copy, $other, 'finish');

                endif;
            
            else:
        

                if (isset($bashConfiguration[$method])) :
                
                    // load start
                    self::loadBashConfiguration($bashConfiguration, $method, $copy, $other);

                    // load bash
                    self::loadBashConfiguration($bashConfiguration, $method, $copy, $other, 'finish');
                
                else:
                
                    if (function_exists($method)) :
                    
                        $ass = self::getInstance();

                        fwrite(STDOUT, $ass->ansii('bold')."Running $method function\n\n". $ass->ansii('reset'));
                        fwrite(STDOUT, call_user_func_array($method, $other));
                        fwrite(STDOUT, PHP_EOL);
                        fwrite(STDOUT, PHP_EOL);
                    
                    else:
                    
                        // failed
                        self::invalid($method);

                    endif;

                endif;

                // end process
                return 0;
            
            endif;

        endif;
    }

    // load bash configuration
    public static function loadBashConfiguration($bashConfiguration, $method, $copy, $other, $target = 'start')
    {
        // get start
        $bash = $bashConfiguration[$method];

        if (isset($bash[$target]))
        {
            switch (is_array($bash[$target]))
            {
                case true:
                    foreach ($bash[$target] as $index => $script)
                    {
                        if (is_string($script))
                        {
                            self::processAndLoadAssistManager($script, $target, $bashConfiguration, $method, $index);
                        }
                        elseif (is_callable($script) && !is_null($script))
                        {
                            self::processAndLoadAssistManagerForClosure($method, $index, $target, $copy, $other, $script, $bashConfiguration);
                        }
                    }
                break;

                case false:
                    if (is_callable($bash[$target]))
                    {
                        self::processAndLoadAssistManagerForClosure($method, null, $target, $copy, $other, $bash[$target], $bashConfiguration);
                    }
                break;
            }

        }
        
    }

    // run cli command
    public static function runCliCommand(string $command)
    {
        // get method
        $script = explode(' ', $command);

        // get method
        $index = null;
        $method = null;

        if (count($script) >= 2)
        {
            $method = $script[2];

            if (isset(self::$bashConfiguration[$method]))
            {
                $index = 0;
            }
        }

        return self::processAndLoadAssistManager($command, 'start', self::$bashConfiguration, $method, $index);
    }

    // register command helper
    public static function commandHelper(array $options)
    {
        self::$commandHelpers = $options;
    }

    // emit decrypt
    public static function emitDecrypt(string &$content) : void 
    {
        // load closure
        foreach (self::$decryptListeners as $closure) if (is_callable($closure)) call_user_func_array($closure, [&$content]);
    }

    // listen for decrypt
    public static function onDecrypt(Closure $closure) : void 
    {
        self::$decryptListeners[] = $closure;
    }

    // load command helper
    private static function loadCommandHelper()
    {
        // load command helpers
        foreach (self::$commandHelpers as $command => $commandList) :

            // split by pipe
            $commandArray = explode('|', $command);

            // run a list
            foreach ($commandArray as $command) :

                // get size
                $commandSize = count(explode(' ', $command));

                // @var array $argv
                $argv = $_SERVER['argv'];

                // start from index 1
                $argv = array_splice($argv, 1);

                // get the command argv 
                $commandArgv = array_splice($argv, 0, $commandSize);

                // check similarities
                if (strtolower(implode(' ', $commandArgv)) == strtolower($command)) :

                    // add command list to argv
                    foreach ($commandList as $option) :

                        // callback function?
                        if ($option !== null && is_callable($option)) :

                            //call closure
                            $returnValue = call_user_func($option, $argv);

                            // check if is an array
                            if (is_array($returnValue)) :

                                $_SERVER['argv'] = ['assist'];
                                // merge both
                                $_SERVER['argv'] = array_merge($_SERVER['argv'], $returnValue);

                            else:

                                // append return value
                                $_SERVER['argv'][] = $returnValue;

                            endif;

                        else:

                            // append now
                            $_SERVER['argv'][] = $option;

                        endif;

                    endforeach;

                    // break out
                    break;

                endif;

            endforeach;

        endforeach;
    }
}