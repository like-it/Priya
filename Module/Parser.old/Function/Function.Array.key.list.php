<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_array_key_list($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    $search = array_shift($argumentList);
    $strict = array_shift($argumentList);
    if($search === null){
        return array_keys($array);
    }
    return array_keys($array, $search, $strict);
}
