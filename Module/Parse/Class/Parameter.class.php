<?php

namespace Priya\Module\Parse;

class Parameter extends Core {
    const MAX_ARRAY = 1024;

    public static function get($parse=array(), Variable $variable){
        $parse = Token::variable($parse, $variable);
        $count_array = 0;
        while(Parameter::has_array($parse)){
            $parse = Parameter::create_array($parse);
            if($count_array > Parameter::MAX_ARRAY){
                break;
            }
            $count_array++;
        }
        $result = array();
        foreach($parse as $nr => $record){
            if(
                $record['type'] == Token::TYPE_COMMA ||
                $record['type'] == Token::TYPE_WHITESPACE
            ){
                continue;
            }
            $record= Token::cast($record);
            $result[] = $record;
        }
        return $result;
    }

    public static function has_array($parse=array()){
        $array_open = false;
        $array_close = false;
        foreach($parse as $record){
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '[' &&
                $record['set']['depth'] == 1 //not 0 but 1 because of method (
            ){
                $array_open = true;
            }
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '[' &&
                $record['set']['depth'] == 1  //not 0 but 1 because of method (
            ){
                $array_close = true;
            }
            if($array_open === true && $array_close === true){
                return true;
            }
        }
        return false;
    }

    public static function create_array($parse=array()){
        $item = array();
        $array = array();
        $key = false;
        $collect = false;
        foreach($parse as $nr => $record){
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '[' &&
                $record['set']['depth'] == 1 //not 0 but 1 because of method (
            ){
                $item = $record;
                $collect = true;
                if(empty($key)){
                    $key = $nr;
                }
                continue;
            }
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == ']' &&
                $record['set']['depth'] == 1  //not 0 but 1 because of method (
            ){
                $collect = false;
                unset($parse[$nr]);
                break;
            }
            if($collect === true){
                if(
                    $record['type'] == Token::TYPE_COMMA ||
                    $record['type'] == Token::TYPE_WHITESPACE
                ){
                    unset($parse[$nr]);
                    continue;
                }
                $record = Value::get($record);
                $array[] = $record['value'];
                unset($parse[$nr]);
            }
        }
        if(!empty($key)){
            $item['type'] = Token::TYPE_ARRAY;
            $item['value'] = $array;
            $parse[$key] = $item;
        }
        return $parse;
    }

}