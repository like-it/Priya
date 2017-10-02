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
use Priya\Module\Core\Object;

class Parser extends Data {
    const DIR = __DIR__;
    const LITERAL = 'literal';
    const PHP_MIN_VERSION = '7.0.0';
    const FUNCTION_LIST = 'Function.List.php';

    private $random;

    public function __construct($handler=null, $route=null, $data=null){
        $this->random(rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999));
        if($data !== null){
            $this->handler($handler);
            $this->route($route);
            $this->data(Parser::object_merge($this->data(), $data));
        } else {
            $this->data(Parser::object_merge($this->data(), $handler));
        }
    }

    public function run(){
        $this->read(__CLASS__);
        $allowed = $this->data('allow');
        $data = new stdClass();
        foreach($allowed as $key => $allow){
            $data = $this->object_set($key, $this->data($allow), $data, 'root');
        }
        $list = $this->data('entity.decode');
        foreach($list as $target => $attribute){
            $decode = $this->request($attribute);
            if(is_array($decode) || is_object($decode)){
                if(is_object($decode)){
                    foreach($decode as $key => $value){
                        $decode->{$key} = html_entity_decode($value, ENT_HTML5);
                    }
                } else {
                    foreach($decode as $key => $value){
                        $decode[$key] = html_entity_decode($value, ENT_HTML5);
                    }
                }
            } else {
                $decode = html_entity_decode($decode, ENT_HTML5);
            }
            $this->request($attribute, $decode);
        }
        $file = new File();
        $read = $file->read($this->data('dir.priya.public') . 'Template/Priya.tpl');

        $parser = new Parse($this->handler(), $this->route(), $this->data());

        $read = $parser->compile($read);

        debug($read, 'document');
        debug($parser->data());

        die('end parsing');

        var_dump($read);

        var_dump($this->data());
        die;



        //$data = $this->compile($data, $data);
        $data =  $this->compile($this->request(), $data);
        return $this->object($data, 'json');
    }

    public function random($random=null){
        if($random !== null){
            $this->setRandom($random);
        }
        return $this->getRandom();
    }

    private function setRandom($random=''){
        $this->random = $random;
    }

    private function getRandom(){
        return $this->random;
    }

    public function compile($string='', $data, $keep=false){
        $input = $string;
        if(
            is_null($string) ||
            is_bool($string) ||
            is_float($string) ||
            is_int($string) ||
            is_numeric($string)
        ){
            return $string;
        }
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
            if(strpos($string, '{') === false && strpos($string, '}') === false){
                return $string;
            }
            $original = $string;
            $string = str_replace("\r", '[' . $this->random() . '][return]', $string);
            $string = str_replace("\n", '[' . $this->random() . '][newline]', $string);
            $string = $this->literalList($string);
            $list =  $this->attributeList($string);
            $attributeList = array();
            $data = $this->object($data);
            $compile_list = array();
            $key_previous = false;
            foreach($list as $key => $value){
                if(substr($key, 1, 1) != '$'){
                    $key_previous = $key;
                    continue;
                }
                $modifierList = explode('|', $value);
                $attribute = trim(array_shift($modifierList));
                $modify = $this->object_get($attribute, $data);
                if(!empty($key_previous) && strpos($key_previous, '{if') !== false){
                    if(
                        strpos($value, '|') !== false &&
                        strpos($value, 'default') > strpos($value, '|') &&
                        strpos($value, ':') > strpos($value, 'default')
                    ){
                      //if should be fine (has default)
                    } elseif(strpos($key_previous, $value) !== false) {
                        //if should have a default in case of empty variable
//                         $value .= '|default:0';
//                         $list[$key] = $value;
                    }
                    if(
                        strpos($value, '|') !== false &&
                        strpos($value, 'quote') > strpos($value, '|') &&
                        strpos($value, ':') > strpos($value, 'quote')
                    ){
                      //if should be fine (has quote)
                    } elseif(strpos($key_previous, $value) !== false) {
                        if($modify === null){
//                             var_dump($modify);
//                             $value .= 'default:0';
//                             $list[$key] = $value;
                        }
                        if(is_bool($modify) || is_numeric($modify)){

                        } else {
                            //  if should have a string_quote in case of string variable
                            $value .= '|quote:"[quote]":"\\\'"';
                            $list[$key] = $value;
                        }
                    }
                }
                $modifierList = explode('|', $value);
//                 $modifierList = explode('|', trim($key,'{}$ ')); //old
                $attribute = trim(array_shift($modifierList));
                if($keep === 'disable-modify'){
                    $modifierList = array();
                }
//                 $modify = $this->object_get($attribute, $data); // up in the row
                if($modify === null){
                    $modify = $this->modify('', $modifierList);
                } else {
                    $modify = $this->modify($modify, $modifierList);
                }
                if($modify===false){
                    $attributeList[$key] = $modify;
                    $key_previous = $key;
                    continue;
                }
                $list[$key] = $modify;
                $attributeList[$key] = $modify;
                $key_previous = $key;
            }
            //add unset here
            foreach($attributeList as $search => $replace){
                $replace = $this->compile($replace, $data, $keep);
                $compile_list[$search] = $replace;
                if(empty($replace) && ($replace !== 0 || $replace !== '0') && !empty($keep)){
                    continue;
                }
                $replace = $this->handle($replace, 'attribute');
                $string = str_replace($search, $replace, $string);
            }
            $string = $this->keep($string);
            $init = 0;
            $counter = 0;
            $functionList = $list;
            $string = $this->controlIfList($string);
            $list = $this->data($this->random() . '.Parser.Control.If.list');
            if(empty($list)){
                $string = $this->functionList($string, $functionList, $attributeList);
            } else {
                $string = $this->statementIfList($string, $list);
            }
            $string = $this->capture($string);
            $string = $this->handle($string, true);
            return $string;
        }
    }

    private function keep($string=''){
        $explode = explode('[keep[', $string);
        if(count($explode) == 1){
            return $string;
        }
        $temp = explode(']]', $explode[1], 2);
        $key = $temp[0];
        $replace = '{' . $key .'}';
        $search = '[keep[' . $key . ']]';
        $string = str_replace($search, $replace, $string);
        return $string;
    }

    private function capture($string=''){
        $append = $this->data('capture.append');
//         $this->debug($append);
        if(!is_array($append)){
            return $string;
        }
        foreach($append as $capture){
            $string = function_capture_append($string, $capture, $this);
        }
        $this->data('delete', 'capture.append');
        return $string;
    }

    private function handle($string, $return=false){
        if(empty($return) || $return == 'attribute' && $return !== true){
            if($string === null){
                $string = 'null';
            }
            elseif($string === true){
                $string = 'true';
            }
            elseif($string === false){
                $string = 'false';
            }
            elseif(is_null($string)){
                $string = 'null';
            }
            elseif(is_array($string)){
                $string = json_encode($string);
                $string = 'array(' . substr($string, 1, -1) . ')';
            }
            elseif(is_object($string)){
                $string = '{object}' .$this->object($string, 'json-line') . '{/object}';
            }
            //nothing on is_int or is_float or is_numeric
            return $string;

        }
        $string = str_replace('{object}', '', $string);
        $string = str_replace('{/object}', '', $string);
        $string = str_replace('[' . $this->random() . '[', '{', $string);
        $string = str_replace(']' . $this->random() . ']', '}', $string);
        if($string === 'null'){
            $string = null;
        }
        elseif($string === 'true'){
            $string = true;
        }
        elseif($string === 'false'){
            $string = false;
        }
        if($string === null || $string === true || $string === false){
            return $string;
        }
        if(is_float($string) || is_int($string)){
            return $string;
        }
        if(is_numeric($string)){
            $pos = strpos($string ,'0');
            if($pos === 0 && is_numeric(substr($string, 1, 1))){
                return $string;
            }
            return $string + 0;
        }
        if(strpos($string, 'array(') === 0 && substr($string, -1, 1) == ')'){
            $string = '[' . substr($string, 6, -1) . ']';
        }
        if(substr($string, 0, 1) == '[' && substr($string, -1, 1) == ']'){
            $json = json_decode($string);
            if(is_array($json)){
                return $json;
            }
        }
        if(
            substr($string, 0, 1) == '{' &&
            substr($string, -1, 1) == '}' &&
            substr($string, 0, (strlen(Parser::LITERAL)+2)) !== '{' . Parser::LITERAL . '}'
        ){
            $json = json_decode($string);
            if(is_object($json)){
                return $json;
            }
        }
        $string = str_replace('[' . $this->random() . '][return]', "\r", $string);
        $string = str_replace('[' . $this->random() . '][newline]', "\n", $string);

        $string = str_replace('{' . Parser::LITERAL . '}', '' , $string);
        $string = str_replace('{/' . Parser::LITERAL . '}', '' , $string);
        return $string;
    }

    private function controlIfList($string=''){
        $explode = explode('{/if}', $string, 2);
        if(count($explode) > 1){
            $rev = strrev($explode[0]);
            $rev_explode = explode('fi{', $rev, 2);
            $tmp_explode= $rev_explode;
            foreach($tmp_explode as $tmp_nr => $tmp_value){
                $tmp_explode[$tmp_nr] = strrev($tmp_value);
            }
            if(count($tmp_explode) > 1){
                $jid = $this->jid($this->random() . '.Parser.Control.If.list');
                $temp = $tmp_explode[0];
                $temp = explode('}', $temp, 2);
                $statement = $temp[0];

                $list = $this->data($this->random() . '.Parser.Control.If.list');
                if(empty($list)){
                    $list = array();
                }
                $record = new stdClass();
                $record->jid = $jid;
                $record->statement = trim($statement, ' ');
                $else = explode('{else}', $tmp_explode[0], 2);
                if(count($else) > 1){
                    $record->else = '{else}';
                    $record->else_replace = '[' . $this->random() . 'else id:' . $jid .':' . $this->random() . ']';
                }
                $record->end_if = '{/if}';
                $record->if_replace = '[' . $this->random() . 'if id:' . $jid .':' . $this->random() .']';
                $record->end_if_replace = '[' . $this->random() . '/if id:' . $jid .':' . $this->random() . ']';
                $list[$jid] = $record;
                $this->data($this->random() . '.Parser.Control.If.list', $list);
                $search = $statement . '}';
                $tmp_explode[0] = str_replace($search, $record->if_replace, $tmp_explode[0]);
                if(!empty($record->else_replace)){
                    $tmp_explode[0] = str_replace('{else}', $record->else_replace, $tmp_explode[0]);
                }
            }
            if(empty($record)){
                return $string;
            }
            krsort($tmp_explode);
            $explode[0] = implode('', $tmp_explode);
            $string = implode($record->end_if_replace, $explode);
            $string = $this->controlIfList($string);
        } else {
            $list = $this->data($this->random() . '.Parser.Control.If.list');
            if(!empty($list)){
                $sort = array();
                $explode = explode('[' . $this->random() . 'if id:', $string);
                foreach($explode as $nr => $part){
                    $temp = explode(':', $part, 2);
                    if(count($temp) == 1){
                        continue;
                    }
                    $jid = $temp[0];
                    if(isset($list[$jid])){
                        $sort[$jid] = $list[$jid];
                    }
                }
                $list = $this->data($this->random() . '.Parser.Control.If.list', $sort);
                foreach($list as $jid => $node){
                    $part = explode($node->end_if_replace, $string);
                    if(!empty($node->else_replace)){
                        $condition = explode($node->else_replace, $part[0]);
                        if(count($condition) == 1){
                            $true = explode($node->if_replace, $part[0]);
                            $node->true = end($true);
                            $node->false = '';
                        } else {
                            $true = $condition[0];
                            $true = explode($node->if_replace, $true);
                            $node->true = end($true);
                            $node->false = end($condition);
                        }
                    } else {
                        $true = explode($node->if_replace, $part[0]);
                        $node->true = end($true);
                        $node->false = '';
                    }
                    if(empty($node->else_replace)){
                        $node->string =
                        $node->if_replace .
                        $node->true .
                        $node->false .
                        $node->end_if_replace
                        ;
                    } else {
                        $node->string =
                        $node->if_replace .
                        $node->true .
                        $node->else_replace .
                        $node->false .
                        $node->end_if_replace
                        ;
                    }
                    $node->statement = str_replace('"[quote]', '"', $node->statement);
                    $node->statement = str_replace('[quote]"', '"', $node->statement);
                    $node->statement = str_replace('"[quote]"', 'null', $node->statement);
                    $node->statement = str_replace('[quote][quote]', 'null', $node->statement);
                    $node->statement = str_replace('[quote]', '\'', $node->statement);
                }
            }
        }
        return $string;
    }

    private function statementIfList($string='', $list=array(), $attributeList=array()){
        $temp = explode('[' . $this->random() . 'if id:', $string, 2);
        if(count($temp) == 1){
            return $string;
        }
        $function = 'if';
        $function_key = $function;
        $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
        $url = $dir . 'Control.' . ucfirst(strtolower($function)) . '.php';
        $function = 'control_' . $function;

        if(function_exists($function) === false){
            if(file_exists($dir . Parser::FUNCTION_LIST)){
                require_once $url;
            }
            elseif(file_exists($url)){
                require_once $url;
            }
        }
        if(function_exists($function) === false){
            var_dump('(Parser) (statementList) missing control: ' . $function);
            //trigger error?
            return array();
        }
        foreach($list as $jid => $node){
            if(!empty($node->result)){
                continue;
            }
            $node->methodList = $this->createMethodList($node->statement, 'statementlist', $attributeList);
            $string = $function($string, $node, $this);
            return $this->statementIfList($string, $this->data($this->random() . '.Parser.Control.If.list'));
        }
        return $string;
    }

    //registrer method & result
    public function execMethodList($methodList=array(), $string='', $type='statementList'){
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
                    $function = 'function_' . str_replace('.', '_', $function);

                    if(function_exists($function) === false){
                        if(file_exists($dir . Parser::FUNCTION_LIST)){
                            require_once $url;
                        }
                        elseif(file_exists($url)){
                            require_once $url;
                        } else {
                            if($type=='functionList'){
                                $replace = 'null';
                            } else {
                                $replace = '0';
                            }
//                             $string = str_replace($search, $replace, $string);
                            trigger_error('(Parser) (execMethodList) missing file: ' . $url);
                            //remove {function} ?
                            continue;
                        }
                    }
                    if(function_exists($function) === false){
                        var_dump('(Parser) (execMethodList) missing function: ' . $function);
                        //trigger error?
                        continue;
                    }
                    $argList = array();
                    if(!empty($method['argumentList'])){
                        $argList = $method['argumentList'];
                    }
                    foreach($argList as $arg_nr => $argument){
                        if($argument == 'true'){
                            $argList[$arg_nr] = true;
                        }
                        if($argument == 'false'){
                            $argList[$arg_nr] = false;
                        }
                        if($argument == 'null'){
                            $argList[$arg_nr] = null;
                        }
                    }
                    if($function == 'function_capture_append'){
                        $string = function_capture_append($string, $argList, $this);
                        $key = reset($argList);
                        $search = '{$' . $key . '}';
                        $replace = $this->object($this->data($key), 'json');
                        $string = str_replace($search, $replace, $string);

                        /*
                        $capture_append = $this->data('capture.append');
                        if(empty($capture_append)){
                            $capture_append = array();
                        }
                        $capture_append[] = $argList;
                        $this->data('capture.append', $capture_append);
                        */
                        continue;
                    }
                    $replace = $function($search, $argList, $this);
                    if($type == 'functionList'){
                        if($replace === null){
                            $replace = 'null';
                        }
                        elseif($replace === false){
                            $replace = 'false';
                        }
                        elseif($replace === true){
                            $replace = 'true';
                        }
                    } else {
                        if($replace === false || $replace === null){
                            $replace = 0; //in if statement
                        }
                        elseif($replace === true){
                            $replace = 1; //in if statement
                        }
                    }
                    if(is_object($replace) || is_array($replace)){
                        if(
                            is_array($replace) &&
                            $this->is_nested_array($replace)
                        ){
                            foreach($replace as $replace_key => $replace_value){
                                $replace[$replace_key] = $this->array_object($replace_value);
                            }
                        }
                        $replace = $this->object($replace, 'json-line');
                    }
                    $string = str_replace($search, $replace, $string, $is_replace);
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

    public function literal($string=''){
        if (is_array($string)){
            foreach($string as $nr => $line){
                $string[$nr] = $this->literal($line);
            }
            return $string;
        }
        elseif(is_object($string)){
            foreach ($string as $key => $value){
                $string->{$key} = $this->literal($value);
            }
            return $string;
        } else {
            if(strpos($string, '{') !== false || strpos($string, '}') !== false){
                $literal_start = '{' . Parser::LITERAL . '}';
                $literal_end = '{/' . Parser::LITERAL . '}';
                $explode = explode($literal_end, $string);
                $literal_end_count = count($explode);
                $explode = explode($literal_start, $string);
                $literal_start_count = count($explode);

                for($i=0; $i < ($literal_end_count - $literal_start_count); $i++){
                    $string = $literal_start . $string;
                }
                $literal_has = false;
                if(strpos($string, $literal_start) === 0){
                    $literal_end_reverse = strrev($literal_end);
                    $string_reverse = strrev($string);
                    if(strpos($string_reverse, $literal_end_reverse) === 0){
                        $literal_has = true;
                    }
                }
                if(empty($literal_has)){
                    $string = $literal_start . $string . $literal_end;
                }
            }
        }
        return $string;
    }

    private function literalList($string=''){
        if(strpos($string, '{' . Parser::LITERAL . '}') === false){
            return $string;
        }
        $list = explode('{' . Parser::LITERAL . '}', $string, 2);
        if(strpos($list[1], '{' . Parser::LITERAL . '}') !== false){
            $list[1] = $this->literalList($list[1]);
        }
        $literal = explode('{/' . Parser::LITERAL . '}', $list[1], 2);
        $attributeList = $this->attributeList($literal[0]);
        foreach ($attributeList as $search => $attr_value){
            $replace = '[' . $this->random() . '[' . substr($search, 1, -1) . ']' . $this->random() . ']';
            $literal[0] = str_replace($search, $replace, $literal[0]);
        }
        $list[1] = implode('[' . $this->random() . '[/' . Parser::LITERAL . ']' . $this->random() . ']', $literal);
        $string = implode('[' . $this->random() . '[' . Parser::LITERAL . ']' . $this->random() . ']' , $list);
        return $string;
    }

    private function FunctionList($string='', $list=array(), $attributeList=array()){
        $methodList = $this->createMethodList($string, 'functionList', $attributeList);
        $string = $this->execMethodList($methodList, $string, 'functionList');
        return $string;
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

    /**
     *
     * @bugs
     *      - not every method gets extracted
     */
    private function createMethodList($statement='', $type='statementList', $attributeList=array()){
        $parts = $this->explode_single(
            array(
                '{',	//was not here, moved to top for bugfix
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
                '}',		//was '};'
            ),
            $statement
        );
        $methodList = array();
        $temp = explode(')}', $statement);
        foreach ($parts as $nr => $part){
            if(strpos($part, '(') === false){
                continue; //always minimum = 2
            }
            $method = explode('(', $part, 2);
            if(empty($method[0])){
                continue;
            }
            if($type == 'functionList'){
                if(strpos($statement, '{' . $method[0]) === false){
                    continue;
                }
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
            $function_tmp = array_shift($temp);
            if(strpos($function_tmp, '{') === false){
                continue;
            }
            $function_tmp = explode('{', $function_tmp, 2);
            $function_key = $function_tmp[1];
            if($type == 'functionList'){
                $function_key = '{' . $function_key . ')}';
            }
            $function = array();
            $func = array_shift($method);
            if(strpos($func, ' ') !== false){
                continue; //contains statement
            }
            $func = ltrim($func, '{!');

//             $function_key = str_replace('.', '_', $function_key);
//             $func = str_replace('.', '_', ltrim($func, '{!'));
            $function[$function_key]['function'] = $func;
            $tmp = explode($function[$function_key]['function'], $function_key, 2);
            if(count($tmp) > 1){
                $arguments = $tmp[1];
                $arguments = trim($arguments, ' ');
                $arguments = rtrim($arguments, '}');
                $arguments = substr($arguments, 1, -1);
            } else {
                continue;
            }
            $arguments = str_replace('[quote]', '', $arguments);
            $arguments = $this->fixBrackets($arguments);
            $arguments = $this->createObjectList($arguments);
            $args = str_getcsv($arguments); //to handle quotes
            $array = false;
            $object = false;
            $counter = 0;
            $list = array();
            foreach($args  as $key => $value){
                if($value === null && !is_array($array)){
                    $list[] = $value;
                    continue;
                }
                elseif($value === true && !is_array($array)){
                    $list[] = $value;
                    continue;
                }
                elseif($value === false && !is_array($array)){
                    $list[] = $value;
                    continue;
                }
                elseif(($value === NAN || strtoupper($value) == 'NAN') && !is_array($array)){
                    $list[] = NAN;
                    continue;
                }
                $explode = explode('[object id:', $value, 2);
                if(count($explode) == 2){
                    $jid = explode(']', $explode[1], 2);
                    if(count($jid) == 2){
                        $jid = array_shift($jid);
                        $list[] = $this->data($this->random() . '.Parser.Control.Object.list.' . $jid);
                        continue;
                    }
                }
                $value = ltrim($value, ' ');
                if(substr($value,0,1) == '$'){
                    $selector = substr($value, 1);
                    $list[] = $this->data($selector);
                    continue;
                }
                if(substr($value, 0, 1) == '\'' && substr($value, -1, 1) == '\''){
                    $value = substr($value, 1, -1);
                }
                $value = str_replace('\\\'', '\'', $value);
                if(is_numeric($value) && !is_array($array)){
                    $list[] = $value + 0;
                    continue;
                }
                $arg = trim($value);
                $form_simple = false;
                if(strpos($arg, '[') === 0){
                    $form_simple = true;
                    $array = array();
                    $explode = explode('[', $value, 2);
                    $explode = explode(']', $explode[1], 2);
                    $array[$key] = $explode[0];
                    continue;
                }
                if(strpos($arg, 'array(') === 0){
                    $array = array();
                    $val = explode('array(', $value, 2);
                    if(count($val) == 1){
                        $array[$key] = $value;
                        continue;
                    } else {
                        $val = explode(')', $val[1], 2);
                        $val = $val[0];
                        if(in_array(substr($val, 0, 1), array('"', '\'')) && in_array(substr($val, -1, 1), array('"', '\''))){
                            $value = substr($val, 1, -1);
                        } else {
                            $value = $val;
                        }
                        $value = $this->replaceBracketClose($value);
                        $array[$key] = $value;
                        $arg = strrev($arg);
                        if(strpos($arg, ')') === 0){
                            $list[] = $array;
                            $array = false;
                            continue;
                        }
                        continue;
                    }
                }
                $arg = strrev($arg);
                if(strpos($arg, ']') === 0 && !empty($array) && !empty($form_simple)){
                    $explode = explode(']', $value, 2);
                    $array[$key] = $explode[0];
                    $list[] = $array;
                    $array = false;
                    $form_simple = false;
                    continue;
                }
                if(strpos($arg, ')') === 0 && !empty($array)){
                    $val = strrev($value);
                    $val = explode(')', $val, 2);
                    $val = strrev($val[1]);
                    if(in_array(substr($val, 0, 1), array('"', '\'')) && in_array(substr($val, -1, 1), array('"', '\''))){
                        $val = substr($val, 1, -1);
                    }
                    $val = $this->replaceBracketClose($val);
                    $array[$key] = $val;
                    $list[] = $array;
                    $array = false;
                    continue;
                }
                if(!empty($array)){
                    $value = $this->replaceBracketClose($value);
                    $array[$key] = $value;
                } else {
                    $list[] = $value;
                }
            }
            if(!empty($array)){
                $list[] = $array;
                $array = false;
            }
            if(in_array($func, array( //first argument is reference
                'array.pop',
                'array.shift',
                'array.push',
                'array.unshift',
                'array.current',
                'array.next',
                'array.previous',
                'array.reset',
                'array.end',
                'array.key',
                'array.object',
                'is.array.nested'
            ))){
                foreach ($list as $list_nr => $list_value){
                    $list[$list_nr] = key($attributeList);
                    array_shift($attributeList);
                    break;
                }
            }
            if(in_array($func, array( //every argument is reference
                'unset'
            ))){
                foreach ($list as $list_nr => $list_value){
                    $list[$list_nr] = key($attributeList);
                    array_shift($attributeList);
                }
            }
            $function[$function_key]['argumentList'] = $list;
            $methodList[$statement][] = $function;
        }
        return $methodList;
    }

    private function replaceBracketClose($string=''){
        if(strpos($string, '[' . $this->random() .'-bracket-close]') !== false){
            $string= str_replace('[' . $this->random() .'-bracket-close]',')', $string);
        }
        return $string;
    }

    private function fixBrackets($string='', $right=false){
        $string = str_replace('\"', '[' . $this->random() .'-quote]', $string);
        $explode = explode('"', $string, 2);
        if(count($explode) === 2){
            if(empty($right)){
                $string = implode('[' . $this->random() .'-quote-left]', $explode);
            } else {
                $string = implode('[' . $this->random() .'-quote-right]', $explode);
            }
            $right = !$right;
            $string = $this->fixBrackets($string, $right);
        }
        $string = $this->brackets($string);
        $string = str_replace('[' . $this->random() .'-quote]', '\"', $string);
        $string = str_replace('[' . $this->random() .'-quote-left]', '"', $string);
        $string = str_replace('[' . $this->random() .'-quote-right]', '"', $string);
        return $string;
    }

    private function brackets($string=''){
        $explode = explode('[' . $this->random() .'-quote-left]', $string);
        foreach($explode as $nr => $value){
            $bracket = strpos($value, ')');
            $quote_right = strpos($value, '[' . $this->random() .'-quote-right]');
            if($bracket !== false && $bracket < $quote_right){
                $explode[$nr] = substr_replace($value, '[' . $this->random() .'-bracket-close]', $bracket, 1);
                $string = implode('[' . $this->random() .'-quote-left]', $explode);
                $string = $this->brackets($string);
            }
        }
        return $string;
    }

    private function createObjectList($arguments=''){

        $explode = explode('{object}', $arguments, 2);
        if(count($explode) == 2){
            $json= explode('{/object}', $explode[1], 2);
            $json= array_shift($json);
            $object = json_decode($json);

            $jid = $this->jid($this->random() . '.Parser.Control.Object.list');

            $this->data($this->random() . '.Parser.Control.Object.list.' . $jid, $object);
            $arguments = str_replace('{object}', '[object id:'. $jid . ']', $arguments);
            $arguments = str_replace('{/object}', '[/object id:'. $jid . ']', $arguments);
            $arguments = str_replace($json, '', $arguments);
            $arguments = $this->createObjectList($arguments);
        }
        return $arguments;
    }

    private function attributeList($string=''){
        $attributeList = array();
        $function = explode('function(', $string);
        foreach($function as $function_nr => $content){
            if(strpos($content, '[' . $this->random() . '[' . Parser::LITERAL . ']' .  $this->random() . ']') !== false){
                return $attributeList;
            }
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
                if(stristr($tmpAttribute, '[' . $this->random() . '][newline]') !== false){
                    continue;
                }
                if(stristr($tmpAttribute, '[' . $this->random() . '][return]') !== false){
                    continue;
                }
                //also literal ?
                if(empty($tmpAttribute)){
                    continue;
                }
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
        return $attributeList;
    }

    private function controlList($string='', $indent=0, $count=0){
        $function = explode('function(', $string);
        foreach($function as $function_nr => $content){
            if(strpos($content, '[' . $this->random() . '[' . Parser::LITERAL . ']' .  $this->random() . ']') !== false){
                return false;
            }
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
                if(strpos($attribute, Parser::LITERAL) !== false){
                    continue;
                }
                if(
                    strpos($attribute, "\r") !== false ||
                    strpos($attribute, "\n") !== false ||
                    strpos($attribute, "\r\n") !== false
                ){
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
            var_dump('(Parser) modifier (' . $modifier . ') not found');
            return $value;
        }
        $function = 'modifier_' . str_replace('.', '_', $modifier);
//         $function = 'modifier_' . $modifier;
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
//             $argumentList[$argumentListLength-1] = substr(trim(end($argumentList)),0,-1);
            foreach($argumentList as $nr => $argument){
                if(empty($nr)){
                    continue;
                }
                $argumentList[$nr] = substr(trim($argument), 0, -1);
            }

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
            var_dump('^^^^^^^^^^^^^^^^^^^^^^');
            var_dump($return);
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