<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use PRiya\Module\Parse;
use Exception;

class Assign extends Core {

    public static function remove($tag=array(), $attribute='', $parser=null){
        $method = '=';
        $explode = explode($method, $tag[Tag::TAG], 2);

        if(!isset($explode[1])){
            $tag[$attribute] = $tag[Tag::TAG];
        } else {
            $tag[$attribute] = rtrim($explode[1], ' ');
        }
        return $tag;
    }

    public static function select($tag=array(), $parser=null){
        $method = '=';
        $explode = explode($method, $tag[Tag::TAG], 2);

        $tag[Tag::ATTRIBUTE] = null;
        $tag[Tag::ASSIGN] = null;

        if(!isset($explode[1])){
            return $tag;
        }

        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);

        if(isset($before[1])){
            return $tag;
        }
        $variable = explode('$', $explode[0], 2);
        if(!isset($variable[1])){
            return $tag;
        }
        //we have an assign
        $check = substr($explode[0], -1);
        if(
            in_array(
                $check,
                array(
                    '+',
                    '-',
                    '.',
                    '!',
                    '*',
                    '/',
                )
            )
        ){
            $method = $check . $method;
        }
        $tag[Tag::ATTRIBUTE] = rtrim($variable[1], '+-.!*/ ');
        $tag[Tag::ASSIGN] = $method;
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $method = '=';
        $explode = explode($method, $tag[Tag::TAG], 2);

        if(!isset($explode[1])){
            return $string;
        }

        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);

        if(isset($before[1])){
            return $string;
        }
        $variable = explode('$', $explode[0], 2);
        if(!isset($variable[1])){
            return $string;
        }
        //we have an assign

        $check = substr($explode[0], -1);

        if(
            in_array(
                $check,
                array(
                    '+',
                    '-',
                    '.',
                    '!',
                    '*',
                    '/',
                )
            )
        ){
            $method = $check . $method;
        }
        $tag[Tag::ASSIGN] = $method;
        $tag[Tag::ATTRIBUTE] = rtrim($variable[1], ' +-.!');
        $tag[Tag::ATTRIBUTE_VALUE] = trim($explode[1], ' ');

        if(substr($tag[Tag::ATTRIBUTE_VALUE], 0, 1) == '$'){
            $tag[Tag::ATTRIBUTE_VALUE] = '{' . $tag[Tag::ATTRIBUTE_VALUE];
        }
        elseif(substr($tag[Tag::ATTRIBUTE_VALUE], 0, 2) == '{$'){
            //needs to keep the }
        }
        else {
            if(substr($tag[Tag::ATTRIBUTE_VALUE], -1) == '}'){
                $tag[Tag::ATTRIBUTE_VALUE] = substr($tag[Tag::ATTRIBUTE_VALUE], 0, -1);
            }
        }
        $tag[Tag::ATTRIBUTE_VALUE] = Parse::token($tag[Tag::ATTRIBUTE_VALUE], $parser->data(), false, $parser);
        $tag = Assign::execute($tag, Tag::ATTRIBUTE_VALUE, $parser);
        $temp = explode($tag[Tag::TAG], $string, 2);
        $string = implode('', $temp);
        return $string;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
        if(
            !empty($tag[Tag::ATTRIBUTE]) &&
            !empty($tag[Tag::ASSIGN])
        ){
            if(!isset($tag[$attribute])){
                $tag[$attribute] = null;
            }
            $left = Cast::translate($parser->data($tag[Tag::ATTRIBUTE]));
            $right = Cast::translate($tag[$attribute]);
            $type = gettype($tag[$attribute]);

            if($type == Parse::TYPE_ARRAY){
                switch($tag[Tag::ASSIGN]){
                    case '+' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                    break;
                    default :
                        $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
                }
            }
            elseif($type == Parse::TYPE_OBJECT){
                switch($tag[Tag::ASSIGN]){
                    case '+' :
                        $left = $parser->data($tag['attribute']);
                        $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                    break;
                    default :
                        $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
                }
            } else {
                switch($tag[Tag::ASSIGN]){
                    case '.=' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left .= $right);
                    break;
                    case '+=' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left += $right);
                    break;
                    case '-=' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left -= $right);
                    break;
                    case '*=' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left * $right);
                    break;
                    case '/=' :
                        if(empty($right)){
                            throw new Exception('Cannot divide to zero on line: ' . $tag[Tag::LINE] . ' column: ' . $tag[Tag::COLUMN] . ' in ' . $parser->data('priya.module.parser.document.url'));
                        }
                        $parser->data($tag[Tag::ATTRIBUTE], $left / $right);
                    break;
                    case '+' :
                        $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                    break;
                    default :
                        $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
                }
            }
        }
        return $tag;
    }
}
