<?php

/**
 * @author        Remco van der Velde
 * @since         2017-04-20
 * @version       1.0
 * @changeLog
 *     -    all
 */

function function_is_empty($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(empty($argumentList)){
        $function['execute'] = true;
    } else {
        foreach($argumentList as $argument){
            $function['execute']  = empty($argument);
            if($function['execute'] === false){
                return $function;
            }
        }
    }
    return $function;
}
