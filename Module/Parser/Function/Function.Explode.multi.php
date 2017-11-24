<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_explode_multi($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $delimiter = array_shift($argumentList);
    $string = array_shift($argumentList);
    $limit = array_shift($argumentList);
    if($limit === null){
        $limit = array();
    }
    $function['execute'] = $parser->explode_multi($delimiter, $string, $limit);
    return $function;
}
