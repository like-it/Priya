<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math_average($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $round = array_shift($argumentList);
    $mode = array_shift($argumentList);
    if(!empty($mode)){
        $mode = constant($mode);
    } else {
        $mode = PHP_ROUND_HALF_UP;
    }
    $count = 0;
    if(empty($array)){
        $function['execute'] = $count;
        return $function;
    }
    foreach($array as $number){
        $count += $number;
    }
    $function['execute'] = round(($count / count($array)), $round, $mode);
    return $function;
}
