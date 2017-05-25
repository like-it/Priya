<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_sub_replace($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $replacement = array_shift($argumentList);
    $start = array_shift($argumentList);
    $length = array_shift($argumentList);
    if(empty($length)){
        return substr_replace($string, $replacement, $start);
    } else {
        return substr_replace($string, $replacement, $start, $length);
    }

}
