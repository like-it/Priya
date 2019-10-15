<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_capture($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = trim(array_shift($argumentList), '"\'');

    $value = $function['string'];
    $value = str_ireplace('{capture', '{capture', $value);
    $value = str_ireplace('{/capture}', '{/capture}', $value);
    $value = str_ireplace('{/capture.append}', '{/capture.append}', $value);
    $value = str_replace('{capture', '{capture[' . $parser->random() . ']', $value);

    $explode = explode('{capture', $value, 2);

    $string = '';
    if(isset($explode[1])){
        $capture = explode('{/capture}', $explode[1], 2);
        $string = $capture[0];
    }

    if(!isset($capture[1])){
        throw new Exception('Missing {/capture} in {capture} tag');
    }
    if($data === null){
        $data = $parser->data();
    }

    $temp = explode('[' . $parser->random() . '](', $string, 2);
    if(isset($temp[1])){
        $tmp = explode(')}', $temp[1], 2);
        $tmp[1] = $parser->compile($tmp[1], $data);
        $parser->data($attribute, $tmp[1]);
        $search = '{capture' . $string . '{/capture}';
        $value = str_replace($search, '', $value);
    }
    $value = str_replace('{capture[' . $parser->random() . ']', '{capture', $value);

    $function['string'] = $value;
    $function['execute'] = '';
    return $function;
}
