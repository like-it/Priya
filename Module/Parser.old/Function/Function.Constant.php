<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_constant($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $name = array_shift($argumentList);
    $name = strtoupper($name);
    $value = array_shift($argumentList);
    if($value === null){
        return constant($name);
    } else {
        define($name, $value);
        return $value;
    }
}
