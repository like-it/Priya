<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_output($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $string = str_replace('PHP_EOL', PHP_EOL, $string);
    if(is_object($string) && method_exists($string, '__toString')){
        echo $string;
        return $string;
    }
    elseif(is_object($string) || is_array($string)){
        $string = $parser->object($string, 'json');
        echo $string;
        return $string;
    } else {
        echo $string;
        return $string;
    }

}
