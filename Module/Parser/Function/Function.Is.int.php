<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_is_int($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $int = array_shift($argumentList);
    if(strtolower($int) == 'nan'){
        $int= NAN;
    }
    $function['execute'] = is_int($int);
    return $function;
}
