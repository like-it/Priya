<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_tag_strip($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $allowable_tag = array_shift($argumentList);
    if(empty($allowable_tag)){
        $function['execute'] = strip_tags($string);
    } else {
        $function['execute'] = strip_tags($string, $allowable_tag);
    }
    return $function;
}