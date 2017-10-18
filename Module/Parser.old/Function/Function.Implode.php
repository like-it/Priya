<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_implode($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $glue = '';
    if(count($argumentList) == 1){
        $pieces = array_shift($argumentList);
    } else {
        $glue = array_shift($argumentList);
        $pieces = array_shift($argumentList);
    }
    return implode($glue, $pieces);

}
