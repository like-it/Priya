<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_link($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $link = array_shift($argumentList);
    $target = array_shift($argumentList);
    if(!file_exists($target)){
        $function['execute'] = false;
        return $function;
    }
    if(file_exists($link)){
        $function['execute'] = false;
        return $function;
    }
    system('ln -s ' . escapeshellarg($target) . ' ' . escapeshellarg($link), $output);
    if(empty($output)){
        $function['execute'] = true;
        return $function;
    } else {
        $function['execute'] = false;
        return $function;
    }
}
