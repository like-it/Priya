<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse\Tag;

function function_microtime($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];

    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);

    if(
        $argument === null ||
        $argument === false
    ){
        $tag[Tag::EXECUTE] = microtime(false);
    } else {
        $tag[Tag::EXECUTE] = microtime(true);
    }
    return $tag;
}
