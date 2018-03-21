<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Variable extends Core {

    public static function find($tag='', $string='', $keep=false, $parser=null){
        if(
            substr($tag['tag'], 0, 1) == '{' &&
            substr($tag['tag'], -1, 1) == '}' &&
            substr($tag['tag'], 1, 1) == '$'
        ){
            if(strpos($tag['tag'], '=') !== false){
                //we might have assign;
                $explode = explode('=', $tag['tag']);
                $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);

                if(!isset($before[1])){
                    //we have assign
                    return $string;
                }
            }
            //have variable...
            $attribute = substr($tag['tag'], 2, -1);
            $result =  $parser->data($attribute);
            if($result === null && $keep){
                return $string;
            }
            $explode = explode($tag['tag'], $string, 2);
            $type = gettype($result);
            if($type == Parse::TYPE_ARRAY){
                $result = '';
            }
            elseif($type == Parse::TYPE_OBJECT){
                $result = '';
            }
            $string = implode($result, $explode);
            return $string;
        }
        return $string;
    }

}