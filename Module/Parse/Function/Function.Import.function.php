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
use Priya\Module\File\Dir;
use Priya\Application;

function function_import_function(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){    
    $name = key_exists(0, $parameter) ? $parameter[0] : null;
    $from = key_exists(1, $parameter) ? $parameter[1] : null;

    if(Dir::exist($name)){
        if(substr($name, -1, 1) != Application::DS){
            $name .= Application::DS;
        }
        $function = $parse->data(Parse::DATA_DIR_FUNCTION);
        $function[] = $name;
        $parse->data(Parse::DATA_DIR_FUNCTION, $function);        
    }
    $execute = null;   
    return $execute;    
}
