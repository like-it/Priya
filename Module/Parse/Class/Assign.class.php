<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use PRiya\Module\Parse;
use Exception;

class Assign extends Core {

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

        $tag['attribute'] = null;
        $tag['assign'] = null;

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
        $tag['attribute'] = rtrim($variable[1], '+-.!*/ ');
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
                    '!',
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
        $tag = Assign::execute($tag, 'value', $parser);
        $temp = explode($tag['tag'], $string, 2);
        $string = implode('', $temp);
        return $string;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
    	if(
    		!empty($tag['attribute']) &&
    		!empty($tag['assign'])
    	){
    		if(!isset($tag[$attribute])){
    			$tag[$attribute] = null;
    		}
    		$left = Cast::translate($parser->data($tag['attribute']));
    		$right = Cast::translate($tag[$attribute]);
    		$type = gettype($tag[$attribute]);

    		if($type == Parse::TYPE_ARRAY){
    			switch($tag['assign']){
    				case '+' :
    					$parser->data($tag['attribute'], $left + $right);
    				break;
    				default :
    					$parser->data($tag['attribute'], $right);
    				break;
    			}
    		}
    		elseif($type == Parse::TYPE_OBJECT){
    			switch($tag['assign']){
    				case '+' :
    					$left = $parser->data($tag['attribute']);
    					$parser->data($tag['attribute'], $left + $right);
    				break;
    				default :
    					$parser->data($tag['attribute'], $right);
    				break;
    			}
    		} else {
    			switch($tag['assign']){
    				case '.=' :
    					$parser->data($tag['attribute'], $left .= $right);
    				break;
    				case '+=' :
    					$parser->data($tag['attribute'], $left += $right);
    				break;
    				case '-=' :
    					$parser->data($tag['attribute'], $left -= $right);
    				break;
    				case '*=' :
    					$parser->data($tag['attribute'], $left * $right);
    				break;
    				case '/=' :
    					if(empty($right)){
    						throw new Exception('Cannot divide to zero on line: ' . $tag['line'] . ' column: ' . $tag['column'] . ' in ' . $parser->data('priya.module.parser.document.url'));
    					}
    					$parser->data($tag['attribute'], $left / $right);
    				break;
    				case '+' :
    					$parser->data($tag['attribute'], $left + $right);
    				break;
    				default :
    					$parser->data($tag['attribute'], $right);
    				break;
    			}
    		}
    	}
    	return $tag;
    }
}
