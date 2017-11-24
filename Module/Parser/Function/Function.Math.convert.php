<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math_convert($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $from = array_shift($argumentList);
    $to = array_shift($argumentList);
    $function['execute'] = base_convert($string, $from, $to);
    return $function;
}
