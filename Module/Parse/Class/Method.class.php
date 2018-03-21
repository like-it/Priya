<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Method extends Core {

    public static function clean($tag=array(), $attribute='', $parser=null){
        $tag[$attribute] = trim($tag[$attribute], ' ');
        return $tag;
    }

    public static function execute($tag=array(), $parser=null){
        $name = str_replace(
            array(
                '..',
                '//',
                '\\',
            ),
            '',
            ucfirst(strtolower($tag['method']))
        );
        $tag['function']['name'] = $name;

        $require = $parser->data('priya.module.parser.require');
        $url =  __DIR__ . '/../Function/Function.Import.php';
        if(!in_array($url, $require)){
        	require_once $url;
        }

        $import = $parser->data('priya.module.parser.import');
        foreach($import as $url){
            $url = $url . 'Function.' . $name . '.php';
            if(in_array($url, $require)){
                break;
            }
            if(file_exists($url)){
                require_once $url;
                $require[] = $url;
                $parser->data('priya.module.parser.require', $require);
                break;
            }
        }
        $name = 'function_' . str_replace('.', '_', strtolower($name));
        $tag['function']['method'] = $name;

        if(function_exists($tag['function']['method'])){
            return $name($tag, $parser);
        } else {
            Throw new Exception('Function "' . $tag['function']['name'] . '" not found on line: ' . $tag['line']  . ' column: ' . $tag['column'] . ' in ' .  $parser->data('priya.module.parser.document.url'));
        }
    }

    public static function replace($tag=array(), $parser=null){
        $explode = explode($tag['tag'], $tag['string'], 2);
        $type = gettype($tag['execute']);
        if(
        	in_array(
        		$type,
        		array(
        			Parse::TYPE_ARRAY,
        			Parse::TYPE_OBJECT
        		)
        	)
        ){
        	$tag['string'] = implode('', $explode);
        	if(empty($tag['string'])){
        		//have parameters or similar...
        		$tag['string'] = $tag['execute'];
        	}
        } else {
        	$tag['string'] = implode($tag['execute'], $explode);
        }
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $tag = Assign::select($tag, $parser);
        $tag = Assign::remove($tag, 'method', $parser);
        $tag = Cast::find($tag, 'method', $parser);
        $tag = Exclamation::find($tag, 'method', $parser);
        $tag = Method::clean($tag, 'method', $parser);
        $method = '(';
        $explode = explode($method, $tag['method'], 2);
        if(!isset($explode[1])){
            return $string;
        }
        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);
        if(isset($before[1])){
            return $string;
        }
        //have method...
        $tag['method'] = ltrim($explode[0], '{');
        $explode = explode('})', strrev($explode[1]), 2);
        $tag['parameter'] = strrev($explode[1]);
        $tag['parameter'] = Parameter::find($tag['parameter'], $parser);
        $tag['string'] = $string;
        $tag = Method::execute($tag, $parser);
        $tag = Exclamation::exectute($tag, $parser);
        $tag = Cast::execute($tag, $parser);
        $type = gettype($tag['execute']);
        if($type == Parse::TYPE_ARRAY){
            if(isset($tag['attribute'])){
                switch($tag['assign']){
                    case '+' :
                        $left = $parser->data($tag['attribute']);
                        $parser->data($tag['attribute'], $left + $tag['execute']);
                        break;
                    default :
                        $parser->data($tag['attribute'], $tag['execute']);
                    break;
                }
            }
        }
        elseif($type == Parse::TYPE_OBJECT){
            if(isset($tag['attribute'])){
                switch($tag['assign']){
                    case '+' :
                        $left = $parser->data($tag['attribute']);
                        $parser->data($tag['attribute'], $left + $tag['execute']);
                        break;
                    default :
                        $parser->data($tag['attribute'], $tag['execute']);
                        break;
                }
            }
        } else {
            if(!empty($tag['attribute'])){
            	$left = Cast::translate($parser->data($tag['attribute']));
            	$right = Cast::translate($tag['execute']);
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
        $tag = Method::replace($tag, $parser);
        return $tag['string'];
    }
}