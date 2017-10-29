<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_empty($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    if(is_string($argument)){
        $argument = str_replace('"null"', '', $argument);
        $argument = str_replace('"false"', '',$argument);
        $argument = str_replace('""', '', $argument);
        $argument = str_replace('"0.0"', '', $argument);
        $argument = str_replace('"0"', '', $argument);
        $argument = str_replace('0.0', '', $argument);
        $argument = str_replace('0', '', $argument);
    }
    $function['execute']  = empty($argument);
    return $function;
}
