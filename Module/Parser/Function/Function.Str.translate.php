<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_translate($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $from = array_shift($argumentList);
    $to = array_shift($argumentList);
    if(is_object($from)){
        $array = $parser->object($from, 'array');
        if(is_array($array)){
            $function['execute'] = strtr($string, $array);
            return $function;
        }
    }
    $function['execute'] = strtr($string, $from, $to);
    return $function;
}