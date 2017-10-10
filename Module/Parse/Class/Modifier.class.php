<?php

namespace Priya\Module\Parse;

class Modifier extends Core {

    public static function get($parse=array()){
        $modifier = false;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            if($modifier === false){
                $modifier = $record;
                continue;
            }
            if($record['type'] == Token::TYPE_DOUBLE_COLON){
                break;
            }
            if($record['type'] == Token::TYPE_COLON){
                break;
            } else {
                $modifier['value'] .= $record['value'];
            }
        }
        return $modifier;
    }

    public static function is($parse=array()){
        if($parse['value'] == '|'){
            $part = reset($parse['right_parse']);
            if($part['type'] == Token::TYPE_STRING){
                return true;
            }
        }
        return false;
    }

    public static function find($value='', $modifier=''){
        $parse = Token::parse($modifier);
        $modifier = Modifier::get($parse);
        if($modifier === false){
            return $value;
        }
        $argument = Modifier::argument($parse);
        $name = str_replace(
            array(
                '..',
                '//',
                '\\',
            ),
            '',
            ucfirst($modifier['value'])
        );

        $url = __DIR__ . '/../Modifier/Modifier.' . $name . '.php';
        $name = 'modifier_' . str_replace('.', '_', strtolower($name));
        if(file_exists($url)){
            require_once $url;
        } else {
            trigger_error('Modifier (' . $name .') not found (' . $url . ')', E_USER_ERROR);
        }        ;
        $value = $name($value, $argument);

        if(!empty($modifier['is_cast'])){
            $record = array();
            $record['value'] = $value;
            $record['is_cast'] = $modifier['is_cast'];
            $record['cast'] = $modifier['cast'];
            $record= Token::cast($record);
            return $record['value'];

        } else {
            return $value;
        }
    }

    public static function argument($parse=array()){
        $argumentList = array();
        $collect = false;
        $key = 0;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            $record =  Value::get($record);
            if(
                in_array(
                    $record['type'],
                    array(
                        Token::TYPE_COLON,
                        Token::TYPE_DOUBLE_COLON
                    )
                )
            ){
                $key++;
                if($record['type'] == Token::TYPE_DOUBLE_COLON){
                    $argumentList[$key] = null;
                    $key++;
                }
                $collect = true;
                $is_array = false;
                continue;
            }
            if(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == '['
            ){
                $is_array = true;
                $array = array();
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_BRACKET &&
                $record['value'] == ']'
            ){
                $is_array = false;
                $argumentList[$key] = $array;
                continue;
            }
            if($collect === true){
                if(
                    $is_array === true &&
                    $record['type'] != Token::TYPE_COMMA
                ){
                    $array[] = $record['value'];
                } else {
                    if(!isset($argumentList[$key])){
                        $argumentList[$key] = $record['value'];
                    } else {
                        $argumentList[$key] .= $record['value'];
                    }
                }
            }
        }
        return $argumentList;
    }

    public static function execute($operator=array()){
        $modifier = Modifier::get($operator['right_parse']);
        $name = str_replace(
            array(
                '..',
                '//',
                '\\',
            ),
            '',
            ucfirst($modifier['value'])
        );

        $url = __DIR__ . '/../Modifier/Modifier.' . $name . '.php';
        $name = 'modifier_' . str_replace('.', '_', strtolower($name));
        if(file_exists($url)){
            require_once $url;
        } else {
            trigger_error('Modifier (' . $name .') not found (' . $url . ')', E_USER_ERROR);
        }
        $before = reset($operator['left_parse']);
        $argument = Modifier::argument($operator['right_parse']);
        $value = $before['value'];
        $operator['execute'] = $name($value, $argument);
        $operator['value'] = $operator['execute'];
        $part = reset($operator['right_parse']);
        if(!empty($part['is_cast'])){
            $operator['is_cast'] = $part['is_cast'];
            $operator['cast'] = $part['cast'];
        }
        $operator= Token::cast($operator);
        return $operator;
    }

}