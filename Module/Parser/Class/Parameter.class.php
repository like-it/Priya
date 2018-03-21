<?php

namespace Priya\Module\Parser;

Use Exception;

class Parameter extends Core {
    const MAX_ARRAY = 1024;
    const MAX_OBJECT = 1024;

    public static function get($parse=array(), $parser=null){
//         $debug = debug_backtrace(true);
//         var_dump($debug[0]);
//         var_dump($debug[0]['args']);
        $original = $parse;
        $parse = Token::variable($parse, '',$parser);
        $count_array = 0;
        while(Parameter::has_array($parse)){
            $parse = Parameter::create_array($parse);
            if($count_array > Parameter::MAX_ARRAY){
                break;
            }
            $count_array++;
        }
        $count_object = 0;
        while(Parameter::has_object($parse)){
            $parse = Parameter::create_object($parse);
            if($count_object > Parameter::MAX_OBJECT){
                break;
            }
            $count_object++;
        }
        $parameter_list = array();
        $parameter_number = 0;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_COMMA){
                $parameter_number++;
                continue;
            }
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            $record = Value::get($record);
            $record = Token::cast($record);
            $parameter_list[$parameter_number][] = $record;
        }
        $result = array();
        /**
         * if is foreach...
         */
        $is_foreach = false;
        foreach ($parameter_list as $nr => $parameter){
            if(isset($parameter[1])){ //we have a fast multiple check
                foreach($parameter as $part){
                    if($part['type'] == Token::TYPE_AS){
                        $is_foreach = true;
                        break;
                    }
                }
            }
        }
        if($is_foreach){
            $result = array();
            foreach($parameter_list as $nr => $parameter){
                if(isset($parameter[1])){ //we have a fast multiple check
                    foreach($parameter as $part_nr => $part){
                        if($part['type'] == Token::TYPE_AS){
                            continue;
                        }
                        if($part['type'] == Token::TYPE_DOUBLE_ARROW){
                            continue;
                        }
                        $result[] = $part;
                    }
                }
            }
            return $result;
        }

        /**
         * multipart string / variable
         */
        $is_result = false;
        foreach($parameter_list as $parameter){
            if(isset($parameter[1])){ //we have a fast multiple check
                $merge = array();
                foreach($parameter as $part){
                    if(
                        $part['type'] == Token::TYPE_STRING &&
                        $part['value'] == ''
                    ){
                        continue;
                    }
                    if(empty($merge)){
                        $merge = $part;
                        continue;
                    }
                    if($merge['type'] != $part['type']){
                        $merge['type'] == Token::TYPE_MIXED;
                    }
                    if($merge['type'] == Token::TYPE_STRING){ //might also need TYPE_MIXED
                        $merge['value'] .= $part['value'];
                    }
                    elseif(
                        (
                            $merge['type'] == Token::TYPE_INT ||
                            $merge['type'] == Token::TYPE_FLOAT ||
                            $merge['type'] == Token::TYPE_MIXED
                        ) &&
                            $part['type'] == Token::TYPE_STRING
                    ){
                        $merge['value'] .= $part['value'];
                    } else {
                        //int with boolean and string
                        //float with boolean and string
                        //int and boolean
                        //boolean and string
                        //boolean and string and boolean
                        //operator and string
//                         var_dump($merge['type']);
//                         var_dump($parameter);
                        throw new Exception(
                            'Parameter::get:Undefined state detected (' .
                            $merge['type'] .
                            '->' .
                            $part['type'] .
                            '), implementation undefined error...'
                        );
                    }
                }
                $result[] = $merge;
            } else {
                $result[] = $parameter[0];
            }
        }
        return $result;
    }

    public static function has_object($parse=array()){
        $object_open = false;
        $object_close = false;
        foreach($parse as $record){
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '{' &&
                $record['set']['depth'] == 2 //not 0 or 1 but 2 because of method ( & {
            ){
                $object_open= true;
            }
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '}' &&
                $record['set']['depth'] == 2  //not 0 or 1 but 2 because of method ( & {
            ){
                $object_close= true;
            }
            if($object_open=== true && $object_close === true){
                return true;
            }
        }
        return false;
    }


    public static function create_object($parse=array()){
        $item = array();
        $json = '';
        $key = false;
        $collect = false;
        foreach($parse as $nr => $record){
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '{' &&
                $record['set']['depth'] == 2 //not 0 || 1 but 2 because of method ( && {
             ){
                $item = $record;
                $collect = true;
                if(empty($key)){
                    $key = $nr;
                }
                $json .= $record['value'];
                continue;
            }
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '}' &&
                $record['set']['depth'] == 2  //not 0 || 1 but 2 because of method ( && {
            ){
                $collect = false;
                unset($parse[$nr]);
                $json .= $record['value'];
                break;
            }
            if($collect === true){
                if(
                    $record['type'] == Token::TYPE_COMMA ||
                    $record['type'] == Token::TYPE_WHITESPACE
                ){
                    unset($parse[$nr]);
                    $json .= $record['value'];
                    continue;
                }
                $record = Value::format_json($record);
                if($record['type'] == Token::TYPE_BOOLEAN){
                    if(!empty($record['value'])){
                        $json .= 'true';
                    } else {
                        $json .= 'false';
                    }
                } else {
                    $json .= $record['value'];
                }
                unset($parse[$nr]);
            }
        }
        if(isset($key)){
            $item['type'] = Token::TYPE_OBJECT;
            $item['value'] = json_decode($json);
            $parse[$key] = $item;
        }
        return $parse;
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
                $record['value'] == ']' &&
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
        if(isset($key)){
            $item['type'] = Token::TYPE_ARRAY;
            $item['value'] = $array;
            $parse[$key] = $item;
        }
        return $parse;
    }

}