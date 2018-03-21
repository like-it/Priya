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
        if(!isset($tag[Tag::ATTRIBUTE_METHOD])){
            var_dump($tag);
            var_dump(debug_backtrace(true));
            die;
        }
        $name = str_replace(
            array(
                '..',
                '//',
                '\\',
            ),
            '',
            ucfirst(strtolower($tag[Tag::ATTRIBUTE_METHOD]))
        );
        $tag[Tag::ATTRIBUTE_FUNCTION][Tag::ATTRIBUTE_NAME] = $name;
        $require = $parser->data('priya.module.parser.require');
        $url =  __DIR__ . '/../Function/Function.Import.php';
        if(!in_array($url, $require)){
            require_once $url;
        }
        $import = $parser->data('priya.module.parser.import');
        foreach($import as $url){
            $url = $url . ucfirst(Tag::ATTRIBUTE_FUNCTION) . '.' . $name . '.php';
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
        $name = Tag::ATTRIBUTE_FUNCTION .'_' . str_replace('.', '_', strtolower($name));
        $tag[Tag::ATTRIBUTE_FUNCTION][Tag::ATTRIBUTE_METHOD] = $name;
        if(function_exists($tag[Tag::ATTRIBUTE_FUNCTION][Tag::ATTRIBUTE_METHOD])){
            return $name($tag, $parser);
        } else {
            var_dump($url);
            Throw new Exception(ucfirst(Tag::ATTRIBUTE_FUNCTION) . ' "' . $tag[Tag::ATTRIBUTE_FUNCTION][Tag::ATTRIBUTE_NAME] . '" not found on line: ' . $tag['line']  . ' column: ' . $tag['column'] . ' in ' .  $parser->data('priya.module.parser.document.url'));
        }
    }

    public static function replace($tag=array(), $attribute='', $parser=null){
        $explode = explode($tag[Tag::ATTRIBUTE_TAG], $tag[Tag::ATTRIBUTE_STRING], 2);
        if(!isset($tag[$attribute])){
            $tag[$attribute] = null;
        }
        $type = gettype($tag[$attribute]);
        if(
            in_array(
                $type,
                array(
                    Parse::TYPE_ARRAY,
                    Parse::TYPE_OBJECT
                )
            )
        ){
            $tag[Tag::ATTRIBUTE_STRING] = implode('', $explode);
            if(empty($tag[Tag::ATTRIBUTE_STRING])){
                //have parameters or similar...
                $tag[Tag::ATTRIBUTE_STRING] = $tag[$attribute];
            }
        } else {
            $tag[Tag::ATTRIBUTE_STRING] = implode($tag[$attribute], $explode);
        }
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $tag = Assign::select($tag, $parser);
        $tag = Assign::remove($tag, Tag::ATTRIBUTE_METHOD, $parser);
        $tag = Cast::find($tag, Tag::ATTRIBUTE_METHOD, $parser);
        $tag = Exclamation::find($tag, Tag::ATTRIBUTE_METHOD, $parser);
        $tag = Method::clean($tag, Tag::ATTRIBUTE_METHOD, $parser);
        $method = '(';
        $explode = explode($method, $tag[Tag::ATTRIBUTE_METHOD], 2);
        if(!isset($explode[1])){
            return $string;
        }
        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);
        if(isset($before[1])){
            return $string;
        }
        //have method...
        $tag[Tag::ATTRIBUTE_METHOD] = ltrim($explode[0], '{');
        $explode = explode('})', strrev($explode[1]), 2);
        if(!isset($explode[1])){
            var_dump($explode);
            die;
        }
        $tag[Tag::ATTRIBUTE_PARAMETER] = strrev($explode[1]);
        $tag[Tag::ATTRIBUTE_PARAMETER] = Parameter::find($tag[Tag::ATTRIBUTE_PARAMETER], $parser);
        $tag = Parameter::execute($tag, Tag::ATTRIBUTE_EXECUTE, $parser);
        //execute to constant
        $tag[Tag::ATTRIBUTE_STRING] = $string;
        $tag = Method::execute($tag, $parser);
        $tag = Exclamation::exectute($tag, Tag::ATTRIBUTE_EXECUTE, $parser);
        $tag = Cast::execute($tag, Tag::ATTRIBUTE_EXECUTE, $parser);
        $tag = Assign::execute($tag, Tag::ATTRIBUTE_EXECUTE, $parser);
        $tag = Method::replace($tag, Tag::ATTRIBUTE_EXECUTE, $parser);
        return $tag[Tag::ATTRIBUTE_STRING];
    }

    public static function is($string='', $parser){
        $method = '(';
        $explode = explode($method, $string, 2);
        if(!isset($explode[1])){
            return false;
        }
        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);
        if(isset($before[1])){
            return false;
        }
        return true;
    }
}