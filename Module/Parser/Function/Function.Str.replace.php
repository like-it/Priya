<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_replace($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $search = array_shift($argumentList);
    $replace = array_shift($argumentList);
    $subject = array_shift($argumentList);
    $attribute = array_shift($argumentList);
    if(!empty($attribute) && substr($attribute,0,1) == '$'){
        $subject = str_replace($search, $replace, $subject, $count);
        $parser->data(substr($attribute, 1), $count);
    } else {
        $subject = str_replace($search, $replace, $subject);
    }
    $function['execute'] = $subject;
    return $function;
}
