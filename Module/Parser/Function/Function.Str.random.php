<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_random($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $length = array_shift($argumentList) + 0;
    $string = '';

    for($i=0; $i < $length; $i++){
        $char = rand(32, 126);
        $char = chr($char);
        $string .= $char;
    }
    $function['execute'] = $string;
    return $function;
}
