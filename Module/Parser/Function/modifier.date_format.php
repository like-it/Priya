<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_date_format($value=null, $argumentList=array()){
    var_dump($value);
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(empty($value) && $value !== 0 && count($argumentList) > 1){
        return end($argumentList);
    }
    if(empty($value) && $value !== 0){
        return false;
    }
    if(is_numeric($value) === false){
        if(count($argumentList) > 1){
            return end($argumentList);
        } else {
            return false;
        }
    }
    return date(reset($argumentList), $value);
}
