<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_compare($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string1 = array_shift($argumentList);
    $string2= array_shift($argumentList);
    $function['execute'] = strcmp($string1, $string2);
    return $function;
}
