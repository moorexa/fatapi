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
    }
}