<?php

use Priya\Application;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_dir_cwd($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $dir = array_shift($argumentList);
    if(is_dir($dir)){
        chdir($dir);
    }
    $function['execute'] = getcwd() . Application::DS;
    return $function;
}
