<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_parameter($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $parameter= array_shift($argumentList);
    return $parser->parameter($parameter);
}
