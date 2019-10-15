<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_key($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $reference = array_shift($argumentList);
    $selector = substr($reference, 2, -1);
    $array = $parser->data($selector);
    $result = key($array);
    $parser->data($selector, $array);
    $function['execute'] = $result;
    return $function;
}
