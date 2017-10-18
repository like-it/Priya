<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_pop($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $selector = substr(array_shift($argumentList), 2, -1);
    $array = $parser->data($selector);
    $result = array_pop($array);
    $parser->data($selector, $array);
    return $result;
}
