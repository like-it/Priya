<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_ireplace($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $search = array_shift($argumentList);
    $replace = array_shift($argumentList);
    $subject = array_shift($argumentList);
    $attribute = array_shift($argumentList);
    if(!empty($attribute) && substr($attribute,0,1) == '$'){
        $subject = str_ireplace($search, $replace, $subject, $count);
        $parser->data(substr($attribute, 1), $count);
    } else {
        $subject = str_ireplace($search, $replace, $subject);
    }
    return $subject;
}
