<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_diff($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $array_compare = array_shift($argumentList);
    $diff = array_diff($array, $array_compare);
    if(empty($argumentList)){
        return $diff;
    }
    array_unshift($argumentList, $array);
    return function_array_diff($value, $argumentList, $parser);
}
