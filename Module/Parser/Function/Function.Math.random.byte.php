<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math_random_byte($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $length = array_shift($argumentList) + 0;

    if(version_compare(PHP_VERSION, $parser::PHP_MIN_VERSION, '>=')){
        $function['execute'] = random_bytes($length);
    } else {
        $function['execute'] = false;
    }
    return $function;
}
