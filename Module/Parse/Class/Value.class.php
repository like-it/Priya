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
            $record['value'] = str_replace('\\\'', '\'', $record['value']);
        }
        return $record;
    }
}