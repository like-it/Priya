<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_chunk($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $size = array_shift($argumentList);
    $preserve_key = array_shift($argumentList);
    if(empty($preserve_key)){
        $function['execute'] = array_chunk($array, $size);
    } else {
        $function['execute'] = array_chunk($array, $size, $preserve_key);
    }
    return $function;
}
