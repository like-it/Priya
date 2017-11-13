<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_base64_decode($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $strict = array_shift($strict);
    if(empty($strict)){
        $function['execute'] = base64_decode($string);
    } else {
        $function['execute'] = base64_decode($string, true);
    }
    return $function;
}
