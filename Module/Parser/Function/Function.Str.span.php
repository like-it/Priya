<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_span($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $subject = array_shift($argumentList);
    $mask = array_shift($argumentList);
    $start = array_shift($argumentList);
    $length = array_shift($argumentList);

    if($start !== null & $length !== null){
        $function['execute'] = strspn($subject, $mask, $start, $length);
    }
    elseif($start !== null){
        $function['execute'] = strspn($subject, $mask, $start);
    } else {
        $function['execute'] = strspn($subject, $mask);
    }
    return $function;
}
