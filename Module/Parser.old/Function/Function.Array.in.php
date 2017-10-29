<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_in($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $needle = array_shift($argumentList);
    $haystack = array_shift($argumentList);
    $strict = array_shift($argumentList);
    if(!empty($strict)){
        return in_array($needle, $haystack, true);
    } else {
        return in_array($needle, $haystack);
    }
}
