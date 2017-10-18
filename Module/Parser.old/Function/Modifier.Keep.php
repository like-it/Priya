<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_keep($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $keep = array_shift($argumentList);
    $amount = array_shift($argumentList);
    if(substr($keep, 0, 1) == '$'){
//         return '{$' . $keep . '}';
        if($amount){
            return '[keep[' . $keep . ']' . $amount . ']';
        } else {
            return '[keep[' . $keep . ']]';
        }

    }
    return $keep;
}
