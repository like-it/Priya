<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_math_max($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $array = array_shift($argumentList);
    if(is_array($array)){
        return max($array);
    } else {
        return max($array,
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList),
            array_shift($argumentList)
        );
    }
}
