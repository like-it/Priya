<?php

/**
 * @author              Remco van der Velde
 * @since               2017-04-20
 * @version             1.0
 * @changeLog
 *     -    all
 */

function function_is_file($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $function['execute'] = is_file(array_shift($argumentList));
    return $function;
}
