<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_set_type($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $var = array_shift($argumentList);
    $orig = $var;
    $type = array_shift($argumentList);
    $result = settype($var, $type);
    if($result === false){
        return false;
    }
    return $var;
}
