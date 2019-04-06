<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_string_uppercase_nth(Parse $parse, $variable=[], $parameter=[], $token=[], $keep=false){
    if(!isset($parameter[0])){
        throw new Exception('Parse error: modifier.string.uppercase.nth needs a position starting at 0 ...');
    } else {
        $number = $parameter[0];
        $variable['execute'] = modifier_string_uppercase_nth_foreach($variable['execute'], $number);
        $variable['is_executed'] = true;
        $token[$variable['token']['nr']] = $variable;
    }
    return $token;
}

function modifier_string_uppercase_nth_foreach($data=null, $number=null){
    if(is_string($data)){
        $modify = substr_replace($data, strtoupper(substr($data, $number, 1)), $number);
        if(strlen($modify) == strlen($data)){
            $data = $modify;
        } else {
            $data = $modify . substr($data, strlen($modify));
        }
    }
    elseif(is_array($data)){
        foreach($data as $key => $value){
            $data[$key] = modifier_string_uppercase_nth_foreach($value, $number);
        }
    }
    elseif(is_object($data)){
        foreach($data as $key => $value){
            $data->$key = modifier_string_uppercase_nth_foreach($value, $number);
        }
    }
    return $data;
}