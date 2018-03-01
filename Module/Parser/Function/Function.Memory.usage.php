<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_memory_usage($function=array(), $argumentList=array(), $parser=null){
    $usage = memory_get_peak_usage(true);
    $format = array_shift($argumentList);
    switch (strtoupper($format)){
        case 'PB' :
            $function['execute'] = round($usage/1024/1024/1024/1024/1024, 2) . ' PB';
            break;
        case 'TB' :
            $function['execute'] = round($usage/1024/1024/1024/1024, 2) . ' TB';
            break;
        case 'GB' :
            $function['execute'] = round($usage/1024/1024/1024, 2) . ' GB';
           break;
        case 'MB' :
            $function['execute'] = round($usage/1024/1024, 2) . ' MB';
        break;
        case 'KB' :
            $function['execute'] = round($usage/1024, 2) . ' KB';
        break;
        case 'B':
            $function['execute'] = $usage . ' B';
        break;
        default:
            $function['execute'] = $usage;
        break;
    }
    return $function;
}
