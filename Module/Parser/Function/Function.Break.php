<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_break($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $amount = (int) array_shift($argumentList);
    if($amount < 1){
        $amount = 1;
    }
    if($amount > $parser->data('priya.parser.break.level')){
        $amount = $parser->data('priya.parser.break.level');
    }
    $parser->data('priya.parser.halt', true);
    $parser->data('priya.parser.break.amount', $amount);
    $explode = explode($function['key'], $function['string'], 2);
    $function['execute'] = '';
//     var_dump($explode[0]);
//     die;
    $parser->data('priya.parser.break.before', $explode[0]);
    return $function;
}
