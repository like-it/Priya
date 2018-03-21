<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Cast extends Core {
    const TYPE_INT = 'int';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_BOOLEAN= 'boolean';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';

    const TRANSLATE_TRUE = 'true';
    const TRANSLATE_FALSE = 'false';
    const TRANSLATE_NULL = 'null';

    const LIST = array(
        Cast::TYPE_BOOL,
        Cast::TYPE_BOOLEAN,
        Cast::TYPE_FLOAT,
        Cast::TYPE_INT,
        Cast::TYPE_INTEGER,
        Cast::TYPE_STRING,
        Cast::TYPE_ARRAY,
        Cast::TYPE_OBJECT
    );

    public static function translate($string=''){
        $type = getType($string);
        if($type == Parse::TYPE_STRING){
            $test = strtolower($string);
            if($test == Cast::TRANSLATE_TRUE){
                return true;
            }
            elseif($test == Cast::TRANSLATE_FALSE){
                return false;
            }
            elseif($test == Cast::TRANSLATE_NULL){
                return null;
            }
            elseif(is_numeric($string)){
                return $string += 0;
            }
        }
        return $string;
    }

    public static function find($tag='', $attribute='', $parser=null){
        if(strpos($tag[$attribute], '(') === false){
            return $tag;
        }
        $explode = explode('(', $tag[$attribute]);

        $cast = array();
        foreach($explode as $part){
            $temp = explode(')', $part, 2);
            if(isset($temp[1])){
                $key = strtolower(trim($temp[0], ' '));
                if(in_array($key, Cast::LIST)){
                    $cast[] = $key;
                    $tmp = explode('(' . $temp[0] . ')', $tag[$attribute], 2);
                    $tag[$attribute] = implode('', $tmp);
                } else {
                    break;
                }
            }
        }
        krsort($cast);
        $tag[Tag::ATTRIBUTE_CAST] = $cast;
       return $tag;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
        if(empty($tag[Tag::ATTRIBUTE_CAST])){
            return $tag;
        }
        if(!isset($tag[$attribute])){
            $tag[$attribute] = null;
        }
        foreach($tag[Tag::ATTRIBUTE_CAST] as $cast){
            switch($cast){
                case Cast::TYPE_INT:
                case Cast::TYPE_INTEGER:
                    $tag[$attribute] = (int) $tag[$attribute];
                break;
                case Cast::TYPE_FLOAT:
                    $tag[$attribute] = (float) $tag[$attribute];
                    break;
                case Cast::TYPE_BOOL:
                case Cast::TYPE_BOOLEAN:
                    $tag[$attribute] = (bool) $tag[$attribute];
                    break;
                case Cast::TYPE_STRING:
                    $tag[$attribute] = (string) $tag[$attribute];
                    break;
                case Cast::TYPE_ARRAY:
                    $tag[$attribute] = (array) $tag[$attribute];
                    break;
                case Cast::TYPE_OBJECT:
                    $tag[$attribute] = (object) $tag[$attribute];
                    break;
            }
        }
        return $tag;
    }
}