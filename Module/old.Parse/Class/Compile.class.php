<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\File\Extension;
use Exception;
use DateTime;
use Priya\Module\Parse\Core as Base;

class Compile extends Base {
    const VERSION = '0.0.1';

    const DATA_PREFIX = 'priya.parse.compile.';
    const DATA_INDENT = Compile::DATA_PREFIX . 'indent';
    const DATA_VARIABLE_ASSIGN = Compile::DATA_PREFIX . 'variable.assign';
    const DATA_FOR = Compile::DATA_PREFIX . 'for';
    const DATA_CLASSNAME = Compile::DATA_PREFIX . 'classname';
    const DATA_VERSION = Compile::DATA_PREFIX . 'version';
    const DATA_ID = Compile::DATA_PREFIX . 'id';
    const DATA_METHOD = Compile::DATA_PREFIX . 'method';
    const DATA_MODIFIER = Compile::DATA_PREFIX . 'modifier';

    public static function get_write_url(Parse $parse){
        $dir = $parse->data('priya.parse.dir.cache');
        $source = $parse->data('priya.parse.read.url');
        $basename = File::basename($source);
        $explode = explode('.', $basename, 2);
        $basename = $explode[0];
        $sha1 = sha1($source);
        return $dir . $basename . '_' . $sha1 . Extension::PHP_CACHE;
    }

    public static function document_start(){
        $document = '<?php' . PHP_EOL;
        return $document;
    }

    public static function document_header(Parse $parse){
        $date = new DateTime();
        // echo $date->format('Y-m-d H:i:sP') . "\n";

        $header = [];
        $header[] = '/**';
        $header[] = ' * ' . '@copyright' . Compile::string_tab(1) . '(c) 2015 - ' . $date->format('Y') . ' priya.software';
        $header[] = ' * ' . '@license' . Compile::string_tab(2) . 'http://priya.software/license/';
        $header[] = ' * ' . '@author' . Compile::string_tab(2) . 'Priya Module Parse - ' . $parse->data('priya.version');
        $header[] = ' * ' . '@version' . Compile::string_tab(2) . $date->format('Y-m-d H:i:sP') . ' ' . $parse->data('priya.version');
        $header[] = ' * ' . '@support' . Compile::string_tab(2) . 'http://priya.software/support/';
        $header[] = ' * ' . '@package' . Compile::string_tab(2) . 'Priya\Module\Parse';
        $header[] = ' * ' . '@category' . Compile::string_tab(1) . 'Compile';
        $header[] = ' */';
        $header[] = '';
        $header[] = '';
        return implode(PHP_EOL, $header);
    }

    public static function string_array($array=[]){
        $result[] = '[';
        if(!is_array($array)){
            var_dump($array);
            die;
        }
        foreach($array as $nr => $value){
            $result[] = trim($value) . ',';
        }
        $end = array_pop($result);
        $end = substr($end, 0, -1);
        $result[] = $end;
        $result[] = ']';
        return implode(' ', $result);
    }

    public static function string_tab($amount=1){
        return str_repeat("\t", $amount);
    }


    public static function method_content(Parse $parse, $foreach=[], $token=[]){
        $start = null;
        foreach($token as $nr => $record){
            if(
                $nr > $foreach['token']['nr'] &&
                $nr < $foreach['token']['tag_close_nr']
            ){
                if($start === null){
                    $start = $nr;
                }
                $content[$nr] = $record;
            }
        }
        $end = end($content);
        if($content[$start]['type'] == Token::TYPE_WHITESPACE){
            $explode = explode("\n", $content[$start]['value'], 2);
            if(isset($explode[1])){
                $content[$start]['value'] = $explode[1];
            }
        }
        /*
        if($end['type'] == Token::TYPE_WHITESPACE){
            $explode = explode("\n", $end['value'], 2);
            if(isset($explode[1])){
                $content[$end['token']['nr']]['value'] = $explode[1];
            }
        }
        */
        return $content;
    }

    public static function foreach_parameter_extract(Parse $parse, $record=[]){
        $result = [];
        $key = [];
        $value = [];
        if(
            isset($record['type']) &&
            $record['type'] == Token::TYPE_METHOD &&
            $record['method']['name'] == 'for.each' &&
            array_key_exists(0, $record['method']['parameter'])
        ){
            $parameter = $record['method']['parameter'][0];
            $has_as = false;
            $has_array_operator = false;

            foreach($parameter as $nr => $item){
                if($item['type'] == Token::TYPE_WHITESPACE){
                    continue;
                }
                elseif(
                    $item['type'] == Token::TYPE_STRING &&
                    $item['value'] == 'as'
                ){
                    $has_as = true;
                }
                elseif($item['type'] == Token::TYPE_IS_ARRAY_OPERATOR){
                    $has_array_operator = true;

                }
                elseif(
                    $has_as === true ||
                    $has_array_operator === true
                ){
                    if($item['type'] != Token::TYPE_VARIABLE){
                        $source =  $parse->data('priya.parse.read.url');
                        if($source === null){
                            throw new Exception('Parse error: for.each variable key expected on line:' . $record['row']  . ' column: ' . $record['column']);
                        } else {
                            throw new Exception('Parse error: for.each variable key expected on line:' . $record['row']  . ' column: ' . $record['column'] . ' in: ' . $source);
                        }
                    }
                    if(empty($value)){
                        $value['name'] = substr($item['variable']['name'], 1);
                        $value['variable'] = str_replace('.', '_', $item['variable']['name']);
                        $has_as = false;
                        $has_array_operator = false;
                    } else {
                        $key = $value;
                        $value['name'] = substr($item['variable']['name'], 1);
                        $value['variable'] = str_replace('.', '_', $item['variable']['name']);
                        $has_as = false;
                        $has_array_operator = false;
                    }
                }
            }
        }
        if(!empty($key)){
            $result['key'] = $key;
        }
        if(!empty($value)){
            $result['value'] = $value;
        }
        return $result;
    }
    public static function constant_meta(Parse $parse){
        $constant = [];
        $indent = $parse->data(Compile::DATA_INDENT);
        $constant[] = Compile::string_tab($indent) . 'const META = [';
        $constant[] = Compile::string_tab($indent + 1) . '"expire" => 1,';
        $constant[] = Compile::string_tab($indent) . '];';
        $constant[] = PHP_EOL;
        return implode(PHP_EOL, $constant);
    }

    public static function private_name(Parse $parse, $name=''){
        $indent = $parse->data(Compile::DATA_INDENT);
        return Compile::string_tab($indent) . 'private $' . $name . ';';
    }

    public static function protected_name(Parse $parse, $name=''){
        $indent = $parse->data(Compile::DATA_INDENT);
        return Compile::string_tab($indent) . 'protected $' . $name . ';';
    }

    public static function public_name(Parse $parse, $name=''){
        $indent = $parse->data(Compile::DATA_INDENT);
        return Compile::string_tab($indent) . 'public $' . $name . ';';
    }

    public static function use_name($name='', $as=null){
        if($as !== null){
            return 'use ' . $name . ' as ' . $as . ';';
        }
        return 'use ' . $name . ';';
    }

    public static function use_function_name($name='', $as=null){
        if($as !== null){
            return 'use function ' . $name . ' as ' . $as . ';';
        }
        return 'use function ' . $name . ';';
    }


    public static function use_function(Parse $parse){
        $method = $parse->data(Compile::DATA_METHOD);
        $use_function = [];
        foreach($method as $name => $true){
            $use_function[] = Compile::use_function_name('Priya\Module\Parse\\' . $name );
        }
        return implode(PHP_EOL, $use_function);
    }

    public static function use(Parse $parse){
        $use = [];
        $use[] = Compile::use_name('Exception');
        $use[] = Compile::use_name('Priya\Module\Parse');
        $use[] = Compile::use_name('Priya\Module\Parse\Token');
        // $public[] = '';
        return implode(PHP_EOL, $use);
    }

    public static function namespace_name(Parse $parse, $name=''){
        $parse->data('priya.parse.compile.namespace', $name);
        return 'namespace ' . $name . ';';
    }

    public static function namespace(Parse $parse){
        $namespace = [];
        $namespace[] = Compile::namespace_name($parse, 'Priya\Module\Parse');
        return implode(PHP_EOL, $namespace);
    }

    public static function method_construct(Parse $parse, $token=[]){
        $method = [];
        $method[] = Compile::string_tab(1) . 'public function __construct(Parse $parse, $token=[]){';
        $method[] = Compile::string_tab(2) . '$this->parse($parse);';
        $method[] = Compile::string_tab(2) . '$this->token($token);';
        $method[] = Compile::string_tab(1) . '}';
        return implode(PHP_EOL, $method);
    }

    public static function method_parse(){
        $method = [];
        $method[] = Compile::string_tab(1) . 'public function parse(Parse $parse=null){';
        $method[] = Compile::string_tab(2) . 'if($parse !== null){';
        $method[] = Compile::string_tab(3) . '$this->parse = $parse;';
        $method[] = Compile::string_tab(2) . '}';
        $method[] = Compile::string_tab(2) . 'return $this->parse;';
        $method[] = Compile::string_tab(1) . '}';
        return implode(PHP_EOL, $method);
    }

    public static function method_value(Parse $parse, $token=[], $method=[]){
        if(!isset($method['method'])){
            var_dump($method);
            die;
        }
        $name = $method['method']['name'];
        $filename = ucfirst(Token::TYPE_FUNCTION) . '.' . ucfirst($name);
        $name = str_replace('.', '_', strtolower($filename));
        $convert_method = $parse->data(Compile::DATA_METHOD);
        if($convert_method === null){
            $convert_method = [];
        }
        $convert_method[$filename] = true;
        $parse->data(Compile::DATA_METHOD, $convert_method);
        $string = $name . '($parse, [ ';
        foreach($method['method']['parameter'] as $nr => $set){
            foreach($set as $token_nr => $record){
                if(!empty($record['is_executed'])){
                    if(
                        in_array(
                            $record['type'],
                            [
                                Token::TYPE_BOOLEAN,
                                Token::TYPE_STRING,
                                Token::TYPE_NULL,
                            ]
                        )
                    ){
                        $string .= $record['value'];
                    } else {
                        $string .= $record['execute'];
                    }
                }
                elseif($record['type'] == Token::TYPE_VARIABLE){
                    if($record['variable']['is_assign'] === false){
                        $attribute = substr($record['variable']['name'], 1);
                        $string .= '$parse->data(\'' . $attribute . '\')';
                    } else {
                        throw new Exception('Not implemented yet...');
                        var_dump($record);
                        die;
                    }
                }
                elseif($record['is_operator'] === true){
                    $string .= $record['value'];
                }
                elseif($record['type'] == Token::TYPE_WHITESPACE){
                    $string .= $record['value'];
                }
                else {
                    var_dump($record);
                    die;
                }
            }
            $string .= ',';
        }
        $string = substr($string, 0, -1) . ' ])';
        return $string;
    }

    public static function modifier_parameter($modifier=[]){
        $parameter = [];
        foreach($modifier['parameter'] as $nr => $token){
            if(!isset($parameter[$nr])){
                $parameter[$nr] = '';
            }
            foreach($token as $token_nr => $record){
                if(!empty($record['is_executed'])){
                    if($record['type'] == Token::TYPE_STRING){
                        $parameter[$nr] .= $record['value'];
                    } else {
                        $parameter[$nr] .= $record['execute'];
                    }
                }
                elseif($record['type'] == Token::TYPE_WHITESPACE){
                    $parameter[$nr] .= ' ';
                }
                else {
                    var_dump($record);
                    die;
                }
            }
            $parameter[$nr] = rtrim($parameter[$nr]);
        }
        return $parameter;
    }

    public static function variable_assign_key(Parse $parse, $attribute, &$data=[], &$is_assign){
        $indent = $parse->data(Compile::DATA_INDENT);
        $variable_assign = $parse->data(Compile::DATA_VARIABLE_ASSIGN);
        if($variable_assign === null){
            $variable_assign = [];
        }
        $attributeList = Parse::explode_multi(Parse::ATTRIBUTE_EXPLODE, $attribute);
        $key = '$data->';
        $end = array_pop($attributeList);
        $is_assign = false;
        if(!in_array($attribute, $variable_assign)){
            $variable_assign[] = $attribute;
            $parse->data(Compile::DATA_VARIABLE_ASSIGN, $variable_assign);
            foreach($attributeList as $attribute_nr => $attribute_part){
                $key .= $attribute_part;
                $data[] = Compile::string_tab($indent) . 'if(!isset(' . $key .') ||!is_object(' . $key . ')){';
                $data[] = Compile::string_tab($indent + 1) . $key . ' = new stdClass();';
                $data[] = Compile::string_tab($indent) . '}';
                $key .= '->';
            }
            $key .= $end;
            $data[] = Compile::string_tab($indent) . 'if(!isset(' . $key . ')){';
            $data[] = Compile::string_tab($indent + 1) . $key . ' = null;';
            $data[] = Compile::string_tab($indent) . '}';
            $is_assign = true;
        } else {
            foreach($attributeList as $attribute_nr => $attribute_part){
                $key .= $attribute_part;
                $key .= '->';
            }
            $key .= $end;
        }
        return $key;
    }

    /*
    public static function variable_value(Parse $parse, $token=[], $record=[], $attribute=null, &$data){
        $value = '';
        if(empty($record['variable'])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            var_dump($record);
            die;
        }
        if(empty($record['variable']['value'])){
            if(
                isset($record['variable']['operator']) &&
                in_array(
                    $record['variable']['operator'],
                    [
                        '++',
                        '--',
                        '**',
                        '%%'
                    ]
                )
            ){
                if($attribute !== null){
                    switch($record['variable']['operator']){
                        case '++' :
                            $key = Compile::variable_assign_key($parse, $attribute, $data, $is_assign);
                            if($is_assign === true){
                                var_dump('undefined, is_assign in variable_assign_key');
                                die;
                            } else {
                                $value = $key . ' + 1';// . '++';
                            }
                        break;
                        case '--' :
                            $value = '$parse->data(\'' . $attribute . '\') - 1';
                        break;
                        /*
                        case '**' :
                            $value = '$parse->data(\'' . $attribute . '\') * 2';
                        break;
                        case '%%' :
                            $value = '$parse->data(\'' . $attribute . '\') %';
                        break;
                        */
                        /*
                    }
                }
            } else {
                if($attribute !== null){
                    $value  = '$parse->data(\'' . $attribute . '\')';
                }
            }
        } else {
            $is_plus = false;
            $plus = '';
            foreach($record['variable']['value'] as $value_nr => $value_value){
                //variable assign / no assign to...
                if(!empty($value_value['is_executed'])){
                    if($value_value['type'] == Token::TYPE_STRING){
                        if($is_plus === false){
                            $value .= $value_value['value'];
                        } else {
                            $plus .= $value_value['value'];
                            $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';
                            $is_plus = false;
                            $plus = '';
                        }
                    } else {
                        if($is_plus === false){
                            $value .= $value_value['execute'];
                        } else {
                            $plus .= $value_value['execute'];
                            var_dump($plus);
                            var_dump($value);
                            die;
                        }
                    }
                }
                elseif(
                    in_array(
                        $value_value['type'],
                        [
                            Token::TYPE_METHOD,
                        ]
                    )
                ){
                    if($is_plus === false){
                        $value = Compile::method_value($parse, $token, $value_value);
                    } else {
                        $plus = Compile::method_value($parse, $token, $value_value);
                    }
                }
                elseif($value_value['is_operator'] === true){
                    if($is_plus === true){
                        $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';
                        $is_plus = false;
                        $plus = '';
                    }
                    if($value_value['value'] == '+'){
                        $is_plus = true;
                        $plus = '';
                    } else {
                        $value .= $value_value['value'];
                    }
                }
                elseif($value_value['type'] == Token::TYPE_WHITESPACE){
                    if($is_plus === false){
                        $value .= $value_value['value'];
                    } else {
                        $plus .= $value_value['value'];
                    }

                }
                elseif($value_value['type'] == Token::TYPE_STRING){
                    if($is_plus === false){
                        $value .= '"' . $value_value['value'] . '"';
                    } else {
                        $plus .= '"' . $value_value['value'] . '"';
                    }
                }
                elseif(
                    $value_value['type'] == Token::TYPE_VARIABLE &&
                    empty($value_value['variable']['is_assign']) &&
                    empty($value_value['variable']['has_modifier'])
                ){
                    if($is_plus === false){
                        $value .= PHPC::variable_value($parse, [ $value_value['value'] ], $token, $value_value);
                        // $value .= $value_value['value'];
                    } else {
                        $plus .= PHPC::variable_value($parse, [ $value_value['value'] ], $token, $value_value);
                        // $plus .= $value_value['value'];
                    }
                }
                else {
                    var_dump($record);
                    var_Dump($value_value);
                    die;
                }
            }
        }
        if($value == 'null'){
            $value = null;
        }
        elseif($value == 'true'){
            $value = true;
        }
        elseif($value == 'false'){
            $value = false;
        }
        elseif(is_numeric($value)){
            $value += 0;
        } else {
            // $value = '\''. $value . '\'';
        }
        return $value;
    }
    */

    public static function method_parameter(Parse $parse, $method=[]){
        foreach($method['method']['parameter'] as $parameter_nr => $set){
            $is_plus = false;
            $plus = '';
            $value = '';
            foreach($set as $token_nr => $record){
                if(empty($record['type'])){
                    $debug = debug_backtrace(true);
                    // var_dump($debug);
                    var_dump($method);
                    die;
                }
                if(!empty($record['is_executed'])){
                    if($record['type'] == Token::TYPE_STRING){
                        if($is_plus === false){
                            $value .= $record['value'];
                        } else {
                            $plus .= $record['value'];
                            $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';
                            $is_plus = false;
                            $plus = '';
                        }
                    } else {
                        if($is_plus === false){
                            $value .= $record['execute'];
                        } else {
                            $plus .= $record['execute'];
                            $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';
                            $is_plus = false;
                            $plus = '';
                        }
                    }
                }
                elseif(
                    $record['type'] == Token::TYPE_VARIABLE &&
                    $record['variable']['is_assign'] === true
                ){
                    var_Dump($record);
                    return new Exception('Not implemented yet...');
                }
                elseif(
                    $record['type'] == Token::TYPE_VARIABLE &&
                    $record['variable']['is_assign'] === false
                ){
                    if(empty($record['variable']['has_modifier'])){
                        $attribute = substr($record['variable']['name'], 1);
                        if($is_plus === false){
                            $variable_value = PHPC::variable_value($parse, $set, $record, $attribute, $data);
                            if(is_string($variable_value)){
                                $explode = explode('::', $variable_value, 2);
                                if(isset($explode[1])){
                                    $left = substr($variable_value, 0, strlen($explode[0]));
                                    if($left == $explode[0]){
                                        $value .= '$parse->data(\'' . $attribute . '\', ' . $variable_value . ')';
                                    } else {
                                        $value .= '$parse->data(\'' . $attribute . '\', \'' . $variable_value .'\')';
                                    }
                                } else {
                                    $value .= '$parse->data(\'' . $attribute . '\', \'' . $variable_value .'\')';
                                }
                            } else {
                                $value .= '$parse->data(\'' . $attribute . '\', ' . $variable_value .')';
                            }
                            $value = '$parse->data(\''. $attribute .'\')';
                        } else {
                            $plus =  '$parse->data('. $attribute .')';
                            var_Dump('plus');
                            die;
                        }
                    } else {
                        var_Dump($record);
                        return new Exception('Not implemented yet...');
                    }

                }
                elseif(
                    in_array(
                        $record['type'],
                        [
                            Token::TYPE_METHOD,
                        ]
                    )
                ){
                    if($is_plus === false){
                        //might need to change $set = $token
                        $value = Compile::method_value($parse, $set, $record);
                    } else {
                        //might need to change $set = $token
                        $plus = Compile::method_value($parse, $set, $record);
                    }
                }
                elseif($record['is_operator'] === true){
                    if($is_plus === true){
                        var_dump('fuck it');
                        die;
                        $value = 'Parse::plus(' . rtrim($value) . ',' . $plus . ')';
                        $is_plus = false;
                        $plus = '';
                    }
                    if($record['value'] == '+'){
                        $is_plus = true;
                        $plus = '';
                    } else {
                        $value .= $record['value'];
                    }
                }
                elseif($record['type'] == Token::TYPE_WHITESPACE){
                    if($is_plus === false){
                        $value .= $record['value'];
                    } else {
                        $plus .= $record['value'];
                    }

                }
                elseif($record['type'] == Token::TYPE_STRING){
                    if($is_plus === false){
                        $value .= '"' . $record['value'] . '"';
                    } else {
                        $plus .= '"' . $record['value'] . '"';
                    }
                }
                else {
                    var_Dump($record);
                    die;
                }
            }
            $parameter[$parameter_nr] = $value;
        }
        return implode(', ', $parameter);
    }

    /*
    public static function method_execute(Parse $parse, $token=[]){
        $result = [];
        $method = [];
        $result[] = Compile::string_tab(1) . 'public static function execute(Parse $parse){';
        $collect = '';
        $is_assign = false;
        foreach($token as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $record['variable']['is_assign'] === true
            ){
                $is_assign = true;
                if($collect !== ''){
                    $method[] = Compile::string_tab(2) . '$data[] = \'' . $collect . '\';';
                    $collect = '';
                }
                $attribute = substr($record['variable']['name'], 1);
                $value = Compile::variable_value($parse, $token, $record, $attribute);
                $method[] = Compile::string_tab(2) . '$parse->data(\'' . $attribute . '\', ' . $value . ');';
            }
            elseif(
                $record['type'] == Token::TYPE_VARIABLE &&
                $record['variable']['is_assign'] === false
            ){
                if($collect !== ''){
                    $method[] = Compile::string_tab(2) . '$data[] = \'' . $collect . '\';';
                    $collect = '';
                }
                if(!empty($record['variable']['has_modifier'])){
                    $attribute = substr($record['variable']['name'], 1);
                    $method[] = Compile::string_tab(2) . '$modifier = $parse->data(\'' . $attribute . '\');';
                    $convert_modifier = $parse->data(Compile::DATA_MODIFIER);
                    if($convert_modifier === null){
                        $convert_modifier = [];
                    }
                    foreach($record['variable']['modifier']['list'] as $modifier_nr => $modifier){
                        $filename = ucfirst(Token::TYPE_MODIFIER) . '.' . ucfirst($modifier['name']);
                        $function_name = strtolower(str_replace('.', '_', $filename));
                        $convert_modifier[$filename] = true;
                        $parameter = Compile::modifier_parameter($modifier);
                        $parameter = implode(',', $parameter);
                        $method[] = Compile::string_tab(2) . '$modifier = ' . $function_name . '($parse, $modifier,' . $parameter . ');';
                    }
                    $method[] = Compile::string_tab(2) . '$data[] = $modifier;';
                    $parse->data(Compile::DATA_MODIFIER, $convert_modifier);
                } else {
                    $attribute = substr($record['variable']['name'], 1);
                    $method[] = Compile::string_tab(2) . '$data[] = $parse->data(\'' . $attribute . '\');';
                }
            }
            elseif(
                $record['type'] == Token::TYPE_METHOD
            ){
                $filename = ucfirst(Token::TYPE_FUNCTION) . '.' . ucfirst($record['method']['name']);
                $function_name = strtolower(str_replace('.', '_', $filename));
                $convert_method = $parse->data(Compile::DATA_METHOD);
                if($convert_method === null){
                    $convert_method = [];
                }
                $convert_method[$filename] = true;
                $parse->data(Compile::DATA_METHOD, $convert_method);
                try {
                    //required for tag_close
                    Parse::require($parse, $filename, Parse::TYPE_FUNCTION);
                } catch(Exception $e){
                    return $e;
                }
                $tag_close = strtoupper(str_replace('.', '_', $record['method']['name']) . '_TAG_CLOSE');
                // var_dump($tag_close);
                $has_tag_close = false;
                if(defined('Priya\Module\Parse\\' . $tag_close)){
                    $has_tag_close = constant('Priya\Module\Parse\\' . $tag_close);
                }
                if(
                    $has_tag_close === true &&
                    !isset($record['token']['tag_close_nr'])
                ){
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        return new Exception('Function: ' .  $record['method']['name'] . ' needs a close tag on line: ' . $record['row'] . ' column: ' . $record['column']);
                    } else {
                        return new Exception('Function: ' .  $record['method']['name'] . ' needs a close tag on line: ' . $record['row'] . ' column: ' . $record['column'] . ' in: ' . $source);
                    }
                }
                $parameter_depth = strtoupper(str_replace('.', '_', $record['method']['name']) . '_PARAMETER_DEPTH');
                if(defined('Priya\Module\Parse\\' . $parameter_depth)){
                    $parameter_depth = constant('Priya\Module\Parse\\' . $parameter_depth);
                } else {
                    $parameter_depth = 1;
                }
                if($has_tag_close === true){
                    $parameter = [];
                    if($parameter_depth == 1){
                        if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                            $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                            $parameter = $parameter_method($parse, $record);
                            var_dump($parameter);
                        } else {
                            if(empty($record['method']['parameter'])){
                                $parameter = null;
                            } else {
                                var_dump($parameter_depth);
                                var_dump($function_name);
                                $parameter = Compile::method_parameter($parse, $record);
                            }
                        }
                    } else {
                        if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                            $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                            $parameter = $parameter_method($parse, $record);
                            var_dump($parameter);
                            die;
                        } else {
                            return new Exception('Not implemented yet...');
                        }
                    }
                    if($parameter === null){
                        $method[] = Compile::string_tab(2) . $function_name . '($parse){';
                    } else {
                        var_dump($parameter);
                        die;
                        if($function_name == 'function_for_each'){
                            $function_name = 'foreach';
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        }
                        elseif($function_name == 'function_for'){
                            $function_name = 'for';
                            var_dump($parameter);
                            die;
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        }
                        elseif($function_name == 'function_if'){
                            $function_name = 'if';
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        }
                        elseif($function_name == 'function_while'){
                            $function_name = 'while';
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        }
                        elseif($function_name == 'function_switch'){
                            $function_name = 'switch';
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        } else {
                            $method[] = Compile::string_tab(2) . $function_name . '(' . $parameter . '){';
                            $method[] = Compile::string_tab(2) . '}';
                        }
                    }
                } else{
                    $parameter = [];
                    if($parameter_depth == 1){
                        if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                            $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                            $parameter = $parameter_method($parse, $record);
                        } else {
                            if(empty($record['method']['parameter'])){
                                $parameter = null;
                            } else {
                                var_dump($function_name);
                                $parameter = Compile::method_parameter($parse, $record);
                            }
                        }
                    } else {
                        if(function_exists('Priya\Module\Parse\\' . $function_name . '_parameter')){
                            $parameter_method = 'Priya\Module\Parse\\' . $function_name . '_parameter';
                            $parameter = $parameter_method($parse, $record);
                        } else {
                            return new Exception('Not implemented yet...');
                        }
                    }
                    if($parameter === null){
                        $method[] = Compile::string_tab(2) . $function_name . '($parse);';
                    } else {
                        $method[] = Compile::string_tab(2) . $function_name . '($parse, [' . $parameter . ']);';
                    }
                }
            }
            elseif(!empty($record['is_executed'])){
                if($record['type'] == Token::TYPE_STRING){
                    $collect .= $record['value'];
                    // $method[] = Compile::string_tab(2) . '$data[] = ' . $record['value'] . ';';
                } else {
                    $collect .= $record['execute'];
                    // $method[] = Compile::string_tab(2) . '$data[] = ' . $record['execute'] . ';';
                }
            }
            elseif($record['type'] == Token::TYPE_STRING){
                $collect .= $record['value'];
                // $method[] = Compile::string_tab(2) . '$data[] = \'' . $record['value'] . '\';';
            }
            elseif($record['type'] == Token::TYPE_WHITESPACE){
                if($is_assign === true){
                    $explode = explode("\n" , $record['value'], 2);
                    if(isset($explode[1])){
                        $record['value'] = $explode[1];
                    }
                    $is_assign = false;
                }
                $collect .= $record['value'];
                // $method[] = Compile::string_tab(2) . '$data[] = \'' . $record['value'] . '\';';
            }
        }
        if($collect !== ''){
            $method[] = Compile::string_tab(2) . '$data[] = \'' . $collect . '\';';
            $collect = '';
        }
        $result[] = Compile::require_method($parse, $parse->data(Compile::DATA_METHOD), Compile::string_tab(2), 'Parse');
        $result[] = Compile::require_modifier($parse, $parse->data(Compile::DATA_MODIFIER), Compile::string_tab(2), 'Parse');
        $result[] = Compile::string_tab(2) . '$data = [];';
        foreach($method as $nr => $record){
            $result[] = $record;
        }
        $result[] = Compile::string_tab(2) . 'return implode(\'\', $data);';
        $result[] = Compile::string_tab(1) . '}';
        var_dump($result);
        die;
        return implode(PHP_EOL, $result);
    }
    */

    public static function method_token(){
        $method = [];
        $method[] = Compile::string_tab(1) . 'public function token($token=null){';
        $method[] = Compile::string_tab(2) . 'if($token !== null){';
        $method[] = Compile::string_tab(3) . '$this->token = $token;';
        $method[] = Compile::string_tab(2) . '}';
        $method[] = Compile::string_tab(2) . 'return $this->token;';
        $method[] = Compile::string_tab(1) . '}';
        return implode(PHP_EOL, $method);
    }

    public static function method(Parse $parse, $token=[]){
        $method = [];
        // $method[] = Compile::method_construct($parse, $token);
        // $method[] = '';
        // $method[] = Compile::method_parse();
        // $method[] = '';
        // $method[] = Compile::method_token();
        // $method[] = '';
        $method[] = Compile::method_execute($parse, $token);
        return implode(PHP_EOL, $method);
    }

    public static function require(Parse $parse){
        $require = $parse->data(Compile::DATA_METHOD);
        $result = [];
        foreach($require as $name => $true){
            $result[] = 'require_once ' . $name . ';';
        }
        return implode(PHP_EOL, $result);
    }

    public static function require_modifier(Parse $parse, $modifier=[], $before='', $classname=''){
        if(empty($classname)){
            $classname = $parse->data(Compile::DATA_CLASSNAME);
        }
        if(!is_array($modifier)){
            return '';
        }
        $result = [];
        foreach($modifier as $name => $true){
            if(is_string($name)){
                $result[] = $before . 'try {';
                $result[] = $before . Compile::string_tab(1) . $classname . '::require(';
                $result[] = $before . Compile::string_tab(2) . '$parse,';
                $result[] = $before . Compile::string_tab(2) . '\'' . $name . '\',';
                $result[] = $before . Compile::string_tab(2) . $classname . '::TYPE_MODIFIER';
                $result[] = $before . Compile::string_tab(1) . ');';
                $result[] = $before . '} catch (Exception $e) {';
                    $result[] = $before . Compile::string_tab(1) . ' echo $e;';
                    $result[] = $before . Compile::string_tab(1) . ' die;';
                $result[] = $before . '}';
            }
            elseif(is_string($true)) {
                $result[] = $before . 'try {';
                $result[] = $before . Compile::string_tab(1) . $classname . '::require(';
                $result[] = $before . Compile::string_tab(2) . '$parse,';
                $result[] = $before . Compile::string_tab(2) . '\'' . $true . '\',';
                $result[] = $before . Compile::string_tab(2) . $classname . '::TYPE_MODIFIER';
                $result[] = $before . Compile::string_tab(1) . ');';
                $result[] = $before . '} catch (Exception $e) {';
                    $result[] = $before . Compile::string_tab(1) . ' echo $e;';
                    $result[] = $before . Compile::string_tab(1) . ' die;';
                $result[] = $before . '}';
            }
        }
        return implode(PHP_EOL, $result);
    }

    public static function require_method(Parse $parse, $method=[], $before='', $classname=''){
        if(empty($classname)){
            $classname = $parse->data(Compile::DATA_CLASSNAME);
        }
        if(!is_array($method)){
            return '';
        }
        $result = [];
        $result[] = $before . 'try {';
        foreach($method as $name => $true){
            if(is_string($name)){
                $result[] = $before . Compile::string_tab(1) . $classname . '::require(';
                $result[] = $before . Compile::string_tab(2) . '$parse,';
                $result[] = $before . Compile::string_tab(2) . '\'' . $name . '\',';
                $result[] = $before . Compile::string_tab(2) . $classname . '::TYPE_FUNCTION';
                $result[] = $before . Compile::string_tab(1) . ');';
            }
            elseif(is_string($true)) {
                $result[] = $before . Compile::string_tab(1) . $classname . '::require(';
                $result[] = $before . Compile::string_tab(2) . '$parse,';
                $result[] = $before . Compile::string_tab(2) . '\'' . $true . '\',';
                $result[] = $before . Compile::string_tab(2) . $classname . '::TYPE_FUNCTION';
                $result[] = $before . Compile::string_tab(1) . ');';
            }
        }
        $result[] = $before . '} catch (Exception $e) {';
            $result[] = $before . Compile::string_tab(1) . ' echo $e;';
            $result[] = $before . Compile::string_tab(1) . ' die;';
            $result[] = $before . '}';
        return implode(PHP_EOL, $result);
    }

    public static function document_class(Parse $parse, $token=[]){
        $class = [];
        $use = Compile::use($parse);
        if(!empty($use)){
            $class[] = $use;
            $class[] = '';
        }
        $classname = 'Compileer_1';
        $parse->data(Compile::DATA_CLASSNAME, $classname);
        $indent = $parse->data(Compile::DATA_INDENT);
        $class[] = Compile::string_tab($indent) . 'class ' . $classname .' {';
        $const = Compile::const($parse);
        if(!empty($const)){
            $class[] = $const;
            $class[] = '';
        }
        $private = Compile::private($parse);
        if(!empty($private)){
            $class[] = $private;
            $class[] = '';
        }
        $protected = Compile::protected($parse);
        if(!empty($protected)){
            $class[] = $protected;
            $class[] = '';
        }
        $public = Compile::public($parse);
        if(!empty($public)){
            $class[] = $public;
            $class[] = '';
        }
        $method = Compile::method($parse, $token);
        if(!empty($method)){
            $class[] = $method;
            $class[] = '';
        }
        $class[] = Compile::string_tab($indent) . '}';
        return implode(PHP_EOL, $class);
    }

    public static function execute(Parse $parse, $data=[], &$class=null){
        $parse->data(Compile::DATA_VERSION, Compile::VERSION);
        $dir = $parse->data(Parse::DATA_DIR_CACHE);
        if(
            $dir !== null &&
            (
                File::exist($dir) === false ||
                Dir::is($dir) === false
            )
        ){
            $create = Dir::create($dir, Dir::CHMOD);
            if($create === false){
                throw new Exception('Compile error: Could not create Cache directory (' . $dir .')...');
            }
        }
        $result = new Conversion($parse->handler(), $parse->route(), $parse->data());
        $document = null;
        $location = [];
        try {
            $location = $result->view('location', 'PHPC/Php.class');
            foreach($location as $nr => $url){
                if(
                    $document === null &&
                    file_exists($url)
                ){
                    $document = File::read($url);
                    break;
                }
            }
        } catch (Exception $e){
            echo $e;
            die;
        }
        if($document === null){
            var_dump($location);
            throw new Exception('Conversion error: cannot find conversion file...');
            die;
        }
        $duration = microtime(true) - $parse->data('time.start');

        // cost +/- 200 msec
        // $parse_document = new Parse($parse->handler(), $parse->route(), $parse->data());
        $token = $parse->tokenize($document);
        $keep = false;
        $execute = '';
        ob_start();
        foreach($token as $nr => $record){
            $record= $token[$nr];
            if(!empty($record['is_executed'])){
                continue;
            } else {
                if($record['is_operator'] === true){
                    $record['execute'] = $record['value'];
                    $record['is_executed'] = true;
                    $token[$record['token']['nr']]  = $record;
                } else {
                    switch($record['type']){
                        case Token::TYPE_VARIABLE :
                            $token = Variable::execute($parse, $record, $token, $keep, true);
                        break;
                        case Token::TYPE_METHOD :
                            $token = Method::execute($parse, $record, $token, $keep, true);
                        break;
                        case Token::TYPE_WHITESPACE :
                        case Token::TYPE_SEMI_COLON :
                        case Token::TYPE_COLON :
                        case Token::TYPE_DOUBLE_COLON :
                        case Token::TYPE_COMMA :
                            $record['execute'] = $record['value'];
                            $record['is_executed'] = true;
                            $token[$record['token']['nr']] = $record;
                        break;
                        case Token::TYPE_STRING :
                            if(!empty($record['is_quote_double'])){
                                $record['execute'] = $parse->compile($record['value']);
                            }
                            elseif(!empty($record['is_quote_single'])){
                                var_dump($record);
                                die;
                            }
                            else {
                                $record['execute'] = $record['value'];
                            }
                            $record['is_executed'] = true;
                            $token[$record['token']['nr']]  = $record;
                        break;
                    }
                }
            }
        }
        $content = ob_get_contents();
        ob_end_clean();
        foreach($token as $nr => $record){
            if(empty($record['is_executed'])){
                var_dump($record);
                $source = $parse->data(Parse::DATA_READ_URL);
                if($source === null){
                    throw new Exception('Compile error: unexecuted token on line:' . $record['row'] . ' column: ' . $record['colum']);
                } else {
                    throw new Exception('Compile error: unexecuted token on line:' . $record['row'] . ' column: ' . $record['colum'] . ' in: ' . $source);
                }
                die;
            }
            $execute .= $record['execute'];
        }

        // $parse_document->data(Parse::DATA_COMPILE_EXECUTE_HOLD, true);
        // $document = $parse_document->compile($document);
        $url = Compile::get_write_url($parse);

        $write = File::write($url, $execute);
        // echo $execute;
        if($write !== false){
            return $url;
        }
        return false;
    }

    public static function value_to_string(Parse $parse, $value = null){
        $type = strtolower(gettype($value));
        switch($type){
            case Token::TYPE_NULL:
                return 'null';
            break;
        }

    }
}