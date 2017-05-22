<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_sub_compare($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string1 = array_shift($argumentList);
    $string2 = array_shift($argumentList);
    $offset = array_shift($argumentList);
    $length = array_shift($argumentList);
    $case_insensitivity= array_shift($argumentList);
    if($length !== null && empty($case_insensitivity)){
        $case_insensitivity = false;
    }
    if($length === null){
        return substr_compare($string1, $string2, $offset);
    } else {
        return substr_compare($string1, $string2, $offset, $length, $case_insensitivity);
    }

}
