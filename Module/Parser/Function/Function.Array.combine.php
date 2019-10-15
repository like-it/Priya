<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_combine($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $keys = array_shift($argumentList);
    $values = array_shift($argumentList);
    if(count($keys) <> count($values)){
        $function['execute'] = false;
    } else {
        $function['execute'] = array_combine($keys, $values);
    }
    return $function;
}
