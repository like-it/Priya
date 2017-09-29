<?php

namespace Priya\Module\Parse;

class Method extends Core {

    public static function get($parse=array(), Variable $variable){
        $has_string = false;
        $is_method = false;
        $requirement = false;
        $parameter = array();
        $result = array();
        foreach($parse as $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            if(empty($result)){
                $result = $record;
                $result['method'] = '';
                $result['value'] = '';
            }
            if($record['type'] == Token::TYPE_DOT){
                $result['value'] .= $record['value'];
            }
            if(
                $record['type'] == Token::TYPE_STRING &&
                $requirement === false
            ){
                $has_string = true;
                $result['method'] .= $record['value'];
                $result['value'] .= $record['value'];
                continue;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == '(' &&
                $record['set']['depth'] == 1 &&
                $has_string === true
            ){
                $requirement = true;
                $result['value'] .= $record['value'];
                continue;
            }
            if(
                $requirement === true &&
                !(
                    $record['type'] === Token::TYPE_PARENTHESE &&
                    $record['value'] === ')' &&
                    $record['set']['depth'] === 1
                )
            ){
                $parameter[] = $record;
                $result['value'] .= $record['value'];
                continue;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == ')' &&
                $record['set']['depth'] == 1 &&
                $requirement === true
              ){
                $result['value'] .= $record['value'];
                $is_method = true;
                break;
            }
            if(is_object($record['value'])){
                return false;
            }
            $result['method'] .= $record['value'];
        }
        if($is_method){
            $result['type'] = Token::TYPE_METHOD;
            $result['parameter'] = Parameter::get($parameter, $variable);
            //add cast as well on the method...
            return $result;
        }
        return false;
    }

    public static function execute($function=array(), Variable $variable){
        $url = __DIR__ . '/../Function/Function.' . ucfirst($function['method']) . '.php';
        $name = 'function_' . str_replace('.', '_', strtolower($function['method']));
        if(file_exists($url)){
            require_once $url;
        } else {
            debug($url . ' not found');
        }
        if(function_exists($name)){
            $argument = array();
            if(isset($function['parameter'])){
                foreach ($function['parameter'] as $parameter){
                    if(isset($parameter['value']) || $parameter['value'] === null){
                        $argument[] = $parameter['value'];
                    }
                }
            }
            $function['execute'] = $name($argument, $variable);
            $function['value'] = $function['execute'];
        } else {
            debug('function (' . $name . ') not exists');
        }
        return $function;
    }

}