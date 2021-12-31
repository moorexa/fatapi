<?php
namespace Console\Conf\Ubuntu;
use Assist;

class UbuntuConf
{
    private static function getConsolePath()
    {
        $workingDir = $_SERVER['PWD'];
        $console = PATH_TO_CONSOLE;
        $home = preg_quote(HOME, '/');
        $console = preg_replace("/^($home)/", '', $console);
        
        $pathToConsole = $workingDir . '/' . $console;

        return $pathToConsole;
    }

    public static function mod_rewrite()
    {
        $path = self::getConsolePath();
        $workingDir = $_SERVER['PWD'];

        // read mod file
        $content = file_get_contents(__DIR__ . '/mod_rewrite.conf');

        // replace {path} with working dir
        $content = str_replace('{path}', $workingDir . '/', $content);

        // Set RIGHT owner user and owner group for apache root dir:
        Assist::runCliCommand('sudo chown -R www-data:www-data '.$workingDir . '/ 2> /dev/null');
            
        // Set RIGHT privileges for apache root dir:
        Assist::runCliCommand('sudo chmod -R 777 '.$workingDir . '/');

        // enable mod engine
        Assist::runCliCommand('sudo a2enmod rewrite');

        // save to tmp folder
        file_put_contents(__DIR__ . '/tmp/default.conf', $content);

        // copy file to /etc/apache2/
        Assist::out('Enter a name for virtual host file : (Press Enter to skip and generate a random name)');
        $virtualHostName = Assist::readLine();

        if ($virtualHostName == null)
        {
            // generate a random configuration file name
            $virtualHostName = 'default-'.time().'.conf';
        }

        if (strpos($virtualHostName, '.conf') === false)
        {
            // add .conf extension
            $virtualHostName .= '.conf';
        }

        Assist::out('Using conf : ('. $virtualHostName . ')');

        // try disable virtual host
        Assist::runCliCommand('sudo a2dissite '. $virtualHostName);

        // copy file
        $destination = '/etc/apache2/sites-available/' . $virtualHostName;
        Assist::runCliCommand('sudo cp ' . __DIR__ . '/tmp/default.conf ' . $destination);

        // enable virtual host
        Assist::runCliCommand('sudo a2ensite ' . $virtualHostName);
        
        // restart server
        Assist::runCliCommand('sudo /etc/init.d/apache2 restart');

        // done
        Assist::out('Operation complete.....');

        // delete default
        unlink(__DIR__ . '/tmp/default.conf');

        Assist::out(PHP_EOL);
    }

    public static function site_default()
    {
        $path = self::getConsolePath();

        $workingDir = $_SERVER['PWD'];

        // read mod file
        $content = file_get_contents(__DIR__ . '/site_default.conf');

        // replace {path} with working dir
        $content = str_replace('{path}', $workingDir . '/', $content);

        // Set RIGHT owner user and owner group for apache root dir:
        Assist::runCliCommand('sudo chown -R www-data:www-data '.$workingDir . '/ 2> /dev/null');
            
        // Set RIGHT privileges for apache root dir:
        Assist::runCliCommand('sudo chmod -R 777 '.$workingDir . '/');

        // copy file to /etc/apache2/
        fwrite(STDOUT, 'Enter a server name eg domain.com: ');
        $servername = Assist::readLine();

        if ($servername != '')
        {
            // replace {servername} with $servername
            $content = str_replace('{servername}', $servername, $content);

            // get server alias
            fwrite(STDOUT, 'Enter server alias eg www.domain.com (Press Enter to skip) : ');
            $serveralias = Assist::readLine();

            if ($serveralias == '')
            {
                $content = str_replace('ServerAlias {alias}', '', $content);
            }
            else
            {
                $content = str_replace('{alias}', $serveralias, $content);
            }

            // get server support email
            fwrite(STDOUT, 'Enter server support email (Press Enter to skip) : ');
            $supportemail = Assist::readLine();

            if ($supportemail == '')
            {
                $supportemail = config('public.support.email');
            }

            $content = str_replace('{email}', $supportemail, $content);

            // save to tmp folder
            file_put_contents(__DIR__ . '/tmp/default.conf', $content);

            // copy file to /etc/apache2/
            $virtualHostName = $servername . '.conf';

            Assist::out('Using conf : ('. $virtualHostName . ')');

            // try disable virtual host
            Assist::runCliCommand('sudo a2dissite '. $virtualHostName . ' 2> /dev/null');

            // copy file
            $destination = '/etc/apache2/sites-available/' . $virtualHostName;
            Assist::runCliCommand('sudo cp ' . __DIR__ . '/tmp/default.conf ' . $destination);

            // enable virtual host
            Assist::runCliCommand('sudo a2ensite ' . $virtualHostName);
            
            // restart server
            Assist::runCliCommand('sudo /etc/init.d/apache2 restart');

            // done
            Assist::out('Operation complete.....');

            // delete default
            unlink(__DIR__ . '/tmp/default.conf');

            Assist::out(PHP_EOL);
        }
        else
        {
            self::out('Invalid servername. operation ended....');
        }
    }

    public static function site_secure()
    {
        $path = self::getConsolePath();

        $workingDir = $_SERVER['PWD'];

        // read mod file
        $content = file_get_contents(__DIR__ . '/site_secure.conf');

        // replace {path} with working dir
        $content = str_replace('{path}', $workingDir . '/', $content);

        // Set RIGHT owner user and owner group for apache root dir:
        Assist::runCliCommand('sudo chown -R www-data:www-data '.$workingDir . '/ 2> /dev/null');
            
        // Set RIGHT privileges for apache root dir:
        Assist::runCliCommand('sudo chmod -R 775 '.$workingDir . '/');

        // copy file to /etc/apache2/
        fwrite(STDOUT, 'Enter a server name eg domain.com: ');
        $servername = Assist::readLine();

        if ($servername != '')
        {
            $utility = PATH_TO_UTILITY . 'Certificate/Private';
            $home = preg_quote(HOME, '/');
            $utility = preg_replace("/^($home)/", '', $utility);
            $certpath = $workingDir . '/' . $utility;
            $certpath = preg_replace('/[\/]{2,}/', '', $certpath);
            
            // replace {certpath}
            $content = str_replace('{certpath}', $certpath, $content);
            
            // replace {servername} with $servername
            $content = str_replace('{servername}', $servername, $content);

            // just a notice
            Assist::sleep('====== Notice (Ensure cerificate files reside in '.$certpath . ') and do not include a path when providing the required names below.');

            // get cerificate file
            fwrite(STDOUT, 'Enter CertificateFile name (.crt): ');
            $CertificateFile = Assist::readLine();

            fwrite(STDOUT, 'Enter CertificateKeyFile name (.key): ');
            $CertificateKeyFile = Assist::readLine();

            fwrite(STDOUT, 'Enter CertificateChainFile name (.ca-bundle): ');
            $CertificateChainFile = Assist::readLine();

            $continue = false;

            if ($CertificateFile != '' && $CertificateKeyFile != '' && $CertificateChainFile != '')
            {
                $filecount = 0;

                $files = [
                    $certpath . '/' . $CertificateFile,
                    $certpath . '/' . $CertificateKeyFile,
                    $certpath . '/' . $CertificateChainFile,
                ];

                foreach($files as $file)
                {
                    if (file_exists($file))
                    {
                        $filecount++;
                    }
                }

                if ($filecount == 3)
                {
                    $content = str_replace('{file}', $CertificateFile, $content);
                    $content = str_replace('{keyfile}', $CertificateKeyFile, $content);
                    $content = str_replace('{chainfile}', $CertificateChainFile, $content);

                    $continue = true;
                }
            }

            if ($continue)
            {
                // save to tmp folder
                file_put_contents(__DIR__ . '/tmp/default.conf', $content);

                // copy file to /etc/apache2/
                $virtualHostName = $servername . '-ssl.conf';

                Assist::out('Using conf : ('. $virtualHostName . ')');

                // try disable virtual host
                Assist::runCliCommand('sudo a2dissite '. $virtualHostName . ' 2> /dev/null');

                // copy file
                $destination = '/etc/apache2/sites-available/' . $virtualHostName;
                Assist::runCliCommand('sudo cp ' . __DIR__ . '/tmp/default.conf ' . $destination);

                // enable virtual host
                Assist::runCliCommand('sudo a2ensite ' . $virtualHostName);
                
                // restart server
                Assist::runCliCommand('sudo /etc/init.d/apache2 restart');

                // done
                Assist::out('Operation complete.....');

                // delete default
                unlink(__DIR__ . '/tmp/default.conf');

                Assist::out(PHP_EOL);
            }
            else
            {
                self::out('1 or more certificate file missing. Please check and try again. Operation ended....');
            }
        }
        else
        {
            self::out('Invalid servername. operation ended....');
        }
    }
}   