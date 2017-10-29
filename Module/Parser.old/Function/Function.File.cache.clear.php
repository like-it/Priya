<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_file_cache_clear($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $clear_realpath_cache = array_shift($argumentList);
    $filename = array_shift($argumentList);
    if($clear_realpath_cache === null && $filename === null){
        return clearstatcache();
    }
    if($filename === null){
        return clearstatcache($clear_realpath_cache);
    } else {
        return clearstatcache($clear_realpath_cache, $filename);
    }
}
