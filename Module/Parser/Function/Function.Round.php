<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_round($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $val = array_shift($argumentList);
    $precision = count($argumentList) >= 1 ? array_shift($argumentList) : 0;
    $mode= count($argumentList) >= 1 ? array_shift($argumentList) : PHP_ROUND_HALF_UP;

    return round($val, $precision, $mode);
}
