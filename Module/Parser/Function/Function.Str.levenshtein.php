<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_levenshtein($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string1 = array_shift($argumentList);
    $string2 = array_shift($argumentList);
    $cost_insert = array_shift($argumentList);
    $cost_replace = array_shift($argumentList);
    $cost_delete = array_shift($argumentList);

    if($cost_insert === null){
        $function['execute'] = levenshtein($string1, $string2);
    } else {
        $function['execute'] = levenshtein($string1, $string2, $cost_insert, $cost_replace, $cost_delete);
    }
    return $function;
}