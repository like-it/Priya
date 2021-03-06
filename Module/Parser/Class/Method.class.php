<?php

namespace Priya\Module\Parser;

use Exception;

class Method extends Core {
    const MAX = 1024;

    public static function find($record=array(), \Priya\Module\Parser $parser){
        if(
            substr($record['method']['tag'], 0, 1) != '{' &&
            substr($record['method']['tag'], -1, 1) != '}'
        ){
            return $record;
        }
        $method = substr($record['method']['tag'], 1, -1);
        $method = Token::restore_return($method, $parser->random());
        $parse = Token::parse($method);
        $record['parse'] = $parse;
        //this has to find the first method in parse & return it!
        $is_method = false;

        $record = Token::method($record, $parser);

        //fix has_Exclamation
        foreach($record['parse'] as $key => $value){
            if($value['type'] == Token::TYPE_METHOD){
                $is_method = true;
                break;
            }
        }
        if($is_method === true){
            $method = $value;
        } else {
            return $record;
        }
//         debug($record, __LINE__ . '::' . __FILE__);
        if(!isset($record['string'])){
            return $record;
        }
        if(
            is_object($record['string']) ||
            is_array($record['string'])
        ){
            return $record;
        }
        $explode = explode($record['method']['tag'], $record['string'], 2);
        if(empty($explode[0]) && empty($explode[1])){
            $record['string'] = $method['value'];
        } else {
            if($method['value'] === true){
                $record['string'] = implode('true', $explode);
            }
            elseif($method['value'] === false){
                $record['string'] = implode('false', $explode);
            }
            elseif($method['value'] === null){
                $record['string'] = implode('null', $explode);
            }
            elseif(is_numeric($method['value'])){
                $record['string'] = implode($method['value'] + 0, $explode);
            }
            elseif(is_string($method['value'])){
                $record['string'] = implode($method['value'], $explode);
            }
            elseif(is_object($method['value']) && isset($method['value']->__tostring)){
                $record['string'] = implode($method['value']->__tostring, $explode);
            }
            elseif(is_object($method['value']) && !isset($method['value']->__tostring)){
                $record['string'] = implode(Method::object($method['value'], 'json'), $explode);
            }
            elseif(is_array($method['value'])){
                $record['string'] = implode(Method::object($method['value'], 'json'), $explode);
            }
        }
        return $record;
    }

    public static function exclamation($record=array(), $method=array(), $parser=null){
        $parse = $record['parse'];
        krsort($parse); //from back to ( (beginning of set)
        $exclamation_count = 0;
        foreach($parse as $nr => $record){
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == ')'
            ){
                continue;
            }
            if($record['type'] == Token::TYPE_EXCLAMATION){
                $exclamation_count++;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == '('
            ){
                break;
            }
        }
        $method['exclamation_count'] = $exclamation_count;
        if($exclamation_count > 0){
            $method['has_exclamation'] = true;
        } else {
            $method['has_exclamation'] = false;
        }
        if($exclamation_count% 2 == 1){
            $method['invert'] = true;
        } else {
            $method['invert'] = false;
        }
        $method['method'] = str_replace('!', '', $method['method']);
        $method = Token::Exclamation($method);
        return $method;
    }

    public static function remove_exclamation($record=array()){
        $parse = $record['parse'];
        krsort($parse); //from back to ( (beginning of set)
        foreach($parse as $nr => $item){
            if(
                $item['type'] == Token::TYPE_PARENTHESE &&
                $item['value'] == ')'
            ){
                continue;
            }
            if($item['type'] == Token::TYPE_EXCLAMATION){
                unset($record['parse'][$nr]);
            }
            if(
                $item['type'] == Token::TYPE_PARENTHESE &&
                $item['value'] == '('
            ){
                break;
            }
        }
        return $record;
    }


    public static function get($parse=array(), $parser=null){
//         $debug = debug_backtrace(true);
//         var_dump($debug[0]);
//         var_dump($debug[0]['args']);
        $is_method = false;
        $possible_method = false;
        $list = array();
        $parameter = array();
        $result = array();
        $parse_method = array();
        $method_has_name = false;

        foreach ($parse as $nr => $record){
//
            if(
                $record['type'] == Token::TYPE_METHOD &&
                isset($record['method'])
            ){
                continue;
            }
            if($is_method === true){
                if(
                    $record['type'] == Token::TYPE_PARENTHESE &&
                    $record['value'] == ')' &&
                    $method_part['set']['depth'] + 1 == $record['set']['depth']
                ){
                    $parse_method[$nr] = $record;
                    $result = array();
                    $result['type'] = Token::TYPE_METHOD;
                    $result['value'] = '';
                    $result['method'] = '';
                    foreach($list as $list_nr => $list_value){
                        $result['method'] .= $list_value['value'];
                    }
                    $count = substr_count($result['method'], '!');
                    $result['exclamation_count'] = $count;
                    if($count > 0){
                        $result['has_exclamation'] = true;
                    } else {
                        $result['has_exclamation'] = false;
                    }
                    if($count % 2 == 1){
                        $result['invert'] = true;
                    } else {
                        $result['invert'] = false;
                    }
                    $result['method'] = str_replace('!', '', $result['method']);
                    $result['set']['depth'] = $method_part['set']['depth'];
//                     var_dump($parameter); //not is parameter but parse?
                    $result['parameter'] = Parameter::get($parameter, $parser);
//                     var_dump($result['parameter']);
                    $result['parse_method'] = $parse_method; //all records of parse which is used to create the method
                    //maybe extend cast to all parse_method tokens
                    $possible_cast = reset($parse_method);
                    if($possible_cast['is_cast'] === true){
                        $result['is_cast'] = true;;
                        $result['cast'] = $possible_cast['cast'];
                    }
//                     var_dump($result);
                    return $result;
                }
                $parameter[] = $record;
                $parse_method[$nr] = $record;
                continue;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE &&
                $record['value'] == '(' &&
                $possible_method === true
            ){
                foreach($list as $list_nr => $list_value){
                    if(
                        in_array(
                            $list_value['type'],
                            array(
                                Token::TYPE_STRING,
                                Token::TYPE_METHOD,
                            )
                        )
                    ){
                        $method_has_name = true;
                    }
                }
                $method_part = end($list);
                if($method_part['set']['depth'] + 1 == $record['set']['depth']){
                    if($method_has_name === true){
                        $is_method = true;
                        $previous_list_nr = false;
                        foreach($list as $list_nr => $list_value){
                            if($previous_list_nr !== false && $list_nr - $previous_list_nr > 1){
                                //everthing before this out
                                foreach($list as $previous_list_nr => $previous_list_value){
                                    if($list_nr == $previous_list_nr){
                                        break;
                                    }
                                    unset($list[$previous_list_nr]);
                                    unset($parse_method[$previous_list_nr]);
                                }
                            }
                            $previous_list_nr = $list_nr;
                        }
                    }
                }
                $parse_method[$nr] = $record;
            }
            if(
                $record['type'] == Token::TYPE_PARENTHESE  &&
                $is_method === false
            ){
                continue;
            }

            if(
                in_array(
                    $record['type'],
                    array(
                        Token::TYPE_STRING,
                        Token::TYPE_METHOD,
                        Token::TYPE_DOT
                    )
                )
            ){
                $possible_method = true;
                $list[$nr] = $record;
                $parse_method[$nr] = $record;
            }
            if($record['type'] == Token::TYPE_EXCLAMATION){
                $list[$nr] = $record;
                $parse_method[$nr] = $record;
            }
        }
        return false;
    }

    public static function method_execute($function=array(), \Priya\Module\Parser $parser){
        $name = str_replace(
            array(
                '..',
                 '//',
                 '\\',
            ),
            '',
            ucfirst(strtolower($function['method']))
        );
//         debug($function, __LINE__ . '::' . __FILE__);
        $function_name = $name;
        $url = __DIR__ . '/../Function/Function.List.php';
        if(
            empty($parser->has_list) &&
            file_exists($url)
        ){
            $parser->has_list = true;
            require_once $url;
            $name_tmp = 'function_' . str_replace('.', '_', strtolower($name));
            if(!function_exists($name_tmp)){    //add in_array $name_tmp for speed...
                $url = __DIR__ . '/../Function/Function.' . $name . '.php';
                if(file_exists($url)){
                    require_once $url;
                }
            }
        }
        if($parser->has_list !== true){
            $url = __DIR__ . '/../Function/Function.' . $name . '.php';
            if(file_exists($url)){
                require_once $url;
            }
            $function_dir = $parser->data('priya.parser.function.dir');
            if(is_array($function_dir)){
                foreach($function_dir as $dir){
                    if(substr($dir, -1) != '/'){
                        $dir .= '/';
                    }
                    $url = $dir . 'Function.' . $name . '.php';
                    if(file_exists($url)){
                        require_once $url;
                    }
                }
            }
        }
        $name = 'function_' . str_replace('.', '_', strtolower($name));
        if(function_exists($name)){
            $argument = array();
            if(isset($function['parameter'])){
                foreach ($function['parameter'] as $nr => $parameter){
                    if(!isset($parameter['value'])){
                        $parameter['value'] = null;
                    }
                    if(isset($parameter['value']) || $parameter['value'] === null){
                        $parameter['value'] = $parser->compile($parameter['value'], $parser->data());
                        $parameter = Value::type($parameter);

                        if($parameter['type'] == Token::TYPE_STRING && substr($parameter['value'], 0, 1) == '\'' && substr($parameter['value'], -1) == '\''){
                            $parameter['value'] = substr($parameter['value'], 1, -1);
                            $parameter['value'] = str_replace('\\\'', '\'', $parameter['value']);
                        }
                        $argument[] = $parameter['value'];
                        $function['parameter'][$nr] = $parameter;
                    }
                }
            }
// echo '<hr><hr>';
// echo $function['string'];
// echo '<hr><hr>';
            $function = $name($function, $argument, $parser, $parser->data());
            $function['value'] = $function['execute'];

            if($function['has_exclamation'] === true){
                if($function['invert'] === true){
                    if(empty($function['value'])){
                        $function['value'] = true;
                    } else {
                        $function['value'] = false;
                    }
                    $function['invert'] = false;
                } else {
                    $function['value'] = (bool) $function['value'];
                }
            }
            $function['is_executed'] = true;
        } else {
            $function['is_executed'] = false;
            throw new Exception('Method::execute:Function "' . $function_name . '" not found');
        }
        if(is_bool($function['value'])){
            if($function['value'] === true){
                $function['value'] = 'true';
            } else {
                $function['value'] = 'false';
            }
        }
        return $function;
    }

}