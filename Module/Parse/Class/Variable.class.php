<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Variable extends Core {
    CONST STATUS = 'is_variable';

    public static function execute(Parse $parse, $variable=[], $token=[], $keep=false){

        if($variable['variable']['is_assign'] === true){
            $token = Variable::assign($parse, $variable, $token, $keep);
            $variable = $token[$variable['token']['nr']];
//             var_dump($token);
        } else {
            $attribute = substr($variable['value'], 1);
            $variable['execute'] = $parse->data($attribute);
            $token = Variable::modify($parse, $variable, $token, $keep);
            $variable = $token[$variable['token']['nr']];
        }
        $variable['is_executed'] = true;
        $token[$variable['token']['nr']] = $variable;
        return $token;
    }

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

    public static function modify(Parse $parse, $variable=[], $token=[], $keep=false){
        return Token::modifier_execute($parse, $variable, $token, $keep);
    }

    public static function value(Parse $parse, $variable=[], $token=[], $keep=false){
        /*
        if(!isset($variable['variable']['value'][1])){
            $variable['variable']['value'] = $variable['variable']['value'][0];
            if($variable['variable']['value']['type'] == Token::TYPE_VARIABLE){
//                 $attribute =
                var_dump($variable['variable']);
                var_Dump('is_var');
            }
            elseif($variable['variable']['value']['type'] == Token::TYPE_METHOD){
                var_dump($parse->data());
                var_Dump('is_met');
            }
            die;
        } else {
        */
//         var_dump($token);
//         var_dump($variable);
//         die;
// Token::set_execute($parse)
            $token = Token::set_execute($parse, $variable['variable']['value'], $variable, $token);
//             var_dump($token);


            /*
            var_dump($token);
            die;


//             $token = Token::token_set_execute($parse, $value, $token);
            $list = [];
            foreach($value as $part){
                $list[] = $part;
            }
            if(!isset($list[1])){
                $variable['variable']['result'] = $list;
                $variable['variable']['execute'] = $list[0]['execute'];
            } else {
                throw new Exception('Value should be down to 1, multiple left...');
            }
            */
//         }
        return $token;
    }

    public static function assign(Parse $parse, $variable=[], $token=[], $keep=false){
        switch($variable['variable']['operator']){
            case '=' :
                if(!isset($token[$variable['token']['nr']])){
                    var_dump('found');
                    die;
                }
//                 var_dump($token[$variable['token']['nr']]);
                $token = Variable::value($parse, $variable, $token, $keep);
                $variable = $token[$variable['token']['nr']];

                /*
                if($variable['variable']['name'] == '$user'){
                    var_dump($token);
//                     die;
                }
                */

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
//         return $variable;
    }
}