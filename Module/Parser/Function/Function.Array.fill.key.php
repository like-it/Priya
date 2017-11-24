<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_array_fill_key($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $key = array_shift($argumentList);
    $val = array_shift($argumentList);
    $function['execute'] = array_fill_keys($key, $val);
    return $function;
}
