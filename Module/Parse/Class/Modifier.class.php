<?php

namespace Priya\Module\Parse;

class Modifier extends Core {

    public static function execute($operator=array(), Variable $variable, $parser=null){
        $modifier = reset($operator['right_parse']);
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
            if($parse['type'] == Token::TYPE_COLON){
                $key++;
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
        debug($operator['value']);
        return $operator;
    }

}