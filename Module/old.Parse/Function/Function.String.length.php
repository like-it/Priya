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

function function_string_length(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){

    if(
        !isset($method['method']) ||
        !isset($method['token']) ||
        !isset($method['token']['nr'])
    ){
        return $token;
    }
    if($method['method']['name'] != 'string.length'){
        return $token;
    }
    $length = 0;
    if(isset($method['method']['parameter'][0])){
        foreach($method['method']['parameter'] as $nr => $parameter){
            $length += strlen($parameter);
        }
    }
    $method['execute'] = $length;
    $method['type'] = Token::TYPE_INT;
    $token[$method['token']['nr']] = $method;
    return $token;
}