<?php

namespace Priya\Module\Parser;

use Priya\Module\Core\Object;
use Exception;

class Assign extends Core {

    private $parser;

    public function __construct($data=null, $random=null, $parser=null){
        $this->data($data);
        $this->random($random);
        $this->parser($parser);
    }

    public function parser($parser=null){
        if($parser !== null){
            $this->setParser($parser);
        }
        return $this->getParser();
    }

    private function setParser($parser=''){
        $this->parser= $parser;
    }

    private function getParser(){
        return $this->parser;
    }

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

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
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
            $create = Token::restore_return($value, $this->random());
            $original = $create;
            $variable = new Variable($this->data(), $this->random(), $this->parser());
            $create = Token::all($create);
            $object = Token::create_object($create, $attribute, $variable, $this->parser());
            if(!empty($object)){
                $object['value'] = $variable->replace($object['value']);
                //is variable data changed?
                $object = Token::cast($object);
                $this->data($attribute, $object['value']);
                return;
            }
            $array = Token::create_array($create, $variable);
            if(!empty($array)){
                $variable = new Variable($this->data(), $this->random(), $this->parser());
                $array['value'] = $variable->replace($array['value']);
                //is variable data changed?
                $array = Token::cast($array);
                $this->data($attribute, $array['value']);
                return;
            }
            $variable = new Variable($this->data(), $this->random(), $this->parser());
            //an equation can be a variable, if it is undefined it will be + 0
            $parse = Token::parse($create);
            $parse = Token::variable($parse, $variable, $attribute);
            $method = array();
            $method['parse'] = $parse;
            $method = Token::method($method, $variable, $this->parser());
            $parse = $method['parse'];
            $math = Token::create_equation($parse, $variable, $this->parser());
            if($math !== null){
                $this->data($attribute, $math);
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
                    $this->object_delete($attribute, $this->data()); //for sorting an object
                    $this->object_set($attribute, null, $this->data());
                    return;
                }
                switch($assign){
                    case '+=' :
                        $plus = $this->data($attribute) + 0;
                        $this->data($attribute, $plus += $item['value']);
                    break;
                    case '-=' :
                        $min = $this->data($attribute) + 0;
                        $this->data($attribute, $min -= $item['value']);
                    break;
                    case '.=' :
                        $add = $this->data($attribute);
                        $this->data($attribute, $add .= $item['value']);
                    break;
                    default :
                        $item = Token::cast($item);
                        $this->data($attribute, $item['value']);
                    break;
                }
                return;
            }
        }
        return;
    }

    private function variable($string='', $type=null){
        $has = false;
        $result = null;
        if($string == 'has' && $type !== null){
            $has = true;
            $string = $type;
        }
        if(
            is_bool($string) ||
            $string== 'true' ||
            $string== 'false' ||
            is_null($string) ||
            $string== 'null' ||
            is_numeric($string) ||
            is_array($string) ||
            is_object($string)
        ){
            if(is_numeric($string)){
                $pos = strpos($string,'0');
                if($pos === 0 && is_numeric(substr($string, 1, 1))){
                } else {
                    $result = $string+ 0;
                }
            }
            elseif(is_bool($string) || $string== 'true' || $string== 'false') {
                $result= (bool) $string;
            }
            elseif(is_null($string) || $string== 'null'){
                $result= null;
            }
            if($has === true){
                return !false; //this means not true but needed for statement default trigger
            }
            return $result;
        }
        $string = trim($string, '\'"');
//         $pattern = '/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        $pattern = '/\{.*\}/';
        preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
        if(count($matches) == 1){
            $result = null;
            foreach ($matches[0] as $key => $search){
                $replace = $this->data(substr($search, 1));
                $is_variable = !(bool) str_replace($search, '', $string);

                if(is_null($replace) && $has === true){
                    return false;
                } elseif($has === true){
                    return true;
                }
                if(
                    (
                        is_bool($replace) ||
                        $replace== 'true' ||
                        $replace== 'false' ||
                        is_null($replace) ||
                        $replace== 'null' ||
                        is_numeric($replace) ||
                        is_array($replace) ||
                        is_object($replace)
                    ) &&
                    $result === null &&
                    $is_variable === true
                ){
                    if(is_numeric($replace)){
                        $pos = strpos($replace,'0');
                        if($pos === 0 && is_numeric(substr($replace, 1, 1))){
                        } else {
                            $result = $replace + 0;
                        }
                    }
                    elseif(is_bool($replace) || $replace== 'true' || $replace== 'false') {
                        $result= (bool) $value;
                    }
                    elseif(is_null($replace) || $replace== 'null'){
                        $result= null;
                    } else {
                        $result = $replace;
                    }
                    break;
                } else {
                    $result = str_replace($search, $replace, $string);
                }
            }
        } else {
            if($has === true){

            }
            if(is_numeric($string)){
                $pos = strpos($string,'0');
                if($pos === 0 && is_numeric(substr($string, 1, 1))){
                } else {
                    $result = $string+ 0;
                }
            }
            elseif(is_bool($string) || $string== 'true' || $string== 'false') {
                $result= (bool) $string;
            }
            elseif(is_null($string) || $string== 'null'){
                $result= null;
            }
            else {
                $result = $string;
            }
        }
        return $result;
    }

}