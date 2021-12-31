<?php
namespace Classes\Platforms\Launchers;

use Lightroom\Requests\Headers;
use Lightroom\Packager\Moorexa\Helpers\RouterControls;
/**
 * @package Platform helper
 * @author Amadi Ifeanyi <amadiify.com>
 */
trait Helper 
{
    // use headers
    use Headers, RouterControls;

    /**
     * @method Helper hasHeaders
     * @return bool
     */
    public function hasHeaders() : bool 
    {
        // @var int $found
        $found = 0;

        // get all headers
        $headers = $this->all();

        // check headers
        foreach ($this->headers_list as $header => $headerValue) :
 
            // update header
            $header = strtolower($header);

            // check if header exists
            if (isset($headers[$header]) && strtolower($headers[$header]) == strtolower($headerValue)) $found++;

        endforeach;

        // return bool
        return $found == count($this->headers_list) ? true : false;
    }

    /**
     * @method Helper loadContentType
     * @param string $platform
     * @return string
     */
    public function loadContentType(string $platform) : string 
    {
        // default content type
        $contentType = 'text/html';

        // load configuration
        $config = self::loadConfig();

        // check for launcher
        if (isset($config['launcher'])) :

            // check for content type
            if (isset($config['launcher']['contentType'])) :

                // check if content type for platform has been set
                if (isset($config['launcher']['contentType'][$platform])) :

                    // update content type
                    $contentType = $config['launcher']['contentType'][$platform];

                endif;

            endif;

        endif;

        // return content type
        return $contentType;
    }

    /**
     * @method Helper generateToken
     * @param string $salt 
     * @return string
     * 
     * Generate a new token for your platform request. This was added for your convinence
     */
    private function generateToken(string $salt) : string 
    {
        // @var string $devsalt
        $devsalt = str_shuffle('fda282295adb2e1d28627dac27e521e3b814934c');

        // generate token salt
        $tokenSalt = hash('sha256', md5($salt) . '\\' . $devsalt);

        // get salt strength
        $length = strlen($salt);
        $length = $length > 26 ? 26 : $length;

        // add extra characters
        $char = range('A','Z');

        // add digits
        $digits = range(1, ($length == 1 ? 2 : $length));

        // create a salt string
        $string = '$'.implode("", array_splice($char, 0, $length)) . '$' . implode("", array_splice($digits, 0, $length));

        // return token string
        return sha1($string.'$'.$salt . '@appsalt:'. $tokenSalt . '@devsalt:' . $devsalt);
    }
}