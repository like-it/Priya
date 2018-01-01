<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_debug($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $debug = array_shift($argumentList);
    $title= array_shift($argumentList);
    $is_export = array_shift($argumentList);

    if(is_null($is_export)){
        $is_export = false;
    }
    $function['execute'] = debug($debug, $title, $is_export, 1);
    return $function;
}
