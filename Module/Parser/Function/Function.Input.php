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
    $text= array_shift($argumentList);
    $text= str_replace('PHP_EOL', PHP_EOL, $text);
    $hidden = array_shift($argumentList);
    if(!empty($hidden)){
        echo $text;
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo PHP_EOL;
    } else {
        $input = rtrim(readline($text), ' ');
    }

    return $input;
}
