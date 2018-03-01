<?php

namespace Priya\Module\Parser;

use Exception;

class Value extends Core {

    public static function get($record=array()){
        if(
            $record['type'] == Token::TYPE_STRING &&
            isset($record['value']) &&
            substr($record['value'], 0, 1) == '\'' &&
            substr($record['value'], -1, 1) == '\''
        ){
            $record['value'] = substr($record['value'], 1, -1);
            $record['value'] = str_replace('\\\'', '\'', $record['value']);
        }
        elseif(
            $record['type'] == Token::TYPE_STRING &&
            isset($record['value']) &&
            substr($record['value'], 0, 1) == '"' &&
            substr($record['value'], -1, 1) == '"'
        ){
            $record['value'] = substr($record['value'], 1, -1);
            $record['value'] = str_replace('\r', "\r", $record['value']);
            $record['value'] = str_replace('\n', "\n", $record['value']);
            $record['value'] = str_replace('\s', "\s", $record['value']);
            $record['value'] = str_replace('\t', "\t", $record['value']);
            $record['value'] = str_replace('\"', '"', $record['value']);
        }
        return $record;
    }

    public static function type($record=array()){
        if(!isset($record['value'])){
            $record['value'] = null;
        }
        if(is_null($record['value'])){
            $record['type'] = Token::TYPE_NULL;
        }
        elseif(is_string($record['value'])){
            $record['type'] = Token::TYPE_STRING;
        }
        elseif(is_bool($record['value'])){
            $record['type'] = Token::TYPE_BOOLEAN;
        }
        elseif(is_int($record['value'])){
            $record['type'] = Token::TYPE_INT;
        }
        elseif(is_float($record['value'])){
            $record['type'] = Token::TYPE_FLOAT;
        }
        elseif(is_array($record['value'])){
            $record['type'] = Token::TYPE_ARRAY;
        }
        elseif(is_object($record['value'])){
            $record['type'] = Token::TYPE_OBJECT;
        } else {
            throw new Exception('Value::type:Unknown type');
        }
        return $record;
    }

    public static function format_json($record=array()){
        if($record['type'] == Token::TYPE_STRING && isset($record['value']) && substr($record['value'], 0, 1) == '\'' && substr($record['value'], -1, 1) == '\''){
            $record['value'] = substr($record['value'], 1, -1) ;
            $record['value'] = str_replace('\\\'', '\'', $record['value']);
            $record['value'] = str_replace('"', '\"', $record['value']);
            $record['value'] = '"' . $record['value'] . '"';
        }
        if(substr($record['value'], 0, 1) == '"' && substr($record['value'], -1, 1) == '"'){
            //do nothing
        } else {
            if(is_numeric($record['value'])){
                $record['value'] += 0;
            }
            elseif(is_bool($record['value'])){
                //do nothing
            }
            elseif(is_null($record['value'])){
                //do nothing
            }else {
                switch($record['value']){
                    case ':';
                    case '{';
                    case '}':
                    case '[':
                    case ']':
                    break;
                    default:
                        $record['value'] = '"' . $record['value'] . '"';
                    break;
                }
            }
            if(substr($record['value'], 0, 2) == '"{' && substr($record['value'], -2 ,2) == '}"'){
                $record['value'] = substr($record['value'], 1, -1);
            }
        }
        return $record;
    }
}