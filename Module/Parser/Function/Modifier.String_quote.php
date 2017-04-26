<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_string_quote($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    $escape = array_shift($argumentList);
    if(!empty($escape)){
        $value = str_replace('\'', $escape, $value);
    }
    return  $argument .  $value. $argument;
}
