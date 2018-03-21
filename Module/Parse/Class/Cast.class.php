<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
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
        $tag['cast'] = krsort($cast);
       return $tag;
    }

    public static function execute($tag=array(), $parser=null){
        if(empty($tag['cast'])){
            return $tag;
        }
        foreach($tag['cast'] as $cast){
            switch($cast){
                case Cast::TYPE_INT:
                case Cast::TYPE_INTEGER:
                    $tag['execute'] = (int) $tag['execute'];
                break;
                case Cast::TYPE_FLOAT:
                    $tag['execute'] = (float) $tag['execute'];
                    break;
                case Cast::TYPE_BOOL:
                case Cast::TYPE_BOOLEAN:
                    $tag['execute'] = (bool) $tag['execute'];
                    break;
                case Cast::TYPE_STRING:
                    $tag['execute'] = (string) $tag['execute'];
                    break;
                case Cast::TYPE_ARRAY:
                    $tag['execute'] = (array) $tag['execute'];
                    break;
                case Cast::TYPE_OBJECT:
                    $tag['execute'] = (object) $tag['execute'];
                    break;
            }

        }
        return $tag;
    }
}