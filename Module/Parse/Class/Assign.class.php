<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use PRiya\Module\Parse;
use Exception;

class Assign extends Core {

    public static function cast($string=''){
        $type = getType($string);
        if($type == Parse::TYPE_STRING){
            $test = strtolower($string);
        
            if($test == 'true'){
                return true;
            }
            elseif($test == 'false'){
                return false;
            }
            elseif($test == 'null'){
                return null;
            }
            elseif(is_numeric($string)){
                return $string += 0;
            }
        }
        return $string;

    }

    public static function remove($tag=array(), $attribute='', $parser=null){
        $method = '=';
        $explode = explode($method, $tag['tag'], 2);

        if(!isset($explode[1])){
            $tag[$attribute] = $tag['tag'];
        } else {
            $tag[$attribute] = rtrim($explode[1], ' ');
        }
        return $tag;
    }

    public static function select($tag=array(), $parser=null){
        $method = '=';
        $explode = explode($method, $tag['tag'], 2);

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
        $tag['attribute'] = rtrim($variable[1], ' +-.!');
        $tag['assign'] = $method;
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $method = '=';
        $explode = explode($method, $tag['tag'], 2);

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
                    '!', //extend to multiple
                    '*',
                    '/',
                )
            )
        ){
            $method = $check . $method;
        }
        $tag['assign'] = $method;
        $tag['attribute'] = rtrim($variable[1], ' +-.!');
        $tag['value'] = rtrim($explode[1]);
        $tag['value'] = ltrim($tag['value'], ' ');

        if(substr($tag['value'], 0, 1) == '$'){
            $tag['value'] = '{' . $tag['value'];
        }
        elseif(substr($tag['value'], 0, 2) == '{$'){
            //needs to keep the }
        }
        else {
            $tag['value'] = rtrim($explode[1], '}');//not allowed
        }
        $tag['value'] = Parse::token($tag['value'], $parser->data(), false, $parser);

        switch($tag['assign']){
            case '+=' :
                $plus = $parser->data($tag['attribute']) + 0;
                $parser->data($tag['attribute'], $plus += $tag['value']);
                break;
            case '-=' :
                $min = $parser->data($tag['attribute']) + 0;
                $parser->data($tag['attribute'], $min -= $tag['value']);
                break;
            case '.=' :
                $add = $parser->data($tag['attribute']);
                $parser->data($tag['attribute'], $add .= $tag['value']);
                break;
            case '!=' :
                $not = $parser->data($tag['attribute']);
                $parser->data($tag['attribute'], $not != $tag['value']);
                break;
            default :
                $tag['value'] = Assign::cast($tag['value']);
                $parser->data($tag['attribute'], $tag['value']);
                break;
        }
        $temp = explode($tag['tag'], $string, 2);
        $string = implode('', $temp);
        return $string;
    }

}
