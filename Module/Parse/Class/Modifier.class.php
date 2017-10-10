<?php

namespace Priya\Module\Parse;

class Modifier extends Core {

    public static function get($parse=array()){
        $modifier = false;
        foreach($parse['right_parse'] as $nr => $record){
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

    public static function execute($operator=array(), Variable $variable, $parser=null){
        $modifier = Modifier::get($operator);
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
        $value = $before['value'];
        $argumentList = array();
        $collect = false;
        $key = 0;
        foreach($operator['right_parse'] as $nr => $parse){
            if(
                in_array(
                    $parse['type'],
                    array(
                        Token::TYPE_COLON,
                        Token::TYPE_DOUBLE_COLON
                    )
                )
            ){
                $key++;
                if($parse['type'] == Token::TYPE_DOUBLE_COLON){
                    $argumentList[$key] = null;
                    $key++;
                }
                $collect = true;
                $is_array = false;
                continue;
            }
            if(
                $parse['type'] == Token::TYPE_BRACKET &&
                $parse['value'] == '['
            ){
                $is_array = true;
                $array = array();
                continue;
            }
            elseif(
                $parse['type'] == Token::TYPE_BRACKET &&
                $parse['value'] == ']'
            ){
                $is_array = false;
                $argumentList[$key] = $array;
                continue;
            }
            if($collect === true){
                if(
                    $is_array === true &&
                    $parse['type'] != Token::TYPE_COMMA
                ){
                    $array[] = $parse['value'];
                } else {
                    if(!isset($argumentList[$key])){
                        $argumentList[$key] = $parse['value'];
                    } else {
                        $argumentList[$key] .= $parse['value'];
                    }
                }
            }
        }
        $operator['execute'] = $name($value, $argumentList);
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