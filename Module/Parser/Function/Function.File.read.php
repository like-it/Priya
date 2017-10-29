<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_file_read($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = array_shift($argumentList);
    $encode = array_shift($argumentList);
    $file = new Priya\Module\File();
    if(strtoupper($encode) == 'BASE64_ENCODE'){
        $read = $file->read($url);
        $function['execute'] = base64_encode($read);
        return $function;
    }
    elseif(strtoupper($encode) == 'LITERAL'){
        $read = $file->read($url);
        $function['execute'] = '{literal}' . $read . '{/literal}';
        return $function;
    } else {
        $function['execute'] = $file->read($url);
        return $function;
    }
}
