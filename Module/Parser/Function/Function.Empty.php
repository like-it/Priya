<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_empty($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    var_dump('$$$$$$$$$$$$$$$$$$$$$');
    var_dump($argumentList);
    foreach($argumentList as $nr => $argument){
        $argument = str_replace('"null"', '', $argument);
        $argument = str_replace('"false"', '', $argument);
        $argument = str_replace('""', '', $argument);
        $argument = str_replace('"0.0"', '', $argument);
        $argument = str_replace('"0"', '', $argument);
        $argument = str_replace('0.0', '', $argument);
        $argument = str_replace('0', '', $argument);
        if(empty($argument)){
            unset($argumentList[$nr]);
        }
    }
    return empty($argumentList);
}
