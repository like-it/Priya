<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_implode($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $glue = '';
    if(count($argumentList) == 1){
        $pieces = array_shift($argumentList);
    } else {
        $glue = array_shift($argumentList);
        $pieces = array_shift($argumentList);
    }
    if($pieces === null){
        $function['execute'] = ''; //implode returns a string...
    } else {
        $function['execute'] = implode($glue, $pieces);
    }
    return $function;

}
