<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_is_set($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    foreach($argumentList as $argument){
        $function['execute']  = isset($argument);
        if($function['execute'] === false){
            return $function;
        }
    }
    return $function;
}