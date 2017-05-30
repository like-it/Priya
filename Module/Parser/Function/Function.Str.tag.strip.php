<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_tag_strip($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $allowable_tag = array_shift($argumentList);
    if(empty($allowable_tag)){
        return strip_tags($string);
    } else {
        return strip_tags($string, $allowable_tag);
    }
}
