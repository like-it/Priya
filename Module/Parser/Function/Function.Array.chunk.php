<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_chunk($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $size = array_shift($argumentList);
    $preserve_key = array_shift($argumentList);
    if(empty($preserve_key)){
        return array_chunk($array, $size);
    }
    return array_chunk($array, $size, $preserve_key);
}
