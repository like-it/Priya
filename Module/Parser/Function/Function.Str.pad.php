<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_pad($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $pad_length = array_shift($argumentList);
    $pad_string = array_shift($argumentList);
    $pad_type = array_shift($argumentList);
    if(empty($pad_string)){
        $pad_string = ' ';
    }
    if(empty($pad_type)){
        $pad_type= STR_PAD_RIGHT;
    } else {
        $pad_type = constant($pad_type);
    }
    return str_pad($input, $pad_length, $pad_string, $pad_type);
}
