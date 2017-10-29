<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_current($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $reference = array_shift($argumentList);
    $selector = substr($reference, 2, -1);
    $array = $parser->data($selector);
    $result = current($array);
    $parser->data($selector, $array);
    return $result;
}
