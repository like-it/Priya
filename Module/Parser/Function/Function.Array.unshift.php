<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-06-04
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_unshift($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $reference = array_shift($argumentList);
    $selector = substr($reference, 2, -1);
    $array = $parser->data($selector);
    $val = array_shift($argumentList);
    $result = array_unshift($array, $val);
    $parser->data($selector, $array);
    if(empty($argumentList)){
        return $result;
    } else {
        array_unshift($argumentList, $reference);
        return function_array_unshift($value, $argumentList, $parser);
    }
}
