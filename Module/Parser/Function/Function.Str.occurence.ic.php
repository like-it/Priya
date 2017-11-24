<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_occurence_ic($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $haystack = array_shift($argumentList);
    $needle = array_shift($argumentList);
    $before_needle = array_shift($argumentList);
    if(empty($before_needle)){
        $before_needle = false;
    }
    $function['execute'] = stristr($haystack, $needle, $before_needle);
    return $function;
}
