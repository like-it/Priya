<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 *
 */

// namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\Parse\Compile;

function function_phpc_require(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){            
    $require = $parse->data(Parse::DATA_FUNCTION);    
    $result = [];
    /*
    if(is_array($require)){
        foreach($require as $nr => $url){
            $result[] = 'require_once \'' . $url . '\';';
        }
    }
    $require = $parse->data(Parse::DATA_MODIFIER);  
    if(is_array($require)){
        foreach($require as $nr => $url){
            $result[] = 'require_once \'' . $url . '\';';
        }
    } 
    */     
    if(empty($result)){
        return null;
    }
    return implode(PHP_EOL, $result);
}
