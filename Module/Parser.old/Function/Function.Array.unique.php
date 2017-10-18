<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_unique($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $sort = strtoupper(array_shift($argumentList));
    if(empty($sort)){
        $sort = SORT_STRING;
    } else{
        $sort = constant($sort);
    }
    return array_unique($array, $sort);
}
