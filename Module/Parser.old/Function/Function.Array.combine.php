<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_combine($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $keys = array_shift($argumentList);
    $values = array_shift($argumentList);
    if(count($keys) <> count($values)){
        return false;
    }
    return array_combine($keys, $values);
}
