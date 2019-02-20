<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parser\Control_Foreach;

function function_for_each($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $level = (int) $parser->data('priya.parser.break.level');
    $level++;
    $parser->data('priya.parser.break.level', $level);
    $list = Control_Foreach::list($function, $parser);
    $key = Control_Foreach::key($function);
    $record = Control_Foreach::record($function);
    $value = Control_Foreach::tag_lower($function['string'], 'for.each');
    $value = Control_Foreach::get($value);
    $content = Control_Foreach::content($value, $parser);
    $content['string'] = Control_Foreach::literal($content['string'], $parser);
    if($content['string'] == ''){
        //can be caused by multiple foreaches with the same arguments...
        $function['execute'] = '';
    }
    elseif($content['string'] === false){
        $function['execute'] = '';
    } else {
//         var_dump($content['string']);
        $function = Control_Foreach::find($function, $content['string'], $list, $key, $record, $parser);
        $function['execute'] = Control_Foreach::replace($function['execute'], $parser);
        $content['string'] = Control_Foreach::replace($content['string'], $parser);
        $function['string'] = Control_Foreach::finalize($content, $function);
    }
    $level = (int) $parser->data('priya.parser.break.level');
    $level--;
    $parser->data('priya.parser.break.level', $level);
    $parser->data('delete', 'priya.parser.function.current');
//     var_dump($function);
//     die;
    return $function;
}
