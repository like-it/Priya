<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_numeric($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_numeric(array_shift($argumentList));
}
