<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_key_list($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $search = array_shift($argumentList);
    $strict = array_shift($argumentList);
    if($search === null){
        $function['execute'] = array_keys($array);
    } else {
        $function['execute'] = array_keys($array, $search, $strict);
    }
    return $function;
}
