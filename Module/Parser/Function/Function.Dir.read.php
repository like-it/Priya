<?php

use Priya\Application;
use Priya\Module\File\Dir;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_dir_read($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = '';
    $recursive = false;
    $format = 'flat';    
    $read = [];
    if(isset($argumentList[0])){
        $url = $argumentList[0];
    }
    if(isset($argumentList[1])){
        $recursive = $argumentList[1];
    }
    if(isset($argumentList[2])){
        $format = $argumentList[2];
    }    
    if(Dir::is($url)){
        $dir = new Dir();
        $read = $dir->read($url, $recursive, $format);        
    } else {
        throw new Exception('Url is no directory');
    }     
    $function['execute'] = $read;
    return $function;
}
