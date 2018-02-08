<?php
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function modifier_default($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    var_dump(debug_backtrace(true));
    if(empty($value) && count($argumentList) >= 1){
        return end($argumentList);
    }
    return $value;
}
