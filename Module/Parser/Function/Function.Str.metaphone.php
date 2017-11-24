<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_metaphone($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $phonemes = array_shift($argumentList);
    if(empty($phonemes)){
        $phonemes = 0;
    }
    $function['execute'] = metaphone($string, $phonemes);
    return $function;

}
