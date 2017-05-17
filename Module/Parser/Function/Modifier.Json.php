<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_json($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(is_object($value)){
        return $value;
    }
    if(is_array($value)){
        return $value;
    }
    if(substr($value,0,1) == '{' && substr($value,1,-1) == '}'){
        return json_decode($value);
    }
    return false;
    /*
    if(is_object($value) || is_array($value)){
        $json = json_encode($value); //remove PRETTY_PRINT
    } else {
        $json = json_decode($value);
    }
    if(!empty($json)){
        return json_encode($json); //remove PRETTY_PRINT
    } else {
        return $value;
    }
    */
}
