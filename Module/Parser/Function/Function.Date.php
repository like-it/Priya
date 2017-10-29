<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_date($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $format = array_shift($argumentList);
    if(empty($format)){
        $format = 'Y-m-d H:i:s';
    }
    elseif($format === true){
        $format = 'Y-m-d H:i:s P';
    }
    elseif(defined($format)){
        $format = constant($format);
    }
    $timestamp = array_shift($argumentList);
    if($timestamp === null){
        $function['execute'] = date($format);
    } else {
        $function['execute'] = date($format, $timestamp);
    }
    return $function;
}
