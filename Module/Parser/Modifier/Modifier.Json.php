<?php
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function modifier_json($value=null, $argumentList=array(), $parser=null){
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
    return json_encode($value, JSON_PRETTY_PRINT);
}