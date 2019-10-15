<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_copy($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $source = array_shift($argumentList);
    $destination = array_shift($argumentList);
    $file = new Priya\Module\File();
    $function['execute'] = $file->copy($source, $destination);
    return $function;
}
