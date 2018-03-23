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
        if(!isset($tag[Tag::METHOD])){
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
            ucfirst(strtolower($tag[Tag::METHOD]))
        );
        $tag[Tag::RUN][Tag::NAME] = $name;
        $require = $parser->data('priya.module.parser.require');
        $url = __DIR__ . '/../Function/Function.Import.php';
        if(!in_array($url, $require)){
            require_once $url;
        }
        $import = $parser->data('priya.module.parser.import');
        foreach($import as $url){
            $url = $url . ucfirst(Tag::RUN) . '.' . $name . '.php';
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
        $name = Tag::RUN.'_' . str_replace('.', '_', strtolower($name));
        $tag[Tag::RUN][Tag::METHOD] = $name;
        if(function_exists($tag[Tag::RUN][Tag::METHOD])){
            return $name($tag, $parser);
        } else {
            var_dump($url);
            Throw new Exception(ucfirst(Tag::RUN) . ' "' . $tag[Tag::RUN][Tag::NAME] . '" not found on line: ' . $tag['line']  . ' column: ' . $tag['column'] . ' in ' .  $parser->data('priya.module.parser.document.url'));
        }
    }

    public static function replace($tag=array(), $attribute='', $parser=null){
        $explode = explode($tag[Tag::TAG], $tag[Tag::STRING], 2);
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
            $tag[Tag::STRING] = implode('', $explode);
            if(empty($tag[Tag::STRING])){
                //have parameters or similar...
                $tag[Tag::STRING] = $tag[$attribute];
            }
        } else {
            $tag[Tag::STRING] = implode($tag[$attribute], $explode);
        }
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $tag = Assign::select($tag, $parser);
        $tag = Assign::remove($tag, Tag::METHOD, $parser);
        $tag = Cast::find($tag, Tag::METHOD, $parser);
        $tag = Exclamation::find($tag, Tag::METHOD, $parser);
        $tag = Method::clean($tag, Tag::METHOD, $parser);
        if($parser->data('priya.module.parser.priya') === true){
            var_dump($tag);
//             die;
        }
        $method = '(';
        $explode = explode($method, $tag[Tag::METHOD], 2);
        if(!isset($explode[1])){
            return $string;
        }
        $before = $parser->explode_multi(array('\'', '"'), $explode[0], 2);
        if(isset($before[1])){
            return $string;
        }
        //have method...

        $tag[Tag::METHOD] = ltrim($explode[0], '{');
        $explode = explode('})', strrev($explode[1]), 2);
        $tag[Tag::PARAMETER] = strrev($explode[1]);
        $tag[Tag::PARAMETER] = Parameter::find($tag[Tag::PARAMETER], $parser);
        $tag = Parameter::execute($tag, Tag::EXECUTE, $parser);
        //execute to constant
        $tag[Tag::STRING] = $string;
        $tag = Method::execute($tag, $parser);
        $tag = Exclamation::exectute($tag, Tag::EXECUTE, $parser);
        $tag = Cast::execute($tag, Tag::EXECUTE, $parser);
        $tag = Assign::execute($tag, Tag::EXECUTE, $parser);
        $tag = Method::replace($tag, Tag::EXECUTE, $parser);
        return $tag[Tag::STRING];
    }

    /**
     * rename to get
     * @param string $string
     * @param unknown $parser
     * @return boolean
     */
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