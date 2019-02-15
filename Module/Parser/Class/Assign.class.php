<?php

namespace Priya\Module\Parser;

use Exception;

class Assign extends Core {
    CONST STATUS = 'is_assign';

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
            $string = Token::newline_restore($record['string'], $random);
        } else {
            $string = $record['string'];
        }
        if(!isset($record['assign'])){
            return $record;
        }
        if(!isset($record['assign']['tag'])){
            return $record;
        }
        if(substr($record['assign']['tag'], 1, 1) != '$'){
            return $record;
        }
        $tag = Token::newline_restore($record['assign']['tag'], $random);
        $explode = explode('=', substr($tag, 1, -1), 2);
        if(
            count($explode) == 2
        ){
            $tmp = explode($tag, $string, 2);
            if(isset($tmp[1])){
                $explode = explode("\n", $tmp[1], 2);
                if(isset($explode[1])){
                    $empty = trim($explode[0]);
                    if(empty($empty)){
                        $tmp[1] = $explode[1];
                    }
                }
                $string = implode('', $tmp);
            }
            $record['string'] = Token::newline_replace($string, $random);
            $record['status'] = Assign::STATUS;
            return $record;
        }
        $explode = explode('++', substr($tag, 1, -1), 2);
        if(
            count($explode) == 2
        ){
            $tmp = explode($tag, $string, 2);
            if(isset($tmp[1])){
                $explode = explode("\n", $tmp[1], 2);
                if(isset($explode[1])){
                    $empty = trim($explode[0]);
                    if(empty($empty)){
                        $tmp[1] = $explode[1];
                    }
                }
                $string = implode('', $tmp);
            }
            $record['string'] = Token::newline_replace($string, $random);
            $record['status'] = Assign::STATUS;
            return $record;
        }
        $explode = explode('--', substr($tag, 1, -1), 2);
        if(
            count($explode) == 2
        ){
            $tmp = explode($tag, $string, 2);
            if(isset($tmp[1])){
                $explode = explode("\n", $tmp[1], 2);
                if(isset($explode[1])){
                    $empty = trim($explode[0]);
                    if(empty($empty)){
                        $tmp[1] = $explode[1];
                    }
                }
                $string = implode('', $tmp);
            }
            $record['string'] = Token::newline_replace($string, $random);
            $record['status'] = Assign::STATUS;
            return $record;
        }
        return $record;
    }

    public static function find($record=null, $parser=null){
        if(isset($record) && isset($record['assign']) && isset($record['assign']['tag'])){
            $input = $record['assign']['tag'];
        }
        if(empty($input)){
            return $record;
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
            $create = Token::newline_restore($value, $parser->random());
            $original = $create;
            $create = Token::all($create);
            $object = Token::create_object($create, $attribute, $parser);
            if(!empty($object)){
                $object['value'] = Variable::replace($object['value'], '', false, $parser);
                //is variable data changed?
                $object = Token::cast($object);
                $parser->data($attribute, $object['value']);
                return $record;
            }
            $array = Token::create_array($create, $parser);
            if(!empty($array)){
                $array['value'] = Variable::replace($array['value'], '', false, $parser);
                //is variable data changed?
                $array = Token::cast($array);
                $parser->data($attribute, $array['value']);
                return $record;
            }
            //an equation can be a variable, if it is undefined it will be + 0
            $parse = Token::parse($create);
            $parse = Token::variable($parse, $attribute, $parser);
            $method = array();
            $method['parse'] = $parse;
            $method['string'] = $record['string'];
            $method['original'] = $record['original'];
            $method['assign'] = $record['assign'];
            $method['key'] = $record['assign']['tag'];
            $method = Token::method($method, $parser);
            $parse = $method['parse'];
            $math = Token::create_equation($parse, $parser);
            $original = $record;
            $original['string'] = $method['string'];
            if($math !== null){
                $parser->data($attribute, $math);
                return $original;
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
                    return $original;
                }
                if(is_string($item['value'])){
                    $item['value'] = Token::literal_extra($item['value']);
                    $item['value'] = Token::newline_replace($item['value'], $parser->random());
                    $item['value'] = Token::literal_replace($item['value'], $parser->random());
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
                return $original;
            }
        } else {
            Assign::plusPlus($tag, $parser);
            Assign::minusMinus($tag, $parser);
        }
        return $record;
    }

    public static function minusMinus($tag='', $parser=null){
        return Assign::counter($tag,'--', $parser);
    }

    public static function plusPlus($tag='', $parser=null){
       return Assign::counter($tag,'++', $parser);
    }

    public static function counter($tag='', $sign='++', $parser=null){
        $explode = explode($sign, substr($tag, 1, -1), 2);
        if(isset($explode[1])){
            $attribute = substr(rtrim($explode[0]), 1);
            if(
                in_array(
                    $attribute,
                    [
                        'delete'
                    ]
                    )
                ){
                    return $record;
            }
            $value = $parser->data($attribute);
            if($value === null){
                $value = 0;
            }
            switch($sign){
                case '++' :
                    $value++;
                    $parser->data($attribute, $value);
                    break;
                case '--' :
                    $value--;
                    $parser->data($attribute, $value);
                    break;
            }
        }
    }

    public static function list($list=array(), $record=array()){
        if(isset($record['status']) && $record['status'] == Assign::STATUS){
            foreach($list as $nr => $attribute){
                if(isset($attribute[$record['assign']['tag']])){
                    unset($list[$nr]);
                }
            }
        }
        return $list;
    }
}