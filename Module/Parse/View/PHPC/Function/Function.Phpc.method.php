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

function function_phpc_method(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){   
    $result = [];  
    $type = key_exists(0, $parameter) ? $parameter[0] : null;    
    if($type === null){
        $private = function_phpc_method($parse, ['private'], $token, $method);
        $protected = function_phpc_method($parse, ['protected'], $token, $method);
        $public = function_phpc_method($parse, ['public'], $token, $method);
                
        if($private !== null){
            $result[] = $private;
            $result[] = PHP_EOL;
            // $method[] = '';            
        }
        if($protected !== null){
            $result[] = $protected;
            $result[] = PHP_EOL;
            // $method[] = '';            
        }
        if($public !== null){
            $result[] = $public;
            $result[] = PHP_EOL;
            // $method[] = '';            
        }            
    } else {
        switch($type){
            case 'private' :
            break;
            case 'protected' :                        
            break;
            case 'public' :
            break;            
        }        
    }
    if(empty($result)){
        return null;
    }        
    return implode(PHP_EOL, $result);
}
