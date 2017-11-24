<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_is_float($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $float = array_shift($argumentList);
    if(strtolower($float) == 'nan'){
        $float= NAN;
    }
    $function['execute'] = is_float($float);
    return $function;
}
