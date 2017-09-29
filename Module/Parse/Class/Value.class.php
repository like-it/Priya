<?php

namespace Priya\Module\Parse;

class Value extends Core {

    public static function get($record=array()){
        if($record['type'] == Token::TYPE_STRING && isset($record['value']) && substr($record['value'], 0, 1) == '\'' && substr($record['value'], -1, 1) == '\''){
            $record['value'] = substr($record['value'], 1, -1);
            $record['value'] = str_replace('\\\'', '\'', $record['value']);
        }
        elseif($record['type'] == Token::TYPE_STRING && isset($record['value']) && substr($record['value'], 0, 1) == '"' && substr($record['value'], -1, 1) == '"'){
            $record['value'] = substr($record['value'], 1, -1);
            $record['value'] = str_replace('\"', '"', $record['value']);
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

            } else {
                switch($record['value']){
                    case ':';
                    case '{';
                    case '}':
                    break;
                    default:
                        $record['value'] = '"' . $record['value'] . '"';
                    break;
                }
            }

        }
        return $record;
    }
}