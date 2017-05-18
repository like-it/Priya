<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_basename($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace(array('\\', '\/'), DIRECTORY_SEPARATOR, $value);
    $basename = basename($value, end($argumentList));
    if(empty($basename)){
        return false;
    }
    return $basename;
}
