<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *     -    all
 */

$start =
    dirname(__DIR__) .
    DIRECTORY_SEPARATOR .
    'Module' . DIRECTORY_SEPARATOR .
    'Autoload' . DIRECTORY_SEPARATOR .
    'Class'  . DIRECTORY_SEPARATOR .
    'Autoload.class.php';
$debug =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    'Function' .
    DIRECTORY_SEPARATOR .
    'Function.Debug.php';

require_once $start;
require_once $debug;

$autoload = new \Priya\Module\Autoload();
$autoload->addPrefix('Priya',  dirname(__DIR__) . DIRECTORY_SEPARATOR);
$autoload->register();