<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_object_merge($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $object = array_shift($argumentList);
    foreach($argumentList as $argument){
        $object = $parser::object_merge($object, $argument);
    }
    $function['execute'] = $object;
    return $function;
}
