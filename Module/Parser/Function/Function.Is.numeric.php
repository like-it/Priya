<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_is_numeric($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $numeric = array_shift($argumentList);
    if(strtolower($numeric) == 'nan'){
        $numeric= NAN;
    }
    $function['execute'] = is_numeric($numeric);
    return $function;
}
