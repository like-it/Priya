<?php

namespace Priya\Module\Parse;

use Priya\Module\Core\Object;

class Method extends Core {
    const MAX = 1024;

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
    }

    public function find($record=array(), Variable $variable, \Priya\Module\Parse $parser){
        $method = substr($record['method']['tag'], 1, -1);
        $parse = Token::parse($method);
        $record['parse'] = $parse;
        $record = Token::method($record, $variable, $parser);
        if($record['parse'][0]['type'] == Token::TYPE_METHOD){
            $method = $record['parse'][0];
        } else {
            return $record;
        }
        $explode = explode($record['method']['tag'], $record['string'], 2);
        if(empty($explode[0]) && empty($explode[1])){
            $record['string'] = $method['value'];
        } else {
            if($method['value'] === true){
                $record['string'] = implode('true', $explode);
            }
            elseif($method['value'] === false){
                $record['string'] = implode('false', $explode);
            }
            elseif($method['value'] === null){
                $record['string'] = implode('null', $explode);
            }
            elseif(is_numeric($method['value'])){
                $record['string'] = implode($method['value'] + 0, $explode);
            }
            elseif(is_string($method['value'])){
                $record['string'] = implode($method['value'], $explode);
            }
            elseif(is_object($method['value']) && isset($method['value']['__tostring'])){
                $record['string'] = implode($method['value']['__tostring'], $explode);
            }
            elseif(is_object($method['value']) && !isset($method['value']['__tostring'])){
                $record['string'] = implode(Method::object($method['value'], 'json'), $explode);
            }
            elseif(is_array($method['value'])){
                $record['string'] = implode(Method::object($method['value'], 'json'), $explode);
            }
        }
        return $record;
    }

    public static function get($parse=array(), Variable $variable, $parser=null, $depth=0){
        $has_string = false;
        $is_method = false;
        $requirement = false;
        $parameter = array();
        $result = array();
        $list = array();
        foreach($parse as $record){
            if(
                $record['type'] == Token::TYPE_METHOD &&
                isset($record['execute']) &&
                $record['execute'] === true
            ){
                continue;
            }
            if(!isset($record['type'])){
                debug($parse);
                debug($record, 'type');
                die;
            }
            if($record['type'] == Token::TYPE_WHITESPACE){
                $list[] = $record;
                continue;
            }
            if($record['type'] == Token::TYPE_OPERATOR){
                $list[] = $record;
                continue;
            }
            /*
            if($record['type'] == Token::TYPE_WHITESPACE){
                $list[] = $record;
                continue;
            }
            */
            if(empty($result)){
                $result = $record;
                $result['method'] = '';
                $result['value'] = '';
            }
            if($record['type'] == Token::TYPE_DOT){
                $result['value'] .= $record['value'];
                $list[] = $record;
            }
            if(
                $record['type'] == Token::TYPE_STRING &&
                $requirement === false
            ){
                $has_string = true;
                $result['method'] .= $record['value'];
                $result['value'] .= $record['value'];
                $list[] = $record;
                continue;
            }
            if(
                $record['type'] == Token::TYPE_METHOD &&
                $requirement === false
            ){
                $has_string = true;
                $result['method'] .= $record['value'];
                $result['value'] .= $record['value'];
                $list[] = $record;
                continue;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == '(' &&
                $record['set']['depth'] == $depth + 1 &&
                $has_string === true
            ){
                $requirement = true;
                $result['value'] .= $record['value'];
                $list[] = $record;
                continue;
            }
            if(
                $requirement === true &&
                !(
                    $record['type'] === Token::TYPE_PARENTHESE &&
                    $record['value'] === ')' &&
                    $record['set']['depth'] == $depth + 1
                )
            ){
                $parameter[] = $record;
                $list[] = $record;
                if($record['type'] != Token::TYPE_DOT){
                    $result['value'] .= $record['value'];
                }
                continue;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == ')' &&
                $record['set']['depth'] == $depth + 1 &&
                $requirement === true
              ){
                $result['value'] .= $record['value'];
                $list[] = $record;
                $is_method = true;
                break;
            }
            if(is_object($record['value'])){
                return false;
            }
            /*
            if(is_bool($record['value'])){
                if(!empty($record['value'])){
                    $result['method'] = 'true';
                } else {
                    $result['method'] = 'false';
                }
            }
            */
            $result['method'] .= $record['value'];
            $list[] = $record;
        }
        if($is_method){
            $result['type'] = Token::TYPE_METHOD;
            $result['parameter'] = Parameter::get($parameter, $variable);
//             $result['parameter'] = Token::method($result['parameter'], $variable, $parser, 1);
            $count = substr_count($result['method'], '!');
            if($count > 0){
                $result['has_exclamation'] = true;
            } else {
                $result['has_exclamation'] = false;
            }
            if($count % 2 == 1){
                $result['invert'] = true;
            } else {
                $result['invert'] = false;
            }
            $result['method'] = str_replace('!', '', $result['method']);
            $result['parse_method'] = $list;
            return $result;
        }
        return false;
    }

    public static function execute($function=array(), \Priya\Module\Parse $parser){
        $url = __DIR__ . '/../Function/Function.' . ucfirst($function['method']) . '.php';
        $name = 'function_' . str_replace('.', '_', strtolower($function['method']));
        if(file_exists($url)){
            require_once $url;
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
            $function = $name($function, $argument, $parser);
            $function['value'] = $function['execute'];

            if($function['has_exclamation'] === true){
                if($function['invert'] === true){
                    if(empty($function['value'])){
                        $function['value'] = true;
                    } else {
                        $function['value'] = false;
                    }
                } else {
                    $function['value'] = (bool) $function['value'];
                }
            }
        } else {
            trigger_error('function (' . $name . ') not exists', E_USER_ERROR);
            die;
        }
        if(is_bool($function['value'])){
            if($function['value'] === true){
                $function['value'] = 'true';
            } else {
                $function['value'] = 'false';
            }
        }
        $function['is_executed'] = true;
        return $function;
    }

}