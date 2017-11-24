<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_parse_int($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $var = array_shift($argumentList);
    $base = array_shift($argumentList);
    if($base === null){
        $base = 10;
    }
    $function['execute'] = intval($var, $base);
    return $function;
}
