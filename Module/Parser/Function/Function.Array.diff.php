<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_diff($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $array_compare = array_shift($argumentList);
    $diff = array_diff($array, $array_compare);
    if(empty($argumentList)){
        $function['execute'] = $diff;
    } else {
        array_unshift($argumentList, $array);
        $function['execute'] = function_array_diff($value, $argumentList, $parser);
    }
    return $function;

}
