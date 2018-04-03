<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse\Tag;

function function_array($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $result = array();
    foreach($argumentList as $argument){
        $result[] = $argument;
    }
    $tag[Tag::EXECUTE] = $result;
    return $tag;
}
