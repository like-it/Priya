<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_wrap_word($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $width = array_shift($argumentList);
    $break = array_shift($argumentList);
    $cut = array_shift($argumentList);
    if($width === null){
        $width = 75;
    }
    if($break === null){
        $break = "\n";
    }
    if(empty($cut)){
        $cut = false;
    }
    $function['execute'] = wordwrap($string, $width, $break, $cut);
    return $function;
}
