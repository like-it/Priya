<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

//namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\File;
use Priya\Application;

function function_require(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){    
    $url = key_exists(0, $parameter) ? $parameter[0] : null;   
    if(File::exist($url)){
        $read = $parse->read($url);
        var_dump($read);
        die;
    }
    $execute = null;   
    return $execute;    
}
