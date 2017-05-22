<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_get_csv($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $delimiter = array_shift($argumentList);
    $enclosure = array_shift($argumentList);
    $escape = array_shift($argumentList);
    return str_getcsv($input, $delimiter, $enclosure, $escape);
}
