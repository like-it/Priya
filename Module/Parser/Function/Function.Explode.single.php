<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_explode_single($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $delimiter = array_shift($argumentList);
    $string = array_shift($argumentList);
    return $parser->explode_single($delimiter, $string);
}
