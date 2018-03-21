<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse\Tag;

function function_array_merge($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $result = array_shift($argumentList);
    foreach($argumentList as $argument){
        $result = array_merge($result, $argument);
    }
    $tag[Tag::EXECUTE] = $result;
    return $tag;
}
