<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_token($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $token = array_shift($argumentList);
    if($token === null){
        $token = $string;
        $function['execute'] = strtok($token);
    } else {
        $function['execute'] = strtok($string, $token);
    }
    return $function;
}