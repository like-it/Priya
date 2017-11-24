<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_password_verify($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $password = array_shift($argumentList);
    $hash = array_shift($argumentList);
    $function['execute'] = password_verify($password, $hash);
    return $function;
}
