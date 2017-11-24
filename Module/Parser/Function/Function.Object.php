<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_object($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $output = array_shift($argumentList);
    $type = array_shift($argumentList);
    $function['execute'] = $parser->object($input, $output, $type);
    return $function;
}
