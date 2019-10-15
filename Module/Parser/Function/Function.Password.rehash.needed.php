<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_password_rehash_needed($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $hash = array_shift($argumentList);
    $algo = array_shift($argumentList);
    if(empty($algo)){
        $algo = PASSWORD_DEFAULT;
    } else {
        $algo = constant($algo);
    }
    $options = array_shift($argumentList);
    if(is_array($options)){
        $function['execute'] = password_needs_rehash($hash, $algo, $options);
    } else {
        $function['execute'] = password_needs_rehash($hash, $algo);
    }
    return $function;
}