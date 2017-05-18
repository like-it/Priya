<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_lc_word($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $delimiters = array_shift($argumentList);
    if(empty($delimiters)){
        $delimiters = " \t\r\n\f\v";
    }
    $delimiters = str_split($delimiters);
    $list = $parser->explode_single($delimiters, $string);
    foreach($list as $nr => $word){
        $string = str_replace($word, lcfirst($word), $string);
    }
    return $string;

}
