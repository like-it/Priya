<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_span($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $subject = array_shift($argumentList);
    $mask = array_shift($argumentList);
    $start = array_shift($argumentList);
    $length = array_shift($argumentList);

    if($start !== null & $length !== null){
        return strspn($subject, $mask, $start, $length);
    }
    elseif($start !== null){
        return strspn($subject, $mask, $start);
    } else {
        return strspn($subject, $mask);
    }
}
