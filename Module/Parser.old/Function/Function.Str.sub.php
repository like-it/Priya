<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_sub($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $start = array_shift($argumentList);
    $length = array_shift($argumentList);
    if($length !== null){
        return substr($string, $start, $length);
    } else {
        return substr($string, $start);
    }

}
