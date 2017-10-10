<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_date_format($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $format = array_shift($argumentList);
    $time = array_shift($argumentList);
    if(is_string($time)){
        $time = strtotime($time);
    }
    if(!empty($value) || ($value === 0 || $value === '0')){
        $time = $value;
    }
    if(empty($format)){
        $format = 'Y-m-d H:i:s';
    }
    if($time === null){
        return date($format);
    } else {
        return date($format, $time);
    }
}
