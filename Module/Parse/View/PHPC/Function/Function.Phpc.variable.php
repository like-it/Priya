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

function function_phpc_variable(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){   
    $result = [];  
    $type = key_exists(0, $parameter) ? $parameter[0] : null;    
    if($type === null){
        $private = function_phpc_variable($parse, ['private'], $token, $method);
        $protected = function_phpc_variable($parse, ['protected'], $token, $method);
        $public = function_phpc_variable($parse, ['public'], $token, $method);
        
        if($private !== null){
            $result [] = $private;
            // $variable[] = '';            
        }
        if($protected !== null){
            $result [] = $protected;
            // $variable[] = '';            
        }
        if($public !== null){
            $result[] = $public;
            // $variable[] = '';            
        }        
    } else {
        switch($type){
            case 'private' :
            break;
            case 'protected' :            
                $result[] = Compile::protected_name($parse, 'parse');
                $result[] = Compile::protected_name($parse, 'token');           
                $result[] = PHP_EOL;     
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

