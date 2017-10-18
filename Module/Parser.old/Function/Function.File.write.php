<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_file_write($value=null, $argumentList=array(), $parser=null){
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
    return $file->write($url, $data);
}
