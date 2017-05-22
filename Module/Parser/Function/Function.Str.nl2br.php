<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_nl2br($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $is_xhtml = array_shift($argumentList);
    if($is_xhtml === null){
        $is_xhtml = true;
    }
    return nl2br($string, $is_xhtml);
}
