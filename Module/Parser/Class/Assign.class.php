<?php

namespace Priya\Module\Parser;

use Priya\Module\Core\Object;
use Exception;

class Assign extends Core {

    public static function is_variable($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_VARIABLE){
            return true;
        }
        return false;
    }

    public static function operator($parse=array()){
        foreach($parse as $nr => $record){
            if(Assign::is_operator($record)){
                return $record;
            }
        }
        return array();
    }

    public static function is_operator($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
            return true;
        }
        return false;
    }

    public static function has_operator($parse=array()){
        foreach($parse as $nr => $record){
            if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
                return true;
            }
        }
        return false;
    }

    public static function has_set($parse=array()){
        foreach ($parse as $nr => $record){
            if(!empty($record['is_set'])){
                return true;
            }
        }
        return false;
    }

    public static function remove_set($parse=array()){
        $set = array();
        foreach ($parse as $nr => $record){
            if(isset($record['set'])){
                continue;
            }
            $set[] = $record;
        }
        return $set;
    }

    public static function replace_set($parse=array(), $search=array(), $replace=array()){
        $match = reset($search); //deep //left to right
        $remove = false;
        $is_replace = false;
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && $record['set']['depth'] == $match['set']['depth']){
                if(!empty($remove)){
                    unset($parse[$nr]);
                    return $parse;
                } else {
                    $remove = true;
                }
            }
            if(!empty($remove)){
                if(empty($is_replace)){
                    $parse[$nr] = $replace;
                    $is_replace = true;
                    continue;
                }
                unset($parse[$nr]);
            }
        }
        return $parse;
    }

    public static function get_set($parse=array()){
        $highest = Assign::get_set_highest($parse);
        $is_set = false;
        $set = array();
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && isset($record['set']['depth']) && $record['set']['depth'] == $highest){
                //first one found
                $is_set = true;
                //till first end parenthese
            }
            if($is_set === true){
                $set[] = $record;
                if(isset($record['parenthese']) && $record['parenthese'] == ')' && $record['set']['depth'] == $highest){
                    $is_set = false;
                    return $set;
                }
            }
        }
        return $set;
    }

    public static function get_set_highest($parse=array()){
        $depth = 0;
        foreach ($parse as $nr => $record){
            if(!empty($record['is_set']) && $record['set']['depth'] > $depth){
                $depth = $record['set']['depth'];
            }
        }
        return $depth;
    }

    public static function row($record=array(), $random=''){
        if(!isset($record['string'])){
            throw new Exception('Assign:record no string in row');
            return $record;
        }
        if(is_string($record['string'])){
            $string = Token::restore_return($record['string'], $random);
        } else {
            $string = $record['string'];
        }
        $tag = Token::restore_return($record['assign']['tag'], $random);
        $explode = explode('=', substr($tag, 1, -1), 2);
        if(
            !empty($explode[0]) &&
            substr($explode[0], 0, 1) == '$' &&
            stristr($explode[0], '|') === false &&
            count($explode) == 2
        ){
            $rand = rand(1000, 9999) . '-' . rand(1000, 9999);
            $anchor = '[' . $random . '-' . $rand .  '][anchor]';
            /**
             * it should replace only 1 at a time...
             *
             */
            $tmp = explode($tag, $string, 2);
            if(count($tmp) == 2){
                $string = implode($anchor, $tmp);
            }
            $explode = explode("\n", $string);
            foreach($explode as $nr => $row){
                $tmp = explode($anchor, $row, 2);
                if(count($tmp) == 2){
                    $explode[$nr] = implode('', $tmp);
                }
            }
            $string = implode("\n", $explode);
            $record['string'] = Newline::replace($string, $random);
        }
        return $record;
    }

    public static function find($input=null, $parser=null){
        if(empty($input)){
            return;
        }
        $tag = $input;
        $assign = false;
        $parse = array();
        $count = 0;
        $explode = explode('=', substr($tag, 1, -1), 2);

        if(
            !empty($explode[0]) &&
            substr($explode[0], 0, 1) == '$' &&
            stristr($explode[0], '|') === false &&
            count($explode) == 2
        ){
            $attribute = substr(rtrim($explode[0]), 1);
            $value = trim($explode[1], ' ');
            if(
                substr($attribute,-1) == '-' ||
                substr($attribute,-1) == '+' ||
                substr($attribute,-1) == '.' ||
                substr($attribute,-1) == '!'
            ){
                $assign = substr($attribute, -1) . '=';
                $attribute = substr($attribute, 0, -1);
                $attribute = rtrim($attribute,' ');
            }
            //before create_object assign variable needed
            $create = Token::restore_return($value, $parser->random());
            $original = $create;
            $create = Token::all($create);
            $object = Token::create_object($create, $attribute, $parser);
            if(!empty($object)){
                $object['value'] = Variable::replace($object['value'], '', false, $parser);
                //is variable data changed?
                $object = Token::cast($object);
                $this->data($attribute, $object['value']);
                return;
            }
            $array = Token::create_array($create, $parser);
            if(!empty($array)){
                $array['value'] = Variable::replace($array['value'], '', false, $parser);
                //is variable data changed?
                $array = Token::cast($array);
                $this->data($attribute, $array['value']);
                return;
            }
            //an equation can be a variable, if it is undefined it will be + 0
            $parse = Token::parse($create);
            $parse = Token::variable($parse, $attribute, $parser);
            $method = array();
            $method['parse'] = $parse;
            $method = Token::method($method, $parser);
            $parse = $method['parse'];
            $math = Token::create_equation($parse, $parser);
            if($math !== null){
                $parser->data($attribute, $math);
                return;
            } else {
                $item = array();
                foreach ($parse as $nr => $record){
                    if(empty($record['type'])){
                        continue;
                    }
                    if($record['type'] == Token::TYPE_WHITESPACE){
                        continue;
                    }
                    $record = Value::get($record);
                    if(empty($item)){
                        $item = $record;
                        continue;
                    }
                    if(!empty($item['type']) && $item['type'] != $record['type']){
                        $item['type'] = Token::TYPE_MIXED;
                    }
                    if(isset($item['value']) && isset($record['value']) && $item['type'] == Token::TYPE_STRING || $item['type'] == Token::TYPE_MIXED){
                        $item['value'] .= $record['value'];
                    }
                    elseif(isset($item['value']) && !empty($record['value'])){
                        throw new Exception('Assign:find: record & item set AND NOT TYPE_MIXED OR TYPE_STRING ');
                    }
                }
                if(!isset($item['value'])){
                    //cant use data with value null to set so...
                    $parser->object_delete($attribute, $parser->data()); //for sorting an object
                    $parser->object_set($attribute, null, $parser->data());
                    return;
                }
                if(is_string($item['value'])){
                    $item['value'] = Literal::extra($item['value']);
                    $item['value'] = Newline::replace($item['value'], $parser->random());
                    $item['value'] = Literal::replace($item['value'], $parser->random());
                }
                switch($assign){
                    case '+=' :
                        $plus = $parser->data($attribute) + 0;
                        $parser->data($attribute, $plus += $item['value']);
                    break;
                    case '-=' :
                        $min = $parser->data($attribute) + 0;
                        $parser->data($attribute, $min -= $item['value']);
                    break;
                    case '.=' :
                        $add = $parser->data($attribute);
                        $parser->data($attribute, $add .= $item['value']);
                    break;
                    default :
                        $item = Token::cast($item);
                        $parser->data($attribute, $item['value']);
                    break;
                }
                return;
            }
        }
    }

}