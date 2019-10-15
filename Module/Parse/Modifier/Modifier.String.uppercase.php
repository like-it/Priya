<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_string_uppercase(Parse $parse, $variable=[], $parameter=[], $token=[], $keep=false){
    $variable['execute'] = modifier_string_uppercase_foreach($variable['execute']);
    $variable['is_executed'] = true;
    $token[$variable['token']['nr']] = $variable;
    return $token;
}

function modifier_string_uppercase_foreach($data=null){
    if(is_string($data)){
        $data = strtoupper($data);
    }
    elseif(is_array($data)){
        foreach($data as $key => $value){
            $data[$key] = modifier_string_uppercase_foreach($value);
        }
    }
    elseif(is_object($data)){
        foreach($data as $key => $value){
            $data->$key = modifier_string_uppercase_foreach($value);
        }
    }
    return $data;
}