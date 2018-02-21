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

function function_for_each($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(isset($function['parameter'][0])){
        $attribute = $function['parameter'][0]['variable'];
        if(substr($attribute, 0, 1) == '$'){
            $attribute = substr($attribute, 1);
        }
        $list = $parser->data($attribute);
    }
    if(isset($function['parameter'][2])){
        $key = $function['parameter'][1]['variable'];
        if(substr($key, 0, 1) == '$'){
            $key= substr($key, 1);
        }
        $record = $function['parameter'][2]['variable'];
        if(substr($record, 0, 1) == '$'){
            $record = substr($record, 1);
        }
    }
    elseif(isset($function['parameter'][1])){
        $record = $function['parameter'][1]['variable'];
        if(substr($record, 0, 1) == '$'){
            $record = substr($record, 1);
        }
    } else {
        throw new Exception('Invalid parameter count for for.each');
    }

    $data = $parser->data(); //needs parser...

    $value = $function['string'];
    $value = str_ireplace('{for.each', '{for.each', $value);
    $value = str_ireplace('{/for.each}', '{/for.each}', $value);
    $value = str_replace('{for.each', '{for.each[' . $parser->random() . ']', $value);

    $explode = explode('{for.each', $value, 2);

    $string = '';
    if(isset($explode[1])){
        $foreach = explode('}hcae.rof/{', strrev($explode[1]), 2);
//         $foreach = explode('{/for.each}', $explode[1], 2);
        $foreach[0] = strrev($foreach[0]);
        $foreach[1] = strrev($foreach[1]);

        $string = $foreach[1]; //not 0 (reverse)
    } else {
        //when having multiple for.each we come here again...
        $value = str_replace('{for.each[' . $parser->random() . ']', '{for.each', $value);
        $function['string'] = $value;
        $function['execute'] = '';
        return $function;
    }
    if(!isset($foreach[1])){
        throw new Exception('Missing {/for.each} in {for.each} tag');
    }
    //might add randomizer to be in scope of the foreach only...
    $temp = explode('[' . $parser->random() . '](', $string, 2);
    if(isset($temp[1])){
        $tmp = explode(')}', $temp[1], 2);
    }
    $internal = '';
    foreach($list as $internal_key => $internal_value){
        $parser->data($key, $internal_key);
        $parser->data($record, $internal_value);
        $compile = $parser->compile($tmp[1]);
        $internal .= $compile;
    }
    $search = '{for.each' . $string . '{/for.each}';
    $value = str_replace($search, $internal, $value);
    $value = str_replace('{for.each[' . $parser->random() . ']', '{for.each', $value);

    $function['string'] = $value;
    $function['execute'] = $internal;
    return $function;
}
