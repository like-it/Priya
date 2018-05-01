<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_capture_prepend($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = trim(array_shift($argumentList), '"\'');

    $value = $function['string'];
    $value = str_ireplace('{capture.prepend', '{capture.prepend', $value);
    $value = str_ireplace('{/capture}', '{/capture}', $value);
    $value = str_ireplace('{/capture.prepend}', '{/capture.prepend}', $value);
    $value = str_replace('{capture.prepend', '{capture.prepend[' . $parser->random() . ']', $value);

    $explode = explode('{capture.prepend', $value, 2);

    $string = '';
    $is_append = false;
    if(isset($explode[1])){
        $capture = explode('{/capture}', $explode[1], 2);
        if(!isset($capture[1])){
            $capture = explode('{/capture.prepend}', $explode[1], 2);
            $is_append = true;
        }
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
//         debug($tmp[1], __LINE__ . '::' . __FILE__);
        array_unshift($array, $tmp[1]);

        $parser->data($attribute, $array);
        if($is_append){
            $search = '{capture.prepend' . $string . '{/capture.prepend}';
        } else {
            $search = '{capture.prepend' . $string . '{/capture}';
        }

        $value = str_replace($search, '', $value);
    }
    $value = str_replace('{capture.prepend[' . $parser->random() . ']', '{capture.prepend', $value);

    $function['string'] = $value;
    $function['execute'] = '';
    return $function;
}
