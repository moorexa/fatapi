<?php
namespace Engine;
/**
 * @package Response
 * @author Amadi Ifeanyi <amadiify.com>
 */
class Response implements Interfaces\ResponseInterface
{
    /**
     * @var int $statusCode
     */
    private $statusCode = 0;

    /**
     * @method ResponseInterface code
     * @param int $code
     * @return void
     * 
     * Sets the http status code
     */
    public function code(int $code) : Response
    {
        http_response_code($code);

        // set the code
        $this->statusCode = $code;

        // return class
        return $this;
    }

    /**
     * @method ResponseInterface success
     * @param string $message
     * @param array $data
     * 
     * This prints a standard success message to the screen
     */
    public function success(string $message, array $data = [])
    {
        // set status code
        if ($this->statusCode == 0) $this->code(200);

        // build data
        $data = array_merge([
            'Status'    => true,
            'Code'      => $this->statusCode,
            'Flag'      => 'RES_SUCCESS',
            'Message'   => $message
        ], $data);

        // show message
        app('screen')->render($data);

        // return class
        return $this;
    }

    /**
     * @method ResponseInterface failed
     * @param string $message
     * @param array $data
     * 
     * This prints a standard failed message to the screen
     */
    public function failed(string $message, array $data = [])
    {
        // set status code
        if ($this->statusCode == 0) $this->code(404);

        // build data
        $data = array_merge([
            'Status'    => false,
            'Code'      => $this->statusCode,
            'Flag'      => 'RES_FAILED',
            'Message'   => $message
        ], $data);

        // show message
        app('screen')->render($data);

        // return class
        return $this;
    }

    /**
     * @method ResponseInterface warning
     * @param string $message
     * @param array $data
     * 
     * This prints a standard warning message to the screen
     */
    public function warning(string $message, array $data = [])
    {
        // set status code
        if ($this->statusCode == 0) $this->code(401);

        // build data
        $data = array_merge([
            'Status'    => true,
            'Code'      => $this->statusCode,
            'Flag'      => 'RES_WARNING',
            'Message'   => $message
        ], $data);

        // show message
        app('screen')->render($data);

        // return class
        return $this;
    }

    /**
     * @method Response save
     * @return void
     * 
     * This saves the response body to the responses folder for that service method.
     */
    public function save()
    {
        // get content
        $content = ob_get_contents();

        // do we have what to save??
        if ($content != null && strlen($content) > 0) :

            // ok get path
            $documentationPath = CURRENT_VERSION_PATH . '/Documentation/';

            // create responses folder
            $responsesFolder = $documentationPath . 'Responses/';

            // create folder
            if (!is_dir($responsesFolder)) mkdir($responsesFolder);

            // build method folder
            $methodFolder = $responsesFolder . CURRENT_SERVICE_METHOD_CALLED . '/';

            // create method folder
            if (!is_dir($methodFolder)) mkdir($methodFolder);

            // push data by code
            $destination = $methodFolder . $this->statusCode . '.md';

            // save content
            file_put_contents($destination, $content);

        endif;
    }
}