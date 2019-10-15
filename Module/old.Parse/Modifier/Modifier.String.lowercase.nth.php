<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_string_lowercase_nth(Parse $parse, $value='', $number=null){    
    if($number === null){
        throw new Exception('Parse error: modifier.string.lowercase.nth needs a position...');
    } else {        
        $value = modifier_string_lowercase_nth_foreach($value, $number);                
    }
    return $value;
}

function modifier_string_lowercase_nth_foreach($data=null, $number=null){
    if(is_string($data)){
        $modify = substr_replace($data, strtolower(substr($data, $number, 1)), $number);
        if(strlen($modify) == strlen($data)){
            $data = $modify;
        } else {
            $data = $modify . substr($data, strlen($modify));
        }
    }
    elseif(is_array($data)){
        foreach($data as $key => $value){
            $data[$key] = modifier_string_lowercase_nth_foreach($value, $number);
        }
    }
    elseif(is_object($data)){
        foreach($data as $key => $value){
            $data->$key = modifier_string_lowercase_nth_foreach($value, $number);
        }
    }
    return $data;
}