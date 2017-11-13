<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_crypt($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $salt = array_shift($argumentList);
    if(!empty($salt)){
        $function['execute'] = crypt($string, $salt);
    } else {
        $function['execute'] = crypt($string);
    }
    return $function;
}