<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parser\Token;
use Priya\Module\Parser\Newline;

function function_capture_append($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = trim(array_shift($argumentList), '"\'');
    $value = $function['string'];
    $value = str_ireplace('{capture.append', '{capture.append', $value);
    $value = str_ireplace('{/capture}', '{/capture}', $value);
    $value = str_replace('{capture.append', '{capture.append[' . $parser->random() . ']', $value);

    $explode = explode('{capture.append', $value, 2);

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

        $array = $parser->data($attribute);
        if(empty($array) || !is_array($array)){
            $array = array();
        }
        $tmp[1] = $parser->compile($tmp[1], $data);
        $array[] = trim($tmp[1]);
        $parser->data($attribute, $array);

        $search = '{capture.append' . $string . '{/capture}';

        $value = str_replace($search, '', $value);
    }
    $value = str_replace('{capture.append[' . $parser->random() . ']', '{capture.append', $value);

    $function['string'] = $value;
    $function['execute'] = '';
    return $function;
}
