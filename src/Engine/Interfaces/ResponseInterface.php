<?php
namespace Engine\Interfaces;
/**
 * @package Response Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface ResponseInterface
{
    /**
     * @method ResponseInterface code
     * @param int $code
     * @return void
     * 
     * Sets the http status code
     */
    public function code(int $code) : ResponseInterface;

    /**
     * @method ResponseInterface success
     * @param string $message
     * @param array $data
     * 
     * This prints a standard success message to the screen
     */
    public function success(string $message, array $data = []);

    /**
     * @method ResponseInterface failed
     * @param string $message
     * @param array $data
     * 
     * This prints a standard failed message to the screen
     */
    public function failed(string $message, array $data = []);

    /**
     * @method ResponseInterface warning
     * @param string $message
     * @param array $data
     * 
     * This prints a standard warning message to the screen
     */
    public function warning(string $message, array $data = []);
}