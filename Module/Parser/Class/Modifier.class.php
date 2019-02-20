<?php

namespace Priya\Module\Parser;

use Exception;

class Modifier extends Core {
    const MAX = 1024;

    public static function get($parse=array()){
        $modifier = false;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            if($record['value'] == '|'){
                if($modifier === false){
                    continue;
                } else {
                    break;
                }
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

    public static function remove($parse=array()){
        $modifier = false;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                unset($parse[$nr]);
                continue;
            }
            if($record['value'] == '|'){
                if($modifier === false){
                    unset($parse[$nr]);
                    continue;
                } else {
                    break;
                }
            }
            if($modifier === false){
                $modifier = $record;
                unset($parse[$nr]);
                continue;
            }
            if($modifier !== false){
                unset($parse[$nr]);
            }
        }
        return $parse;
    }

    public static function pipe($parse=array(), $random=''){
        $string = '';
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_STRING){
                $record['value'] = str_replace('|', '[' . $random . '][pipe]', $record['value']);
            }
            $string .= $record['value'];
        }
        return $string;
    }

    /**
     * @todo
     * -    multiple modifiers;
     */
    public static function find($value='', $modifier='', $parser=null){
        if(is_array($modifier)){
            $string = array();
            foreach($modifier as $nr =>  $part){
                $string[$nr] = Modifier::pipe($part, $parser->random());
            }
            $modifier = implode(' | ', $string);
        } else {
            $temp = Token::parse($modifier);
            $modifier = Modifier::pipe($temp, $parser->random());
        }
        $parse = Token::parse($modifier);
        $counter = 0;
        while($modifier = Modifier::get($parse)){
            if($modifier === false){
                return $value;
            }
            $argument = Modifier::argument($parse, $parser);

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
                throw new Exception('Modifier (' . $name .') not found (' . $url . ')');
            }
            $value = $name($value, $argument, $parser);
            if(!empty($modifier['is_cast'])){
                $record = array();
                $record['value'] = $value;
                $record['is_cast'] = $modifier['is_cast'];
                $record['cast'] = $modifier['cast'];
                $record = Token::cast($record);
                $value = $record['value'];
            }
            $parse = Modifier::remove($parse);
            $counter++;
            if($counter > Modifier::MAX){
                break;
            }
        }
        return $value;
    }

    public static function argument($parse=array(), $parser=null){
        $argumentList = array();
        $collect = false;
        $key = 0;
        $is_modifier = false;
        $depth = null;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            if($record['value'] == '|'){
                if($is_modifier === false){
                    $is_modifier = true;
                } else {
                    return $argumentList;
                }
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
                $depth = $record['set']['depth'];
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
                if($record['value'] == '}' && $record['set']['depth'] == $depth){
                    break;
                }
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
        foreach($argumentList as $key => $argument){
            if(substr($argument, 0, 1) == '$'){
                $attribute = substr($argument, 1);
                if($attribute === false){
                    continue;
                } else {
                    $argumentList[$key] = Variable::value(
                        $parser->data($attribute)
                    );
                }
            }
        }
        $result = array();
        foreach ($argumentList as $argument){
            $result[] = Token::literal_restore($argument, $parser->random());
        }
        return $result;
    }

    public static function execute($operator=array(), $parser=null){
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
            throw new Exception('Modifier (' . $name .') not found (' . $url . ')');
        }
        $value = '';
        foreach ($operator['left_parse'] as $before){
            if($before['value'] == '{'){
                break;
            }
            $value .= $before['value'];
        }
        $argument = Modifier::argument($operator['right_parse'], $parser);
        $operator['execute'] = $name($value, $argument, $parser);
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