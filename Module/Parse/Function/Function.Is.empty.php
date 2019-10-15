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
use Priya\Module\Parse\Token;

function function_is_empty(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){

    if(
        !isset($method['method']) ||
        !isset($method['token']) ||
        !isset($method['token']['nr'])
    ){
        return $token;
    }
    if($method['method']['name'] != 'is.empty'){
        return $token;
    }
    $is_empty = true;
    if(!isset($method['method']['parameter'][0])){
        $method['execute'] = $is_empty;
    } else {
        foreach($method['method']['parameter'] as $nr => $value){
            if(!empty($value)){
                $is_empty = false;
                break;
            }
        }
        $method['execute'] = $is_empty;
    }
    $method['type'] = Token::TYPE_BOOLEAN;
    $token[$method['token']['nr']] = $method;
    return $token;
}