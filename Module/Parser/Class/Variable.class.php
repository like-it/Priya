<?php

namespace Priya\Module\Parser;

class Variable extends Core {

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
        if(is_numeric($mixed)){
            return $mixed + 0;
        }
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

    public static function fix($parse=array()){
        $result = array();
        $is_variable = false;
        $has_variable = false;
        $collect = false;
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_VARIABLE){
                $has_variable = true;
                $is_variable = $nr;
                $result[$nr] = $record;
                continue;
            }
            if($is_variable !== false){
                if(substr($record['value'], 0, 1) == '.'){
                    $collect = true;
                }
            }
            if(
                $collect === true &&
                !in_array(
                    $record['type'],
                    array(
                        Token::TYPE_DOT,
                        Token::TYPE_STRING
                    )
                )
            ){
                $collect = false;
            }
            if(
                $collect === true &&
                $record['value'] == '"'
            ){
                $collect = false;
            }
            if($collect === true){
                $result[$is_variable]['value'] .= $record['value'];
                continue;
            }
            $result[$nr] = $record;
        }
        if($has_variable === true){
            foreach($result as $nr => $record){
                if($record['type'] == Token::TYPE_VARIABLE){
                    $previous = prev($result);
                    $key = key($result);
                    if($previous['value'] == '"'){
                        unset($result[$key]);
                    }
                    $next = next($result);
                    $key = key($result);
                    if($next['value'] == '"'){
                        unset($result[$key]);
                        break;
                    }
                }
                next($result);
            }
        }
        return $result;
    }

    public function find($record=array(), $keep=false){
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
        }
        elseif(!empty($record['is_cast']) && !empty($record['cast'])){
            $attribute = ltrim(str_replace('(' . $record['cast'] . ')', '', $attribute), ' ');
        }
        if(substr($attribute,0, 1) != '$'){
            //cast record string
            return $record;
        }
        $modifier_list = explode('|', $attribute);
        $attribute = trim(array_shift($modifier_list), ' ');
        if(!empty($modifier_list)){
            $modifier = Token::restore_return(implode('|', $modifier_list), $this->random());
        } else {
            $modifier = '';
        }
        if(strpos($attribute, '=') !== false){
            unset($record['cast']);
            unset($record['is_cast']);
            return $record;
        }
        if(!isset($record['string'])){
            return $record;
        }
        $explode = explode($record['variable']['tag'], $record['string'], 2);
        $replace = $this->replace($attribute, $modifier, $keep);
        if(is_object($replace)){
            if(
                isset($replace->__tostring) &&
                !is_array($replace->__tostring) &&
                !is_object($replace->__tostring)
            ){
                $replace = $replace->__tostring;
            }
        }
        elseif(is_array($replace)){
            //do nothing with replace
        }
        elseif(is_bool($replace)){
            if($replace === true){
                $replace = 'true';
            } else {
                $replace = 'false';
            }
        }
        elseif(is_null($replace) && $keep == false){
            $replace = 'null';
        }
        elseif(is_null($replace) && $keep == true){
            $replace = $record['variable']['tag'];
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
        if(is_array($item['replace']) || is_object($item['replace'])){
            $record['string'] = $item['replace'];
        } else {
            $record['string'] = implode($item['replace'], $explode);
        }

        unset($record['cast']);
        unset($record['is_cast']);
        if($record['string'] == 'null'){
            $record['string'] = null;
        }
        elseif($record['string'] == 'true'){
            $record['string'] = true;
        }
        elseif($record['string'] == 'false'){
            $record['string'] = false;
        }
        return $record;
    }

    public function replace($input=null, $modifier='', $keep=false){
        $original = $input;
        if(
            (
                is_null($input) ||
                is_bool($input) ||
                is_float($input) ||
                is_int($input) ||
                is_numeric($input)
            ) &&
            $modifier = ''
        ){
            return $input;
        }
        if (is_array($input)){
            foreach($input as $nr => $value){
                $input[$nr] = $this->replace($value, $modifier);
            }
            return $input;
        }
        elseif(is_object($input)){
            foreach ($input as $key => $value){
                $input->{$key} = $this->replace($value, $modifier);
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
                        $output = $this->data($attribute);
                        if($output === null && $keep === true){
                            return $output;
                        }
                        $output = Variable::value($output);
                        $output = Modifier::find($output, $modifier, $this, $this->parser());
                        var_dump($output);
//                         var_dump(debug_backtrace(true));
//                         die;
                        $output = $this->parser()->compile($output, $this->parser()->data());
                        //parse comile again on output
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
                        debug($attribute);
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