<?php 
namespace Classes\Cli;
/**
 * @package Cli Interface
 * @author Amadi Ifeanyi <amadiify.com>
 */
interface CliInterface
{
    /**
     * @method CliInterface loadBash
     * @return array
     */
    public static function loadBash() : array;
}