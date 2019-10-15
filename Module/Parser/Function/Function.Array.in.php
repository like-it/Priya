<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_in($function=array(),$argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $needle = array_shift($argumentList);
    $haystack = array_shift($argumentList);
    if($haystack === null){
        $haystack = [];
    }
    elseif(is_string($haystack) && $haystack == ''){
        $haystack = [];
    }
    $strict = array_shift($argumentList);
    if(!empty($strict)){
        $function['execute'] = in_array($needle, $haystack, true);
    } else {
        $function['execute'] = in_array($needle, $haystack);
    }
    return $function;
}
