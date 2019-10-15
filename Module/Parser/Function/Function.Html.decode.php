<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_html_decode($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $html = array_shift($argumentList);
    $function['execute'] = htmlspecialchars_decode(
        $html,
        ENT_NOQUOTES
    );
    return $function;
}
