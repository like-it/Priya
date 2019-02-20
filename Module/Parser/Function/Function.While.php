<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parser\Control_While;

function function_while($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $level = (int) $parser->data('priya.parser.break.level');
    $level++;
    $parser->data('priya.parser.break.level', $level);

    $value = Control_While::tag_lower($function['string'], 'while');
    $value = Control_While::get($value);
    $content = Control_While::content($value, $parser);
    $content['string'] = Control_While::literal($content['string'], $parser);

    if($content['string'] == ''){
        //can be caused by multiple whiles with the same arguments...
        $function['execute'] = '';
    }
    elseif($content['string'] === false){
        $function['execute'] = '';
    } else {
        $function = Control_While::find($function, $content['string'], $argumentList, $parser);
        $function['execute'] = Control_While::replace($function['execute'], $parser);
        $content['string'] = Control_While::replace($content['string'], $parser);
        $function['string'] = Control_While::finalize($content, $function);
    }
    $level = (int) $parser->data('priya.parser.break.level');
    $level--;
    $parser->data('priya.parser.break.level', $level);
    return $function;
}
