<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_unset($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $unset = array_shift($argumentList);
    $parser->data('delete', substr($unset, 2, -1));
    if(!empty($argumentList)){
        function_unset($value, $argumentList, $parser);
    }
    $function['execute'] = '';
    return $function;
}
