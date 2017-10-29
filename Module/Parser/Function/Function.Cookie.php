<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_cookie($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = array_shift($argumentList);
    $value = array_shift($argumentList);
    $duration = array_shift($argumentList);
    $function['execute'] = $parser->cookie($attribute, $value, $duration);
    return $function;
}
