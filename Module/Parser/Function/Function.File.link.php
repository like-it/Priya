<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_file_link($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $link = array_shift($argumentList);
    $target = array_shift($argumentList);
    if(!file_exists($target)){
        return false;
    }
    if(file_exists($link)){
        return false;
    }
    system('ln -s ' . escapeshellarg($target) . ' ' . escapeshellarg($link), $output);
    if(empty($output)){
        return true;
    } else {
        return false;
    }
}
