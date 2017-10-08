<?php

namespace Priya\Module\Parse;

class Variable extends Core {

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
    }

    public static function type($mixed=null){
        if(is_int($mixed)){
            return Token::TYPE_INT;
        }
        elseif(is_float($mixed)){
            return Token::TYPE_FLOAT;
        }
        elseif(is_bool($mixed)){
            return Token::TYPE_BOOLEAN;
        }
        elseif(is_array($mixed)){
            return Token::TYPE_ARRAY;
        }
        elseif(is_object($mixed)){
            return Token::TYPE_OBJECT;
        }
        elseif(is_object($mixed)){
            return Token::TYPE_OBJECT;
        }
        elseif(Operator::is_arithmetic($mixed)){
            return Token::TYPE_OPERATOR;
        }
        elseif(is_string($mixed)){
            return Token::TYPE_STRING;
        } else {
            return Token::TYPE_NULL;
        }
    }

    public static function value($mixed=null){
        switch($mixed){
            case 'true':
                return true;
            break;
            case 'false':
                return false;
            break;
            case 'null':
                return null;
            break;
            default:
                return $mixed;
            break;
        }
    }

    public function find($record=array()){
        $attribute = substr($record['variable']['tag'], 1, -1);
        $tokens = Token::all($attribute);
        foreach($tokens as $nr => $token){
            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);
                if($record['is_cast'] === true){
                    $record['cast'] = Token::type(Token::get($token));
                }
            }
        }
        if(
            !empty($record['is_cast']) &&
            (
                $record['cast'] == 'boolean' ||
                $record['cast'] == 'bool'
            )
        ){
            $attribute = ltrim(str_replace('(bool)', '', $attribute), ' ');
//             $temp = explode('(bool)', $record['string'], 2);
//             $record['string'] = imlode('', $temp);
        }
        elseif(!empty($record['is_cast']) && !empty($record['cast'])){
            $attribute = ltrim(str_replace('(' . $record['cast'] . ')', '', $attribute), ' ');
//             $temp = explode('(' . $record['cast'] . ')', $record['string'], 2);
//             $record['string'] = implode('', $temp);
        }
        if(substr($attribute,0, 1) != '$'){
            //cast record string
            return $record;
        }
        $modifier_list = explode('|', $attribute);
        $attribute = array_shift($modifier_list);
        if(strpos($attribute, '=') !== false){
            unset($record['cast']);
            unset($record['is_cast']);
            return $record;
        }
        if(!isset($record['string'])){
            return $record;
        }
        $explode = explode($record['variable']['tag'], $record['string'], 2);
        $replace = $this->replace($attribute);
        if(is_object($replace)){
            if(
                isset($replace->__tostring) &&
                !is_array($replace->__tostring) &&
                !is_object($replace->__tostring)
            ){
                $replace = $replace->__tostring;
            } else {
                $replace = ''; //(object) ?
            }
        }
        elseif(is_array($replace)){
            $replace = ''; //(array) ?
        }
        elseif(is_bool($replace)){
            if($replace === true){
                $replace = 'true';
            } else {
                $replace = 'false';
            }
        }
        elseif(is_null($replace)){
            $replace = 'null';
        }
        $item = array();
        $item = $record;
        $item['replace'] = $replace;
        $item = Token::cast($item, 'replace');
        if($item['replace'] === true){
            $item['replace'] = 'true';
        }
        elseif($item['replace'] === false){
            $item['replace'] = 'false';
        }
        elseif($item['replace'] === null){
            $item['replace'] = 'null';
        }
        $record['string'] = implode($item['replace'], $explode);
        unset($record['cast']);
        unset($record['is_cast']);
        return $record;
    }

    public function replace($input=null){
        $original = $input;
        if(
            is_null($input) ||
            is_bool($input) ||
            is_float($input) ||
            is_int($input) ||
            is_numeric($input)
        ){
            return $input;
        }
        if (is_array($input)){
            foreach($input as $nr => $value){
                $input[$nr] = $this->replace($value);
            }
            return $input;
        }
        elseif(is_object($input)){
            foreach ($input as $key => $value){
                $input->{$key} = $this->replace($value);
            }
            return $input;
        } else {
            //remove if statements
            $tag = new Tag($input);
            $list = $tag->find();
            $output = null;
            $output_type = Token::TYPE_NULL;
            $is_set = false;
            $record = array();
            if(empty($list)){
                if(substr($input, 0, 1) == '$'){
                    $attribute = substr($input, 1);
                    if($attribute === false){
                        $output = $input;
                    } else {
                        if($attribute == 'this.what'){
                            debug($input);
//                             debug(debug_backtrace(true));
                            die;
                        }
                        $output = $this->data($attribute);
                        $output = Variable::value($output);
                    }
                } else {
                    $output = $input;
                }
            } else {
                foreach ($list as $nr => $subList){
                    foreach ($subList as $search => $empty){
                        if(substr($search, 1, 1) != '$'){
                            $output = $input;
                            continue;
                        }
                        $attribute = substr($search, 2, -1);
                        $value = $this->data($attribute);
                        $value = Variable::value($value);
                        $type = Variable::type($value);
                        debug($value, 'val2ue'); //set this->Data(attribute,value);
                        debug($attribute, 'attri2bute');
                        debug($input, 'inp2ut');
                        if($output != null){
                            $output_type = Variable::type($output);
                        }
                        if($output === null){
                            if(in_array($type, array(
                                    Token::TYPE_ARRAY,
                                    Token::TYPE_OBJECT
                            ))){
                                $output = $value;
                            } else {
                                $output = str_replace($search, $value, $input);
                                $output = Variable::value($output);
                                $type = Variable::type($value);
                            }
                            //make list?
                            $record['attribute'] = $attribute;
                            $record['search'] = $search;
                            $record['value'] = $value;
                            $record['type'] = $type;
                        } else {
                            if($type == Token::TYPE_OBJECT && $output_type == Token::TYPE_OBJECT){
                                $output = Variable::object_merge($output, $value);
                                continue;
                            }
                            elseif($type == Token::TYPE_ARRAY && $output_type == Token::TYPE_ARRAY){
                                $output = array_merge($output, $value);
                                continue;
                            }
                            if($output_type == Token::TYPE_OBJECT && $type == Token::TYPE_NULL){
                                continue; //add false too ?
                            }
                            if($output_type == Token::TYPE_ARRAY && $type == Token::TYPE_NULL){
                                continue; //add false too ?
                            }
                            if(in_array($output_type, array(
                                    Token::TYPE_ARRAY,
                                    Token::TYPE_OBJECT
                            ))){
                                continue; //preserve arrays and objects
                            }
                            if(empty($is_set)){
                                $output = $input;
                                $is_set = true;
                                $output = str_replace($record['search'], $record['value'], $output);
                            }
                            $output = str_replace($search, $value, $output);
                        }
                    }
                }
            }
            return $output;
        }
    }
}