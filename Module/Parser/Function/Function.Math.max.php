<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math_max($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    if(!is_array($array)){
        array_unshift($argumentList, $array);
        $array = $argumentList;
    }
    $function['execute'] = max($array);
    return $function;
}
