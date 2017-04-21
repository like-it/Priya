<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use stdClass;
use Priya\Application;

class Parser extends Data {
    const DIR = __DIR__;

    public function __construct($handler=null, $route=null, $data=null){
        $this->data($this->object_merge($this->data(), $handler));
    }

    public function compile($string='', $data, $keep=false){
        $input = $string;
        if (is_array($string)){
            foreach($string as $nr => $line){
                $string[$nr] = $this->compile($line, $data, $keep);
            }
            return $string;
        }
        elseif(is_object($string)){
            foreach ($string as $key => $value){
                $string->{$key} = $this->compile($value, $data, $keep);
            }
            return $string;
        } else {
            $list =  $this->attributeList($string);
            $attributeList = array();
            if(empty($list)){
                return $string;
            }
            $data = $this->object($data);
            foreach($list as $key => $value){
                if(substr($key, 1, 1) != '$'){
                    continue;
                }
                $modifierList = explode('|', trim($key,'{}$ '));
                $attribute = trim(array_shift($modifierList));
                if($keep === 'disable-modify'){
                    $modifierList = array();
                }
                $modify = $this->object_get($attribute, $data);
                if($modify === null){
                    $modify = $this->modify('', $modifierList);
                } else {
                    $modify = $this->modify($modify, $modifierList);
                }
                if($modify===false){
                    continue;
                }
                $list[$key] = $modify;
                $attributeList[$key] = $modify;
            }
            foreach($attributeList as $search => $replace){
                $replace = $this->compile($replace, $data, $keep);
                if(empty($replace) && !empty($keep)){
                    continue;
                }
                if(is_object($replace)){
                    $replace = $this->object($replace, 'json');
                }
                $string = str_replace($search, $replace, $string);
            }
            foreach($list as $key => $value){
                if(substr($key, 1, 1) == '$'){
                    continue;
                }
                $temp = explode(' ', $string);
                $function = ltrim(reset($temp), '{');
                $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
                if(in_array($function, array('if'))){
                    $url = $dir . 'control.' . $function . '.php';
                    $function = 'control_' . $function;
                } else {
                    $url = $dir . 'function.' . $function . '.php';
                    $function = 'function_' . $function;
                }
                if(file_exists($url)){
                    require_once $url;
                } else {
                    var_dump('missing file: ' . $url);
                    //remove function ?
                    continue;
                }

                if(function_exists($function) === false){
                    var_dump('missing function: ' . $function);
                    //trigger error?
                    continue;
                }
                if(strpos($function, 'control') === 0){
                    if($key == '{/if}'){
                        $argumentList = $this->createArgumentListIf($string);
                        foreach($argumentList as $nr => $argument){
                            $methodList = $this->createMethodList($argument['statement']);
                            $argumentList[$nr]['methodList'] = $methodList;
                        }
                        $string =  $function($string, $argumentList, $this);
                    }
                    /*

                    foreach($argumentList as $nr => $argument){
                        //if argument['statement'] == function
                    }
                    var_dump('---------------------------------------');
                    var_dump($key);
                    var_dump($argumentList);
//                     $argumentList= $this->compile($argumentList, $this->data());
                    $string =  $function($string, $argumentList, $this);
                    */
                } else {
                    $argumentList = $this->createArgumentList($list);
                    $argumentList= $this->compile($argumentList, $this->data());
                    $string =  $function($string, $argumentList, $this);
                }
            }
            return $string;
        }
    }

    public function replace($search='', $replace='', $data=''){
        return $data;
        if(is_string($data)){
            return str_replace($search, $replace, $data);
        }
        foreach($data as $key => $value){
            if(is_string($value)){
                if(stristr($search, $value) !== false){
                    echo 'Found';
                }
                if(is_array($data)){
                    $data[$key] = str_replace($search, $replace, $value);
                } elseif(is_object($data)){
                    $data->$key = str_replace($search, $replace, $value);
                }
            } else {
                if(is_array($data)){
                    $data[$key] = $this->replace($search, $replace, $value);
                } elseif(is_object($data)){
                    $data->$key = $this->replace($search, $replace, $value);
                }
            }
        }
        return $data;
    }

    public function read($url=''){
        $read = parent::read($url);
        if(!empty($read)){
            return $this->data($this->compile($this->data(), $this->data()));
        }
        return $read;
    }

    public function recursive_compile($list='', $children='nodeList'){
        if(is_object($list)){
            foreach ($list as $jid => $node){
                if(isset($node->{$children})){
                    $node->{$children} = $this->recursive_compile($node->{$children}, $children);
                }
                $node = $this->compile($node, $node);
            }
        }
        return $list;
    }

    private function createMethodList($statement=''){
        $temp = $this->explode_multi(
            array(
                '&&',
                '||',
                'and',
                'or',
                'xor',
                '==',
                '===',
                '<>',
                '!=',
                '!==',
                '<',
                '<=',
                '>',
                '>=',
                '<=>'
            ),
            $statement
         );
        $methodList = array();
        foreach ($temp as $nr => $part){
            $method = explode('(', $part, 2);
            if(count($method) == 1){
                return $methodList;
            }
            $inSet = false;
            foreach($method as $key => $value){
                if(empty($value)){
                    $inSet = true;
                    continue;
                }
                if(!empty($inSet)){
                    $method = explode('(', $value, 2);
                    break;
                }
            }
            foreach($method as $key => $value){
                $method[$key] = trim($value);
            }
            $function_key = trim($part, ' ()') . ')';
            $function = array();
            $function[$function_key]['function'] = array_shift($method);
            $arguments = reset($method);
            $arguments = strrev($arguments);
            $args = explode(')', $arguments, 2);
            array_shift($args);
            $arguments = reset($args);
            $arguments = strrev($arguments);

            $args = explode(',', $arguments);
            $array = false;
            $list = array();
            foreach($args  as $key => $value){
                $arg = trim($value);
                if(strpos($arg, '[') === 0){
                    $array = array();
                    $array[] = $value;
                    continue;
                }
                if(strpos($arg, 'array(') === 0){
                    $array = array();
                    $array[] = $value;
                    continue;
                }
                $arg = strrev($arg);
                if(strpos($arg, ']') === 0){
                    $array[] = $value;
                    $list[] = implode(',', $array);
                    $array = false;
                    continue;
                }
                if(strpos($arg, ')') === 0){
                    $array[] = $value;
                    $list[] = implode(',', $array);
                    $array = false;
                    continue;
                }
                if(!empty($array)){
                    $array[] = $value;
                } else {
                    $list[] = $value;
                }
            }
            $function[$function_key]['argumentList'] = $list;
            $methodList[$statement][] = $function;
        }
        return $methodList;


        /*
        foreach($temp as $nr => $part){
            $sep = explode(')', $part);
            var_dump($sep);
        }
        var_dump('------------------------');
        */

//         var_dump($temp);
    }

    private function createArgumentList($list=array()){
        if(!is_array($list)){
            $list = (array) $list;
        }
        $attribute = reset($list);
        if(empty($attribute)){
            return array();
        }
        $attribute = explode('="', $attribute);
        $argumentList = array();
        $index = false;
        foreach($attribute as $key => $value){
            $index = explode(' ', $value, 2);
            array_shift($index);
            $index = implode(' ', $index);
            if(empty($index)){
                continue;
            }
            if(isset($attribute[$key+1])){
                $temp = $attribute[$key+1];
                $temp = explode('"', str_replace('\"', '__internal_quote', $temp), 2); //maybe add str_Replace('\"', to temp
                $temp = reset($temp);
                $temp = str_replace('__internal_quote', '"', $temp);
                $argumentList[$index] = $temp;
            }
        }
        foreach($argumentList as $key => $value){
            if(substr($value,0,1) == '[' && substr($value,-1,1) == ']'){
                $temp = explode(',', substr($value, 1, -1));
                foreach($temp as $temp_key => $temp_value){
                    $temp[$temp_key] = trim($temp_value);
                }
                $argumentList[$key] = $temp;
            }
        }

        return $argumentList;
    }

    private function createArgumentListIf($list=array()){
        if(!is_array($list)){
            $list = (array) $list;
        }
        $attribute = reset($list);
        if(empty($attribute)){
            return array();
        }
        $attribute = explode('{if', $attribute);
        $argumentList = array();
        $index = false;
        foreach($attribute as $key => $value){
            $temp = explode('}', $value, 2);
            $statement = trim(reset($temp));
            if(empty($statement)){
                continue;
            }
            //explode statements on space
//             var_dump($statement);
            $else = explode('{else}', end($temp));
            if(count($else) == 1){
                //no else
            } else {
                $true = reset($else);
                $false = rtrim(end($else), '{/if}');
            }
            $argument = array();
            $argument['statement'] = $statement;
            $argument['true'] = $true;
            $argument['false'] = $false;

            $argumentList[] = $argument;
        }
        /*
        foreach($argumentList as $key => $value){
            if(substr($value,0,1) == '[' && substr($value,-1,1) == ']'){
                $temp = explode(',', substr($value, 1, -1));
                foreach($temp as $temp_key => $temp_value){
                    $temp[$temp_key] = trim($temp_value);
                }
                $argumentList[$key] = $temp;
            }
        }
        */
        return $argumentList;
    }

    private function attributeList($string=''){
        $function = explode('function(', $string);
        foreach($function as $function_nr => $content){
            $attributeList = array();
            $list = explode('{', $string);

            if(empty($list)){
                return $string;
            }
            foreach($list as $nr => $record){
                $variable = false;
                $tmp = explode('}', $record);
                $tmpAttribute = '';
                if(count($tmp) > 1){
                    $tmpAttribute = trim(array_shift($tmp));
                }
                if(!empty($tmpAttribute)){
                    if(substr($tmpAttribute,0,1) == '$'){
                        $variable = true;
                        $tmpAttribute = substr($tmpAttribute, 1);
                    }
                    if(empty($variable)){
                        $key = '{' . $tmpAttribute . '}';
                    } else {
                        $key = '{$' . $tmpAttribute . '}';
                    }
                    $oldString = $string;
                    $string = str_replace($key, '[[' . $tmpAttribute . ']]', $string);

                    if($string != $oldString){
                        $tmpAttributeList = $this->attributeList($string);
                    }
                    if(!empty($tmpAttributeList)){
                        foreach($tmpAttributeList as $tmp_nr => $tmp_record){
                            $tmp_key = str_replace('[[' . $tmpAttribute . ']]', '{$' . $tmpAttribute . '}', $tmp_nr);
                            $tmpAttributeList[$tmp_key] = str_replace('[[' . $tmpAttribute . ']]', '{$' . $tmpAttribute . '}', $tmp_record);
                            unset($tmpAttributeList[$tmp_nr]);
                        }
                        foreach($tmpAttributeList as $tmp_nr => $tmp_record){
                            $attributeList[$tmp_nr] = $tmp_record;
                        }
                    }
                    $attributeList[$key] = $tmpAttribute;
                }
            }
        }
        return $attributeList;
    }

    private function modify($value=null, $modifier=null, $argumentList=array()){
        if(is_array($modifier)){
            return $this->modifyList($value, $modifier);
        }
        $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
        $url = $dir . 'modifier.' . $modifier . '.php';
        if(file_exists($url)){
            require_once $url;
        } else {
            return $value;
        }
        $function = 'modifier_' . $modifier;
        if(function_exists($function) === false){
            //trigger error?
            return $value;
        }
        $argumentList= $this->compile($argumentList, $this->data());
        return $function($value, $argumentList);
    }

    private function modifier($value='', $modifier_value='', $return='modify'){
        $argumentList = explode(':"', trim($modifier_value));
        $quote_remove = true;
        $argumentListLength = count($argumentList);
        if($argumentListLength == 1){
            $argumentList = explode(":'", trim($modifier_value));
            $argumentListLength = count($argumentList);
        }
        if($argumentListLength == 1){
            $argumentList = explode(': "', trim($modifier_value));
            $argumentListLength = count($argumentList);
        }
        if($argumentListLength == 1){
            $argumentList = explode(": '", trim($modifier_value));
            $argumentListLength = count($argumentList);
        }
        if($argumentListLength == 1){
            $argumentList = explode(':', trim($modifier_value));
            $argumentListLength = count($argumentList);
            $quote_remove = false;
        }
        if(!empty($quote_remove)){
            $argumentList[$argumentListLength-1] = substr(trim(end($argumentList)),0,-1);
        }
        $modifier = trim(array_shift($argumentList));
        if($return == 'modify'){
            return $this->modify($value, $modifier, $argumentList);
        }
        elseif($return == 'modifier'){
            return $modifier;
        } elseif($return == 'modifier-value') {
            return implode(':', $argumentList);
        } else {
            var_dump($argumentList);
            die;
        }
    }

    private function modifyList($value=null, $modifierList=array()){
        if(empty($modifierList)){
            return $value;
        }
        foreach($modifierList as $modifier_nr => $modifier_value){
            $value = $this->modifier($value, $modifier_value, 'modify');
        }
        return $value;
    }
}