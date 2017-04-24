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
    const MAX_ITERATION = 128;

    private $argument;

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
            $original = $string;
            $list =  $this->attributeList($string);
            $attributeList = array();
            if(empty($list)){
                return $string;
            }
            $data = $this->object($data);
            $compile_list = array();
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
                $compile_list[$search] = $replace;
                if(empty($replace) && !empty($keep)){
                    continue;
                }
                if(is_object($replace)){
                    $replace = $this->object($replace, 'json');
                }
                $string = str_replace($search, $replace, $string);
            }
            $init = 0;
            $counter = 0;
            while($init < 2){
                $test = array();
                $list = $this->controlList($string, 10, $init);
//                 var_dump($list);
                if($list === false){
                    $test[] = false;
                } else {
                    $test[] = true;
                }
                $string = $this->createStatementList($string, $list);
                $init++;
                $list = $this->controlList($string, 20, $init);
//                 var_dump($list);
                if($list === false){
                    $test[] = false;
                } else {
                    $test[] = true;
                }
                $string = $this->createStatementList($string, $list);
                foreach($test as $output){
                    if(!empty($output)){
                        $init = 0;
                    }
                }
                $counter++;
                if($counter > Parser::MAX_ITERATION){
                    //variable in if condition
                    break;
                }
            }
            return $string;
        }
    }

    private function createStatementList($string='', $list=array()){
        if(empty($list)){
            return $string;
        }
        $condition_list = array();
        foreach($list as $key => $value){
            if(!is_array($value)){
                continue;
            }
            $tmp = array();
            foreach ($value as $val_key => $val_value){
                $tmp[$val_key] = $val_value;
                if($val_key == '{/if}'){
                    $statement = '';
                    foreach($tmp as $tmp_key => $tmp_value){
                        $statement .= $tmp_key;
                        foreach($tmp_value as $tmp_value_key => $tmp_value_list){
                            foreach ($tmp_value_list as $tmp_value_list_key => $tmp_value_list_value){
                                $statement .= $tmp_value_list_value;
                            }
                        }
                    }
                    $condition_list[] = $statement;
                }
            }
        }
        if(empty($condition_list)){
            return $string;
            $methodList = $this->createMethodList($string);
//             var_dump($methodList);
            $string = $this->execMethodList($methodList, $string);
//             var_dump($string);
            return $string;
        }
        foreach ($condition_list as $condition_key => $condition){
            $argumentList = $this->createArgumentListIf($condition);
            foreach($argumentList as $nr => $argument){
                $methodList = $this->createMethodList($argument['statement']);
                $argumentList[$nr]['methodList'] = $methodList;
                $argumentList[$nr]['string'] = $condition;
            }
            $function = 'if';
            $function_key = $function;
            $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
            $url = $dir . 'Control.' . ucfirst(strtolower($function)) . '.php';
            $function = 'control_' . $function;

            if(file_exists($url)){
                require_once $url;
            }
            if(function_exists($function) === false){
                var_dump('missing function: ' . $function);
                //trigger error?
                return array();
            }
            $condition =  $function($condition, $argumentList, $this);
            $argumentList = $this->argument();
            foreach($argumentList as $nr => $argument){
                if($argument['condition'] === true){
                    $string = str_replace($argument['string'], $argument['result'] . $argument['extra'], $string);
                } elseif($argument['condition'] == 'ignore') {
                    continue;

                } else{
                    $string = str_replace($argument['string'], $argument['result'] . $argument['extra'], $string);
                }
            }
        }
        return $string;
    }

    public function execMethodList($methodList=array(), $string=''){
        if(empty($string)){
            return $string;
        }
        if(empty($methodList)){
            return $string;
        }
        $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
        foreach ($methodList as $method_nr => $methodCollection){
            foreach ($methodCollection as $method_collection_key => $methodCollectionList){
                foreach ($methodCollectionList as $search => $method){
                    if(empty($method['function'])){
                        continue;
                    }
                    $function = $method['function'];
                    $url = $dir . 'Function.' . ucfirst(strtolower($function)) . '.php';
                    $function = 'function_' . $function;

                    if(file_exists($url)){
                        require_once $url;
                    } else {
                        var_dump('(Parser) missing file: ' . $url);
                        //remove function ?
                        continue;
                    }

                    if(function_exists($function) === false){
                        var_dump('(Parser) missing function: ' . $function);
                        //trigger error?
                        continue;
                    }
                    $argList = array();
                    if(!empty($method['argumentList'])){
                        $argList = $method['argumentList'];
                    }
                    $replace =  $function($search, $argList, $this);
                    if($replace=== false || $replace=== null){
                        $replace= 0;	//not in if statement
                    }
                    $string = str_replace($search, $replace, $string);
                    $before = explode('(', $search, 2);
                    $count = substr_count($before[0], '!');
                    for($i=0; $i < $count; $i++){
                        $string= '!' . $string;
                    }
                }
            }
        }
        return $string;
    }

    private function createFunctionList($string='', $replace='', $key='', $list=array(), $compile_list=array()){
        $temp = explode(' ', $string);
        $method= ltrim(reset($temp), '{');
        $method = explode('(', $method, 2);
        $function = ltrim(reset($method), '!');
        /*
        $function_list = explode('{', $function, 2);
        if(count($function_list) == 2){
            array_shift($function_list);
        }
        $function = reset($function_list);
        */

        $function_key = $function;
        $search  = $key;
        foreach($compile_list as $compile_key => $compile_value){
            $search = str_replace($compile_key, $compile_value, $search);
        }
        $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
        if(in_array($function, array('if'))){
            $url = $dir . 'Control.' . ucfirst(strtolower($function)) . '.php';
            $function = 'control_' . $function;
        } else {
            $url = $dir . 'Function.' . ucfirst(strtolower($function))  . '.php';
            $function = 'function_' . $function;
        }
        if(file_exists($url)){
            require_once $url;
        } else {
            var_dump('output');
            var_dump($string);
            var_dump($function);
            var_dump($compile_list);
            var_dump('(parser) missing file: ' . $url);
            //remove function ?
            return array();
        }

        if(function_exists($function) === false){
            var_dump('missing function: ' . $function);
            //trigger error?
            return array();
        }
        if(strpos($function, 'control') === 0){
            if($key == '{/if}'){
                $argumentList = $this->createArgumentListIf($string);
                foreach($argumentList as $nr => $argument){
                    $methodList = $this->createMethodList($argument['statement']);
                    $argumentList[$nr]['methodList'] = $methodList;
                }
                $string =  $function($string, $argumentList, $this);
                $functon_list[$search][$function_key] = $string;
            }
        } else {
            foreach($compile_list as $compile_key => $compile_value){
                $value = str_replace($compile_key, $compile_value, $value);
            }
            $methodList = $this->createMethodList($value);
            foreach ($methodList as $method_key => $method_value){
                foreach ($method_value as $method_nr => $methodCollection){
                    foreach ($methodCollection as $method_collection_key => $method){
                        if(empty($method['function'])){
                            continue;
                        }
                        $argList = array();
                        if(!empty($method['argumentList'])){
                            $argList = $method['argumentList'];
                        }
                        $function = $method['function'];
                        $url = $dir . 'Function.' . ucfirst(strtolower($function)) . '.php';
                        $function = 'function_' . $function;

                        if(file_exists($url)){
                            require_once $url;
                        } else {
                            var_dump('(Control.If) missing file: ' . $url);
                            //remove function ?
                            continue;
                        }

                        if(function_exists($function) === false){
                            var_dump('missing function: ' . $function);
                            //trigger error?
                            continue;
                        }
                        $res =  $function($method_collection_key, $argList, $this);
                        $function_list[$search][$function_key] = $res;
                    }
                }
            }
        }
        if(isset($function_list[$search][$function_key])){
//             continue;	//done by method
            return $function_list;
        }
        $argumentList = $this->createArgumentList($list);
        $argumentList= $this->compile($argumentList, $this->data());
        $replace =  $function($replace, $argumentList, $this);
        $function_list[$search][$function_key] = $replace;
        return $function_list;
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
        $temp = $this->explode_single(
            array(
                '&&',
                '||',
                ' and ',
                ' or ',
                ' xor ',
                '==',
                '===',
                '<>',
                '!=',
                '!==',
                '<',
                '<=',
                '>',
                '>=',
                '<=>',
                '};'
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
            $func = array_shift($method);
            $function[$function_key]['function'] = ltrim($func, '{!');
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
    }

    private function createArgumentList($list=array()){
        if(!is_array($list)){
            $list = (array) $list;
        }
        $attribute = reset($list);
        if(empty($attribute)){
            return array();
        }
        if(is_array($attribute)){
            return array();
        }
        $attribute = explode('="', $attribute);
        $argumentList = array();
        $index = false;
        foreach($attribute as $key => $value){
            $index = explode(' ', $value, 2);
            $temp = array_shift($index);
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
            if($statement == '{else'){
                continue;
            }
            $else = explode('{else}', end($temp));
            if(count($else) == 1){
                $explode = explode('{/if}', reset($else));
                $true = array_shift($explode);
                $extra = implode('{/if}', $explode);
                $false = '';
            } else {
                $true = reset($else);
                $explode = explode('{/if}', end($else));
                $false = array_shift($explode);
                $extra = implode('{/if}', $explode);
            }
            $argument = array();
            $argument['original'] = $statement;
            $argument['statement'] = $statement;
            $argument['true'] = $true;
            $argument['false'] = $false;
            $argument['extra'] = $extra;

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

    private function controlList($string='', $indent=0, $count=0){
        $function = explode('function(', $string);
//         var_dump($function);
        foreach($function as $function_nr => $content){
            $attributeList = array();
            $list = explode('{', $string);
            if(empty($list)){
                return false;
            }
            foreach($list as $nr => $record){
                $tmp = explode('}', $record);
                $attribute = array_shift($tmp);
                $value = false;
                if(count($tmp) >= 1){
                    $value = implode('}', $tmp);
                }
                if($value === false){
                    continue;
                }
                if(strpos($attribute, '$') === 0){
                    continue;
                }
                $key = $nr;
                if(strpos($attribute, '/') === 0){
                    $attributeList[$key]['{' . $attribute . '}'][$indent-1] = $value;
                    $indent-=2;
                } else {
                    if(substr($attribute,0,2) == 'if'){
                        $attributeList[$key]['{' . $attribute . '}'][$indent+$count] = $value;
                    } else {
                        $attributeList[$key]['{' . $attribute . '}'][$indent] = $value;
                    }
                    $indent+=1;
                }
            }
            $list = array();

            foreach($attributeList as $key => $attList){
                foreach($attList as $attribute => $record){
                    foreach($record as $indent => $value){
                        $list[$indent][$attribute][$key] = $record;
                    }
                }
            }
        }
        if(empty($list)){
            return false;
        } else {
            return $list;
        }
    }

    private function modify($value=null, $modifier=null, $argumentList=array()){
        if(is_array($modifier)){
            return $this->modifyList($value, $modifier);
        }
        $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
        $url = $dir . 'Modifier.' . ucfirst(strtolower($modifier)) . '.php';
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

    public function argument($argument=null){
        if($argument !== null){
            $this->setArgument($argument);
        }
        return $this->getArgument();
    }

    private function setArgument($argument=array()){
        $this->argument = $argument;
    }

    private function getArgument(){
        return $this->argument;
    }
}