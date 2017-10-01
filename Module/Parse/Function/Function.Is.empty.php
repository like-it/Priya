<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_empty($argumentList=array(), $variable=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $list = array();
    foreach($argumentList as $argument){
        $list[] = $argument;
    }
    foreach($list as $nr => $argument){
        $argument = str_replace('"null"', '', $argument);
        $argument = str_replace('"false"', '',$argument);
        $argument = str_replace('""', '', $argument);
        $argument = str_replace('"0.0"', '', $argument);
        $argument = str_replace('"0"', '', $argument);
        $argument = str_replace('0.0', '', $argument);
        $argument = str_replace('0', '', $argument);
        if(empty($argument)){
            unset($list[$nr]);
        }
    }
    return empty($list);
}
