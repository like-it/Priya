<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_error($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $type = array_shift($argumentList);
    $attribute = array_shift($argumentList);
    $val = array_shift($argumentList);
    return $parser->error($type, $parser->random() . '.' . $attribute, $value);
}
