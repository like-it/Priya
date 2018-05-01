<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_microtime($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);

    if(
        $argument === null ||
        $argument === false
    ){
        $function['execute'] = microtime(false);
    } else {
        $function['execute'] = microtime(true);
    }
    return $function;
}
