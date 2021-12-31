<?php
/** @noinspection All */
/**
 * @package Deploy Remote Project
 * @version 0.0.1
 * @author Ifeanyi Amadi <amadiify.com>
 * 
 * Allows a POST Request then extracts zip files and folders to server root directory
 * Using Moorexa, run "php assist deploy" from the terminal. 
 * Using Deploy cli, run "php deploy.php run" from the terminal.
 * Ensure this script exists on your server
 */

 // DEFINE ROOT
 define ('__ROOT__', '.');

 // define GLOB_BRACE
 if(!defined('GLOB_BRACE')) define('GLOB_BRACE', 128);

 // class definition here.. 
 class DeployProject
 {
    // @var $remote_address (optional)
    public $remote_address = ''; 

    // @var $requestID 
    // set a unique requestID
    public $requestID = '';

    // @var $requestHeader
    // set a unique request header
    public $requestHeader = 'X-Deploy-ID';

    // @var $options
    private $options = ['deploy', 'rollback', 'getall'];

    // @var $uploadName
    public $uploadName = 'deployZip';

    // base directory
    public $basedir = __ROOT__ . '/deploy/';

    // current version
    public $version = '0.0.1';

    // authenticate request
    public function authenticateRequest()
    {
        // get all headers
        $headers = getallheaders();

        foreach ($headers as $key => $val)
        {
            $key = strtolower($key);
            if (strtolower($this->requestHeader) == $key)
            {
                if ($val == $this->requestID)
                {
                    return true;
                }

                break;
            }
        }

        $this->failed("Authentication Failed. Invalid {$this->requestHeader}");

        return false;
    }

    // request option
    public function requestOption()
    {
        set_time_limit(0);

        $post = $_POST;
        
        if (count($post) == 0 && count($_FILES) > 0)
        {
            if (isset($_FILES[$this->uploadName]))
            {
                $post['option'] = 'deploy';
            }
        }
        
        if (isset($post['option']))
        {
            $option = strtolower($post['option']);
            $options = array_flip($this->options);

            if (isset($options[$option]))
            {
                $this->{$option}();

                return true;
            }

            $this->failed("Request Option not valid.");
        }

        $this->failed("Request Option missing.");

    }

    // show text and sleep for 100000 milliseconds.
    protected static function sleep($text)
    {
        fwrite(STDOUT, $text . PHP_EOL);
        usleep(100000);
    }

    // RUN From cli
    public static function runCli($args)
    {
        // get instance of deploy class
        $instance = new DeployProject();

        if (!defined('HOME'))
        {
            // define home dir
            define('HOME', __ROOT__ . '/');
        }

        // get command 
        $command = $args[0] ?? null;

        // get other
        $options = array_splice($args, 1);

        // ensure command was sent
        if (!is_null($command))
        {
            // continue
            switch (strtolower($command))
            {
                // deploy
                case 'run': 
                    self::out("Deploy to Production server\n");

                    // get configuration.
                    $address = strlen($instance->remote_address) > 4 ? $instance->remote_address : null;
                    $requestID = $instance->requestID;
                    $requestHeader = $instance->requestHeader;
                    $uploadName = $instance->uploadName;

                    if (filter_var($address, FILTER_VALIDATE_URL))
                    {
                        $files = [];
    
                        $zip = new \ZipArchive();

                        $hash = md5($address);
            
                        $zipfile = HOME . 'utility/Storage/Tmp/deploy'.time().'.zip';
                        $logfile = HOME . 'utility/Storage/Logs/Deploy/deploylog'.$hash.'.json';

                        $other = null;
                        $haslognew = false;

                        // check directory
                        if (!is_dir(HOME . 'utility/Storage'))
                        {
                            $zipfile = $instance->basedir . 'deploy'.time().'.zip';
                            $logfile = $instance->basedir . 'deploylog'.$hash.'.json';

                            // make directory if it doesn't exists.
                            if (!is_dir($instance->basedir))
                            {
                                mkdir($instance->basedir, 0777);
                            }
                        }

                        $_allfiles = 0;
                        $notrack = false;

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
                                            $dir = HOME . $dr;
                                        
                                            if (is_dir($dir) || file_exists($dir))
                                            {
                                                // save zip file.
                                                $instance->saveZipFile($zip, $zipfile, $logfile, $haslognew, $log, $other, $allfiles, $dir);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        if (!file_exists($zipfile))
                        {
                            $instance->saveZipFile($zip, $zipfile, $logfile, $haslognew, $log, $other, $allfiles);
                        }

                        // deploy now
                        if (file_exists($zipfile))
                        {
                            self::out("[POST]"." Authenticating with Remote Server\n");
                            
                            if (filter_var($address, FILTER_VALIDATE_URL))
                            {
                                $url = rtrim($address, 'deploy.php');
                                $url = rtrim($url, '/') . '/deploy.php';

                                if ($haslog)
                                {
                                    $url .= '?mode=add-replace';
                                }

                                $url .= '?size='.$allfiles;

                                if ($notrack)
                                {
                                    $url .= '&notrack=true';
                                }

                                //$url .= $other;
                                $mime = mime_content_type($zipfile);

                                if (class_exists('CURLFile'))
                                {
                                    $cfile = new CURLFile(realpath($zipfile));
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
                                    $instance->uploadName => $cfile
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
                                "{$instance->requestHeader}: {$instance->requestID}",
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
                                $filesize = $instance->convertToReadableSize($size);

                                self::sleep("[POST] Uploading ($filesize)..\n");

                                $run = curl_exec($ch); 

                                if (curl_errno($ch))
                                {
                                    $msg = curl_error($ch);
                                }
                                
                                // delete zip file
                                unlink($zipfile);

                                $data = json_decode($run);

                                if (is_object($data))
                                {
                                    if ($data->status == 'success')
                                    {
                                        self::out("Complete! ".$data->message);

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
                                        self::out("Failed! ".$data->message);
                                    }
                                }
                                else
                                {
                                    $run = strip_tags($run);
                                    self::out("Operation canceled. An error occured." . " $msg". ' '.$run);
                                }
                            }
                            else
                            {
                                self::out("Invalid Remote Address '{$address}/'");
                            }
                        }
                        else
                        {
                            self::out('Operation ended. Couldn\'t generate project zip file.');
                        }

                    }
                    else
                    {
                        self::out('Invalid Remote Address ('.$address.')');
                    }

                break;

                // rollback
                case 'rollback':
                    
                    self::out("Rollback Transaction on Production server\n");

                    // get configuration.
                    $address = strlen($instance->remote_address) > 4 ? $instance->remote_address : null;
                    $requestID = $instance->requestID;
                    $requestHeader = $instance->requestHeader;

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
                        "{$instance->requestHeader}: {$instance->requestID}",
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
                                self::out("Complete! ".$data->message);
                            }
                            else
                            {
                                self::out("Failed! ".$data->message);
                            }
                        }
                        else
                        {
                            $run = strip_tags($run);
                            self::out("Operation canceled. An error occured." . " $msg". ' '.$run);
                        }
                    }
                    else
                    {
                        self::out('Invalid Remote Address ('.$address.')');
                    }
                    
                break;

                // generate key
                case 'genkey':
                    // get a new key
                    $string = implode('|',array_values($_SERVER));
                    $key = $instance->generateID(time() . __ROOT__ . '/deploy/token='.sha1(uniqid().$string));
                    // save token
                    $content = file_get_contents(__ROOT__ . '/deploy.php');
                    $build = '$requestID = \''.$key.'\';';
                    $replace = '$requestID = \''.$instance->requestID.'\';';
                    // replace old token with newly generated token
                    $content = str_replace($replace, $build, $content);
                    // save 
                    file_put_contents(__ROOT__ . '/deploy.php', $content);
                    // all done.
                    self::out('Deploy Token generated successfully and saved as the default token.');
                break;

                // help
                case 'help':
                    // generates a quick help.
                    self::out('Deploy.php helps deploy project to production server. Written by Ifeanyi Amadi https://www.amadiify.com');
                    self::out('You are running on v'.$instance->version);
                    self::out('==============='."\n"."## Commands \n===============");
                    self::out("1. run : start deploy process. you have to set the remote_url and also move a copy of deploy.php to the root of your production server.
                    \n2. rollback : Rollback a transaction. you may specify which transaction to rollback to.
                    \n3. genkey : generates a new token for authentication 
                    \n4. help : help screen 
                    \n5. update : Updates deploy.php to most recent version without breaking your application");
                    self::out('==============='."\n"."## Options \n===============");
                    self::out("1. run : php deploy.php run -<directory-name>|-<filename> (This deploys everything inside the directory)
                    \n2. run : php deploy.php run (This deploys updated files or entire code base)");
                break;

                // update deploy.php
                case 'update':

                    self::out("Update Script from Repo.\n");

                    // get current version
                    $version = $instance->version;

                    // check repo
                    $repo = 'https://www.amadiify.com/repo/-update-cli/deploy';

                    // make request
                    $ch = curl_init($repo);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $run = curl_exec($ch);

                    if (curl_errno($ch))
                    {
                        $msg = curl_error($ch);
                    }

                    $data = json_decode($run);

                    // is object
                    if (is_object($data))
                    {
                        // get status
                        $status = $data->status;

                        // success
                        switch ($status)
                        {
                            case 'success':
                                //get content
                                $content = $data->content;
                                $changelog = $data->changelog;
                                // update deploy.php
                                file_put_contents(HOME . 'deploy.php', $content);
                                // include deploy
                                include_once HOME . 'deploy.php';
                                // create instance
                                $c = new DeployProject();
                                // get new key
                                $key = $c->requestID;
                                $remote_address = $c->remote_address;

                                $build = '$requestID = \''.$key.'\';';
                                $replace = '$requestID = \''.$instance->requestID.'\';';

                                // remote address
                                $remotebuild = '$remote_address = \'\';';
                                $remotereplace = '$remote_address = \''.$instance->remote_address.'\';';

                                // replace old token with newly generated token
                                $content = str_replace($build, $replace, $content);
                                $content = str_replace($remotebuild, $remotereplace, $content);

                                // update now
                                file_put_contents(HOME . 'deploy.php', $content);

                                if (strlen($changelog) > 3)
                                {
                                    file_put_contents(HOME . 'DeployChangeLog.md', $changelog);
                                }

                                self::out('You are now running on version '.$data->version);
                            break;

                            case 'error':
                                self::out('An error occured : '. $data->message);
                            break;
                        }
                    }
                    else
                    {
                        self::out('Update failed. '. $msg);
                    }
                    
                break;
            }
        }
        else
        {
            self::out('Invalid action. try deploy,rollback,genkey,help');
        }

        fwrite(STDOUT, PHP_EOL);
    }

    // get deploy zip file
    private function getDeployZipFile(&$zipfiles, &$haslognew, &$log, &$allfiles=[], $dir)
    {
        if (is_dir($dir))
        {
            $data = glob(rtrim($dir, '/') .'/{,.}*', GLOB_BRACE);

            foreach ($data as $i => $f)
            {
                if (basename($f) != '.' && basename($f) != '..')
                {
                    if (is_file($f))
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
                    
                    elseif (is_dir($f) && basename($f) != 'backup')
                    {
                        $dr = $this->getAllFiles($f);

                        $single = $this->reduce_array($dr);

                        if (count($single) > 0)
                        {
                            foreach ($single as $z => $d)
                            {
                                if ($dir == HOME)
                                {
                                    if (!isset($log[$d]))
                                    {
                                        $zipfiles[] = $d;
                                        $log[$d] = filemtime($d);
                                    }
                                    else
                                    {
                                        // check filemtime
                                        $fmtime = filemtime($d);
                                        if ($log[$d] != $fmtime)
                                        {
                                            $zipfiles[] = $d;
                                            $log[$d] = $fmtime;
                                        }
                                    }
                                }
                                else
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
        elseif (is_file($dir))
        {
            $zipfiles[] = $dir;
            $log[$dir] = filemtime($dir);   
            $allfiles[] = $dir;
        }
    }

    // get deploy zipfile
    public function saveZipFile(&$zip, &$zipfile, &$logfile, &$haslognew, &$log, &$other, &$_allfiles, $dir = __ROOT__ . '/')
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

            if (count($del) > 0)
            {
                $sym = $haslog === true ? '&' : '?';
                $other = $sym.'del='.json_encode($del);
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

    // flush output
    private static function out($message)
    {
        fwrite(STDOUT, "\n". $message . "\n");
    }

    // failed output
    private function failed( $text = "Request failed" )
    {
        static $outputSent;

        if ($outputSent === null)
        {
            // returns a json output
            echo json_encode(['status' => 'failed', 'message' => $text]);
            $outputSent = true;
        }
    }  
    
    // success output
    private function success( $text = "Request was a success" )
    {
        static $outputSent;

        if ($outputSent === null)
        {
            // returns a json output
            echo json_encode(['status' => 'success', 'message' => $text]);
            $outputSent = true;
        }
    }  

    //  generate requestID
    private function generateID($text)
    {
        return password_hash(hash('sha256', md5($text)), PASSWORD_BCRYPT);
    }

    private function getall($dir = __ROOT__)
    {
        $glob = glob($dir . '/{,.}*', GLOB_BRACE);
        $files = [];

        foreach ($glob as $i => $f)
        {
            if ($f != '.' && $f != '..')
            {
                if (is_dir($f))
                {
                    $dir = $this->getall($f);
                }
                elseif(is_file($f))
                {
                    $files[] = $f;
                }
            }
        }

        return $files;
    }

    // deploy option
    private function deploy()
    {
        $zipfile = isset($_FILES[$this->uploadName]) ? $_FILES[$this->uploadName] : null;

        // do we have a file to deploy?
        if (!is_null($zipfile))
        {
            $name = $zipfile['name'];
            $tmp = $zipfile['tmp_name'];
            $error = $zipfile['error'];

            $mode = isset($_GET['mode']) ? $_GET['mode'] : 'backup';

            if (isset($_GET['del']))
            {
                $mode = 'delete';
            }

            if ($error == 0)
            {
                // back up previous contents
                if (!is_dir(__ROOT__ . '/deploy-track'))
                {
                    mkdir(__ROOT__ . '/deploy-track', 0755, true);
                }

                $notrack = isset($_GET['notrack']) ? $_GET['notrack'] : false;

                if (is_dir(__ROOT__ . '/deploy-track'))
                {
                    // get extension
                    $split = explode('.', $name);
                    $extension = end($split);

                    if (strtolower($extension) == 'zip')
                    {
                        if ($mode == 'backup' && $notrack == false)
                        {
                            
                            //  try to backup 
                            $zip = new ZipArchive();

                            $filename = 'deploy'.(isset($_GET['size']) ? $_GET['size'] : $zipfile['size']).'.zip';

                            $zipfile = __ROOT__ . '/deploy-track/'. $filename;

                            $files = 0;
                    
                            if ($zip->open($zipfile, ZipArchive::CREATE) === true)
                            {
                                $data = glob(__ROOT__ .'/{,.}*', GLOB_BRACE);
                                
                                foreach ($data as $i => $f)
                                {
                                    if (basename($f) != '.' && basename($f) != '..')
                                    {
                                        if (is_file($f) && basename($f) != 'deploy.php')
                                        {
                                            $zip->addFile($f);
                                            $files++;
                                        }
                                        elseif (is_dir($f) && basename($f) != 'deploy-track')
                                        {
                                            $dr = $this->getAllFiles($f);
                        
                                            $single = $this->reduce_array($dr);
                        
                                            if (count($single) > 0)
                                            {
                                                foreach ($single as $z => $d)
                                                {
                                                    $files++;
                                                    $zip->addFile($d);
                                                }
                                            }
                                            
                                        }
                                    }
                                }
                                
                                $zip->close();
                            }   

                            $continue = false;

                            if (file_exists($zipfile))
                            {
                                $continue = true;
                            }
                            elseif ($files == 0)
                            {
                                $continue = true;
                            }
                            
                            
                        }
                        else
                        {
                            $continue = true;
                        }

                        // check if backup was successful
                        if ($continue)
                        {
                            // ok move uploaded file and extract it
                            move_uploaded_file($tmp, __ROOT__ . '/deploy-track/'.$name);

                            $zip = new ZipArchive();
                            $res = $zip->open(__ROOT__ . '/deploy-track/'.$name);

                            if ($res === true)
                            {
                                if (isset($_GET['del']))
                                {
                                    $del = json_decode(trim($_GET['del']));

                                    if (is_object($del))
                                    {
                                        foreach ($del as $index => $path)
                                        {
                                            if (file_exists($path) && is_writable($path))
                                            {
                                                unlink($path);
                                            }
                                        }
                                    }
                                }

                                $zip->extractTo(__ROOT__);

                                if ($zip->close())
                                {
                                    if (is_writable(__ROOT__ . '/deploy-track/'.$name))
                                    {
                                        unlink(__ROOT__ . '/deploy-track/'.$name);
                                    }

                                    $this->success("Deployment was successful.");
                                }

                                return true;
                            }

                            $this->failed("Upload failed. Please try again. Ensure file is a valid zip file");
                        }

                        $this->failed("Backup Failed. Please ensure deploy.php and /deploy-track has full write access.");
                    }

                    $this->failed("Invalid File extention. Must be a 'zip' file.");
                }

                $this->failed('Failed to Create directory '. __ROOT__ . '/deploy-track');
                
            }

            $this->failed("Error occured! File too large!");
        }

        $this->failed("No file with {$this->uploadName} to deploy.");
    }

    // rollback option
    private function rollback()
    {
        $deploy = isset($_POST['deploy']) ? $_POST['deploy'] : null;

        if ($deploy !== null)
        {
            if (strpos(strtolower($deploy), '.zip') === false)
            {
                $deploy .= '.zip';
            }
        }
        else
        {
            // get the most recent backup
            $all = glob(__ROOT__. '/deploy-track/*');
            $vers = [];
            foreach ($all as $i => $o)
            {
                if (basename($o) != '.' && basename($o) != '..')
                {
                    if (is_file($o))
                    {
                        $file = stat($o);
                        $stamp = $file['ctime'];
                        $vers[$stamp] = basename($o);
                    }
                }
            }

            if (count($vers) > 0)
            {
                $max = max(array_keys($vers));
                $deploy = $vers[$max];
            }
        }   

        $file = __ROOT__. '/deploy-track/'. $deploy;

        if (is_file($file) && file_exists($file))
        {
            $zip = new ZipArchive();
            $res = $zip->open($file);

            if ($res === true)
            {
                // ok try to delete files and folders
                $data = glob(__ROOT__ .'/{,.}*');
                    
                foreach ($data as $i => $f)
                {
                    if (basename($f) != '.' && basename($f) != '..')
                    {
                        if (is_file($f) && basename($f) != 'deploy.php')
                        {
                            if (is_writable($f))
                            {
                                @unlink($f);
                            }
                        }
                        elseif (is_dir($f) && basename($f) != 'deploy-track')
                        {
                            $dr = $this->getAllFiles($f);

                            $single = $this->reduce_array($dr);

                            if (count($single) > 0)
                            {
                                foreach ($single as $z => $d)
                                {
                                    if (is_writable($d))
                                    {
                                        @unlink($d);
                                    }
                                }
                            }

                            if (is_writable($f))
                            {
                                @unlink($f);
                            }
                        }
                    }
                }

                $zip->extractTo(__ROOT__);

                if ($zip->close())
                {
                    $this->success("Rollback was successful.");

                    if (is_writable($file))
                    {
                        @unlink($file);
                    }

                    return true;
                }

                $this->failed("Rollback failed! Extraction wasn't successful");
            }

            $this->failed("Zip file '$file' isn't a valid file. Rollback operation cancelled.");
        }

        $this->failed("Rollback failed! File $deploy not found");
    }

    // Helper functions 
    private function getAllFiles($dir)
    {
        $files = [];

        $files = $this->___allfiles($dir);

        return $files;
    }

    private function ___allfiles($dir)
    {
        $file = [];

        $dir = rtrim($dir, '/');

        $glob = glob($dir.'/{,.}*', GLOB_BRACE);

        if (is_array($glob) && count($glob) > 0)
        {
            foreach ($glob as $i => $p)
            {
                if (basename($p) != '.' && basename($p) != '..')
                {
                    $p = preg_replace("/[\/]{2}/", '/', $p);

                    if (is_file($p))
                    {
                        $file[] = $p;
                    }
                    elseif (is_dir($p))
                    {
                        $file[] = $this->___allfiles($p);
                    }
                }
            }
        }

        //$glob = null;

        return $file;
    }

    private function reduce_array($array)
    {	
        $arr = [];
        $arra = $this->__reduceArray($array, $arr);

        return $arra;
    }

    private function __reduceArray($array, $arr)
    {

        if (is_array($array))
        {
            foreach ($array as $a => $val)
            {
                if (!is_array($val))
                {
                    $arr[] = $val;
                }
                else
                {
                    foreach($val as $v => $vf)
                    {
                        if (!is_array($vf))
                        {
                            $arr[] = $vf;
                        }
                        else
                        {
                            $arr = $this->__reduceArray($vf, $arr);
                        }
                    }
                }
            }
        }

        return $arr;
    }

    private function convertToReadableSize($size){
        $base = log($size) / log(1024);
        $suffix = array("Byte", "KB", "MB", "GB", "TB");
        $f_base = floor($base);
        $convert = round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    
        if ($convert > 0)
        {
            return $convert;
        }
    
        return 0 . 'KB';
    }
    // -end helper functions.
 }

if (isset($_SERVER['REQUEST_METHOD'])) :

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === "POST")
    {
        // send content type
        header("Content-Type: application/json");

        // create a fresh instance
        $deploy = new DeployProject();
        
        // authenticate user
        if ($deploy->authenticateRequest())
        {
            // good
            $deploy->requestOption();
        }
    }
    else
    {
        // run for terminal.
        if (substr(php_sapi_name(), 0, 3) == 'cli')
        {
            // call method
            $argv = $_SERVER['argv'];

            // remove deploy.php
            array_shift($argv);

            // run interface.
            DeployProject::runCli($argv);
        }
    }
    
endif;