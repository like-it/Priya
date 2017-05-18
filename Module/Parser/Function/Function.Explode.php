<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_explode($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $delimiter = array_shift($argumentList);
    $string = array_shift($argumentList);
    $limit = array_shift($argumentList);

    if(!empty($limit)){
        return explode($delimiter, $string, $limit);
    } else {
        return explode($delimiter, $string);
    }
}
