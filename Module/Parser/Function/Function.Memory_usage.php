<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_memory_usage($value=null, $argumentList=array(), $parser=null){
    $usage = memory_get_peak_usage(true);
    $format = array_shift($argumentList);
    switch (strtoupper($format)){
        case 'GB' :
            return round($usage/1024/1024/1024, 2) . ' GB';
           break;
        case 'MB' :
            return round($usage/1024/1024, 2) . ' MB';
        break;
        case 'KB' :
            return round($usage/1024, 2) . ' KB';
        break;
        case 'B':
            return $usage . ' B';
        break;
        default:
            return $usage;
        break;

    }

}
