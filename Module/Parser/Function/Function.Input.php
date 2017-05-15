<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 * - when |more is enabled without PHP_EOL it isnt working (same as readline) thats why
 */

function function_input($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = '';
    $string = array_shift($argumentList);
    $string = str_replace('PHP_EOL', PHP_EOL, $string);
    $hidden = array_shift($argumentList);

    $input = readline($string);
    return $input;
}
