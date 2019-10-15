<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math_random($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $min = array_shift($argumentList);
    $max = array_shift($argumentList);
    if($min === null && $max === null){
        $function['execute'] = rand();
        return $function;
    }
    elseif(isset($min) && isset($max)){
        if(version_compare(PHP_VERSION, $parser::PHP_MIN_VERSION, '>=')){
            $function['execute'] = random_int($min, $max);
            return $function;
        } else {
            $function['execute'] = rand($min, $max);
            return $function;
        }
    } else {
        $function['execute'] = false;
        return $function;
    }
}
