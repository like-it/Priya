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

function function_break(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){
    if(
        !isset($method['method']) ||
        !isset($method['token']) ||
        !isset($method['token']['nr'])
    ){
        return $token;
    }
    if($method['method']['name'] != 'break'){
        return $token;
    }

    if(isset($method['method']['parameter'][0])){
        $amount = $method['method']['parameter'][0];
    } else {
        $amount = 1;
    }
    if($amount > $parse->data('priya.parse.break.level')){
        $amount = $parse->data('priya.parse.break.level');
    }
    $parse->data('priya.parse.halt', true);
    $parse->data('priya.parse.break.amount', $amount);
    $parse->data('priya.parse.break.nr', $method['token']['nr']);
    $method['type'] = Token::TYPE_NULL;
    $method['execute'] = null;
    $method['is_executed'] = true;
    $token[$method['token']['nr']] = $method;
    return $token;
}