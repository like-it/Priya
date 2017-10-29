<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_key_exists($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $key = array_shift($argumentList);
    $array = array_shift($argumentList);
    $function['execute'] = array_key_exists($key, $array);
    return $function;
}
