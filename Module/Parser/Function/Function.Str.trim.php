<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_trim($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $mask = array_shift($argumentList);
    if($mask === null){
        $mask = " \t\n\r\0\x0B";
    }
    $function['execute'] = trim($string, $mask);
    return $function;
}
