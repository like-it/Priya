<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_css($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $object = array_shift($argumentList);

    if(!is_object($object)){
        $function['execute'] = '';
        return $function;
    }
    $css = '';
    foreach($object as $key => $value){
        $css .= $key . ':' . $value . ';' . "\n";
    }
    $function['execute'] = $css;
    return $function;
}
