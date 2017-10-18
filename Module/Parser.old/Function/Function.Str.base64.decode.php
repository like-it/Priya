<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_base64_decode($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $strict = array_shift($strict);
    if(empty($strict)){
        return 	base64_decode($string);
    } else {
        return 	base64_decode($string, true);
    }
}
