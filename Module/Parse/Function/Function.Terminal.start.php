<?php
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_start($tag=array(), $parser=null){
    $argumentList = $tag['parameter'];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = array_shift($argumentList);
    $attribute = $attribute ? $attribute : 'priya.module.terminal.start';
    $parser->data($attribute, true);
    $tag['execute'] = '';
    return $tag;
}