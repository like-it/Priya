<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_password_rehash_needed($value=null, $argumentList=array(), $parser=null){
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
        return password_needs_rehash($hash, $algo, $options);
    } else {
        return password_needs_rehash($hash, $algo);
    }
}
