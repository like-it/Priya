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
use Priya\Module\Parse\Token;
use Priya\Module\Parse\Compile;

use Priya\Module\Parse\PHPC;

function function_phpc_static(
    Parse $parse,
    $parameter = [],
    &$token = [],
    $method = []
){   
    $result = [];  
    $type = key_exists(0, $parameter) ? $parameter[0] : null;    
    $data = key_exists(1, $parameter) ? $parameter[1] : [];        
    if($type === null){
        $private = function_phpc_static($parse, ['private', $data], $token, $method);
        $protected = function_phpc_static($parse, ['protected', $data], $token, $method);
        $public = function_phpc_static($parse, ['public', $data], $token, $method);
        
        if($private !== null){
            $result[] = $private;                
        }
        if($protected !== null){
            $result[] = $protected;                   
        }
        if($public !== null){
            $result[] = $public;                   
        }        
    } else {
        switch($type){
            case 'private' :
            break;
            case 'protected' :                        
            break;
            case 'public' :                       
                $result[] = PHPC::create_static_public_data($parse, [ $data ], $token, $method);                                  
                $result[] = create_static_public_execute($parse, [ $data ], $token, $method);
                $result[] = '';
            break;
        }
    }
    
    if(empty($result)){
        return null;
    }    
    return implode(PHP_EOL, $result);
}

function create_static_public_execute(
    Parse $parse,    
    $parameter = [],
    &$token = [],
    $method = []
){      
    $indent = $parse->data(Compile::DATA_INDENT);
    $result[] = Compile::string_tab($indent) . 'public static function execute(Parse $parse){';       
        
    $data = key_exists(0, $parameter) ? $parameter[0] : [];        
    $parse->data(Compile::DATA_INDENT, $indent + 1);
    $parse->data(PHPC::DATA_CONVERSION_ADD, true);
    $conversion = PHPC::conversion_data($parse, [ $data ], $token, $method);

    // $data  = $parse->data(Parse::DATA_TOKEN);    
    
    $result[] = Compile::require_method($parse, $parse->data(Compile::DATA_METHOD), Compile::string_tab($indent + 1), 'Parse');
    $result[] = Compile::require_modifier($parse, $parse->data(Compile::DATA_MODIFIER), Compile::string_tab($indent + 1), 'Parse');

    // $result[] = Compile::string_tab(1) . '$data = [];';
    foreach($conversion as $nr => $record){
        $result[] = $record;
    }
    $result[] = PHP_EOL;
    // $result[] = Compile::string_tab(1) . 'return implode(\'\', $data);';
    $parse->data(Compile::DATA_INDENT, $indent);
    $result[] = Compile::string_tab($indent) . '}';            
    return implode(PHP_EOL, $result);
}
