<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Variable extends Core {
    CONST STATUS = 'is_variable';

    public static function execute(Parse $parse, $variable=[], $token=[], $keep=false, $tag_remove=true){
        if($variable['variable']['is_assign'] === true){
            $token = Variable::assign($parse, $variable, $token, $keep);
            $variable = $token[$variable['token']['nr']];
        } else {
            $attribute = substr($variable['value'], 1);
            $variable['execute'] = $parse->data($attribute);
            $token = Variable::modify($parse, $variable, $token, $keep);
            $variable = $token[$variable['token']['nr']];
        }
        $variable['is_executed'] = true;
        $token[$variable['token']['nr']] = $variable;
        $token = Variable::cleanup($variable, $token, $tag_remove);
        return $token;
    }

    public static function cleanup($variable=[], $token=[], $tag_remove=true){
        return Token::variable_cleanup($variable, $token, $tag_remove);
    }

    /*
    public static function cleanup($token=[], $variable=[], $tag_open_nr=null, $tag_close_nr=null){
        if(
            $tag_open_nr !== null &&
            $tag_close_nr !== null
        ){
            $is_assign = false;
            for($i = $tag_open_nr; $i <= $tag_close_nr; $i++){
                if($i == $variable['token']['nr']){
                    if(
                        isset($token[$i]['variable']['is_assign']) &&
                        $token[$i]['variable']['is_assign'] === true
                        ){
                            //remove newline on next whitespace
                            $is_assign = true;
                    }
                    continue;
                }
                unset($token[$i]);
            }
            if($is_assign === true){
                $end = end($token);
                for($i = $tag_close_nr + 1; $i <= $end['token']['nr']; $i++){
                    if(isset($token[$i])){
                        $token[$i] = Token::remove_empty_line($token[$i], 'value');
                        break;
                    }
                }
                for($i = $tag_open_nr - 1; $i >= 0; $i--){
                    if(isset($token[$i])){
                        if($token[$i]['type'] == Token::TYPE_WHITESPACE){
                            $explode = explode("\n", $token[$i]['value']);
                            if(isset($explode[1])){
                                $end = array_pop($explode);
                                $explode[] = rtrim($end);
                                $token[$i]['value'] = implode("\n", $explode);
                            }
                        }
                    }
                }
            }
        }
        return $token;

    }
    */

    public static function modify(Parse $parse, $variable=[], $token=[], $keep=false, $tag_remove=true){
        return Token::modifier_execute($parse, $variable, $token, $keep, $tag_remove);
    }

    public static function value(Parse $parse, $variable=[], $token=[], $keep=false, $tag_remove=true){
        return Token::set_execute($parse, $variable['variable']['value'], $variable, $token, $keep, $tag_remove);
    }

    public static function assign(Parse $parse, $variable=[], $token=[], $keep=false, $tag_remove=true){
        switch($variable['variable']['operator']){
            case '=' :
                if(!isset($token[$variable['token']['nr']])){
                    var_dump('found');
                    die;
                }
                $token = Variable::value($parse, $variable, $token, $keep, $tag_remove);
                $variable = $token[$variable['token']['nr']];
                $attribute = substr($variable['variable']['name'], 1 );
                if(!isset($variable['is_executed'])){
                    var_dump($variable);
                    die;
                }
                $parse->data($attribute, $variable['execute']);
            break;
            case '+=' :
            break;
            case '-=' :
            break;
            case '*=' :
            break;
            case '%=' :
            break;
            case '/=' :
            break;
            case '++' :
                $attribute = substr($variable['variable']['name'], 1 );
                $value = $parse->data($attribute);
                if($value === null){
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']);
                    } else {
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']  . ' in file: ' . $source);
                    }
                }
                $value++;
                $parse->data($attribute, $value);
            break;
            case '--' :
                $attribute = substr($variable['variable']['name'], 1 );
                $value = $parse->data($attribute);
                if($value === null){
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']);
                    } else {
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']  . ' in file: ' . $source);
                    }
                }
                $value--;
                $parse->data($attribute, $value);
            break;
            case '**' :
                $attribute = substr($variable['variable']['name'], 1 );
                $value = $parse->data($attribute);
                if($value === null){
                    $source = $parse->data('priya.parse.read.url');
                    if($source === null){
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']);
                    } else {
                        throw new Exception('Undefined variable: ' . $variable['variable']['name'] . ' on line: ' . $variable['row'] . ' column: ' . $variable['column']  . ' in file: ' . $source);
                    }
                }
                $value = $value * $value;
                $parse->data($attribute, $value);
                $is_debug =true;
            break;
            case '**=' :
            break;
            case '^=' :
            break;
            case '&=' :
            break;
            case '|=' :
            break;
        }
        if($keep === true){
            $variable['execute'] = $variable['value'];
        } else {
            $variable['execute'] = null;
            $variable = Token::value_type($variable, 'execute');
        }
        $token[$variable['token']['nr']] = $variable;
        return $token;
    }
}