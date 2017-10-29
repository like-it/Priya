<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_split($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $length = array_shift($argumentList);
    if(empty($length)){
        $function['execute'] = str_split($string);
    } else {
        $function['execute'] = str_split($string, $length);
    }
    return $function;
}
