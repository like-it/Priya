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
    $argument = reset($argumentList);
    return  $argument .  $value. $argument;
}
