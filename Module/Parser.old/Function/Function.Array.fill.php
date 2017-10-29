<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_fill($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $start_index = array_shift($argumentList);
    $num = array_shift($argumentList);
    $val = array_shift($argumentList);
    return array_fill($start_index, $num, $val);
}
