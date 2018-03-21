<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_merge($tag=array(), $parser=null){
    $argumentList = $tag['parameter'];
    var_dump($argumentList);
    die;
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $reference = array_shift($argumentList);
    $selector = substr($reference, 2, -1);
    $array = $parser->data($selector);
    $result = next($array);
    $parser->data($selector, $array);
    $function['execute'] = $result;
    return $function;
}
