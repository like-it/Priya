<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_format($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $string = sprintf($string,
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList)
    );
    $function['execute'] = $string;
    return $function;
}
