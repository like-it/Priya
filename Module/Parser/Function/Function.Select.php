<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Data;

function function_select($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = array_shift($argumentList);
    $select = array_shift($argumentList);

    $result = null;
    if(!$select){
        $select = $url;
        $url = null;
        $result = $parser->data($select);
    }
    if($url){
        if(file_exists($url) === false){
            throw new Exception('File not exists (' . $url . ')');
        }
        $data = new Data();
        $data->read($url);
        $data->data($parser->compile($data->data(), $parser->data(), true, true));
        $result = $data->data($select);
    }
    $function['execute'] = $result;
    return $function;
}
