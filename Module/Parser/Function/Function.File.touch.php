<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_touch($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = array_shift($argumentList);
    $mtime = array_shift($argumentList);
    $atime = array_shift($argumentList);
    if($mtime === null){
        $mtime = time();
    }
    if($atime !== null){
        $function['execute'] = touch($url, $mtime, $atime);
    } else {
        $function['execute'] = touch($url, $mtime);
    }
    return $function;
}
