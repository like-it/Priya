<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 * multiple parameters uses a logical AND
 */

use Priya\Module\Parse;
use Priya\Module\Parse\Token;

function function_echo(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){      
    if(!is_array($parameter)) {
        return;        
    }       
    $execute = ''; 
    if(key_exists(0, $parameter)){
        foreach($parameter as $nr => $argument){
            $execute .= (string) $argument;
            echo ((string) $argument);
        }
    }            
    return $execute;
}