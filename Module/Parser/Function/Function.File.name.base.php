<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_name_base($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $path = array_shift($argumentList);
    $suffix = array_shift($argumentList);
    if(empty($suffix)){
        $function['execute'] = basename($path);
    } else {
        $function['execute'] = basename($path, $suffix);
    }
    return $function;
}
