<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_str_compare_version($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $version1 = array_shift($argumentList);
    $version2 = array_shift($argumentList);
    $operator = array_shift($argumentList);

    if($version1 == 'PHP_VERSION'){
        $version1 = constant('PHP_VERSION');
    }
    if($version2 == 'PHP_VERSION'){
        $version2 = constant('PHP_VERSION');
    }

    if($operator=== null){
        $function['execute'] = version_compare($version1, $version2);
    } else {
        $function['execute'] = version_compare($version1, $version2, $operator);
    }
    return $function;
}
