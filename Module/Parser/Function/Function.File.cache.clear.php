<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_cache_clear($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $clear_realpath_cache = array_shift($argumentList);
    $filename = array_shift($argumentList);
    if($clear_realpath_cache === null && $filename === null){
        $function['execute'] = clearstatcache();
        return $function;
    }
    if($filename === null){
        $function['execute'] = clearstatcache($clear_realpath_cache);
        return $function;
    } else {
        $function['execute'] = clearstatcache($clear_realpath_cache, $filename);
        return $function;
    }
}
