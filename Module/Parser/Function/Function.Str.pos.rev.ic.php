<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_pos_rev_ic($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $haystack = array_shift($argumentList);
    $needle = array_shift($argumentList);
    $offset = array_shift($argumentList);
    if(empty($offset)){
        $offset = 0;
    }
    $haystack = strtolower($haystack);
    $needle = strtolower($needle);
    $function['execute'] = strrpos($haystack, $needle, $offset);
    return $function;
}
