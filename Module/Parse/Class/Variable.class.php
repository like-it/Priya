<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Variable extends Core {
    CONST STATUS = 'is_variable';

    public static function execute(Parse $parse, $variable=[], $token=[], $keep=false){

        if($variable['variable']['is_assign'] === true){
            $variable = Variable::assign($parse, $variable, $token, $keep);
        } else {
            $attribute = substr($variable['value'], 1);
            $variable['execute'] = $parse->data($attribute);
            $variable = Variable::modify($parse, $variable, $token, $keep);
            $variable['is_executed'] = true;
        }
        return $variable;
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
            $value = Token::set_execute($parse, $variable['variable']['value']);
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
//         }
        return $variable;
    }

    public static function assign(Parse $parse, $variable=[], $token=[], $keep=false){
        switch($variable['variable']['operator']){
            case '=' :
                $variable = Variable::value($parse, $variable, $token=[], $keep);
                $attribute = substr($variable['variable']['name'], 1 );
                $parse->data($attribute, $variable['variable']['execute']);
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
            break;
            case '--' :
            break;
            case '**' :
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
            $variable['execute'] = '';
        }
        return $variable;
    }
}