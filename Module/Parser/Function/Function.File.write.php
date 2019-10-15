<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_write($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = array_shift($argumentList);
    $data = array_shift($argumentList);
    $encode = array_shift($argumentList);
    $file = new Priya\Module\File();
    if(strtoupper($encode) == 'BASE64_DECODE'){
        $data = base64_decode($data);
    }
    $function['execute'] = $file->write($url, $data);
    return $function;
}
