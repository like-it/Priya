<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_scan($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $format = array_shift($argumentList);
    $scan = sscanf($string, $format);
    if(count($argumentList) > 0){
        $object = new stdClass();
        foreach($argumentList as $attribute){
            $object->{$attribute} = array_shift($scan);
        }
        return $object;
    }
    return $scan;
}
