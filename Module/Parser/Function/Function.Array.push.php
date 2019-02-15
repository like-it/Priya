<?php

/**
 * @author         Remco van der Velde
 * @since         2017-06-04
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_push($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);

    $reference = $function['parameter'][0]['variable'];
    $selector = substr($reference, 1);
    $parser->data($selector);

    while($value = array_shift($argumentList)){
        $result = array_push($array, $value);
    }
    $parser->data($selector, $array);
    $function['execute'] = $result;
    return $function;
}