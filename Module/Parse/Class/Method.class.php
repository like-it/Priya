<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Method extends Core {
    const DOT = '.';
    const SLASH_FORWARD = '/';
    const SLASH_BACKWARD = '\\';
    const QUOTE_SINGLE = '\'';
    const QUOTE_DOUBLE = '"';
    const EMPTY = '';
    const UNDERSCORE = '_';
    const OPEN = '(';
    const CLOSE = ')';

    const IMPORT =  __DIR__ . '/../Function/Function.Import.php';
    const EXT_PHP = '.php';

    const NOT_IN = array(
        Method::DOT . Method::DOT,
        Method::SLASH_FORWARD . Method::SLASH_FORWARD,
        Method::SLASH_BACKWARD . Method::SLASH_BACKWARD
    );

    const NOT_BEFORE = array(
        Method::QUOTE_SINGLE,
        Method::QUOTE_DOUBLE
    );

    public static function clean($tag=array(), $attribute='', $parser=null){
        $tag[$attribute] = trim($tag[$attribute], Parse::SPACE);
        return $tag;
    }

    public static function execute($tag=array(), $parser=null){
        if(!isset($tag[Tag::METHOD])){
            var_dump($tag);
            var_dump(debug_backtrace(true));
            die;
        }
        $name = str_replace(
            Method::NOT_IN,
            Method::EMPTY,
            ucfirst(strtolower($tag[Tag::METHOD]))
        );
        $tag[Tag::RUN][Tag::NAME] = $name;
        $require = $parser->data('priya.module.parser.require');

        if(!in_array(Method::IMPORT, $require)){
            require_once Method::IMPORT;
        }
        $import = $parser->data('priya.module.parser.import');
        foreach($import as $url){
            $url = $url . ucfirst(Tag::RUN) . Method::DOT . $name . Method::EXT_PHP;
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
        $name =
            Tag::RUN .
            Method::UNDERSCORE .
            str_replace(
                Method::DOT,
                Method::UNDERSCORE,
                strtolower($name)
            );
        $tag[Tag::RUN][Tag::METHOD] = $name;
        if(function_exists($tag[Tag::RUN][Tag::METHOD])){
            return $name($tag, $parser);
        } else {
            var_dump($url);
            Throw new Exception(ucfirst(Tag::RUN) . ' "' . $tag[Tag::RUN][Tag::NAME] . '" not found on line: ' . $tag['line']  . ' column: ' . $tag['column'] . ' in ' .  $parser->data('priya.module.parser.document.url'));
        }
    }

    public static function replace($tag=array(), $attribute='', $parser=null){
        if(empty($tag[Tag::TAG])){
            var_dump($tag);
            var_dump($attribute);
            var_dump(debug_backtrace(true));
            var_dump('found error');
            die;
        }
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
            $tag[Tag::STRING] = implode(Parse::STRING_EMPTY, $explode);
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
        if($parser->data('priya.debug') === true && $tag['tag'] !== '{$priya.debug = true}'){
//             var_dump($tag);
//             die;
        }
        $tag = Assign::select($tag, $parser);
        //might need to delete $tag[Tag::VALUE] (older than 15-05-2018 delete this comment)
        $tag = Assign::remove($tag, Tag::METHOD, $parser);
        $tag = Cast::find($tag, Tag::METHOD, $parser);
        $tag = Exclamation::find($tag, Tag::METHOD, $parser);
        $tag = Method::clean($tag, Tag::METHOD, $parser);
        $method = METHOD::OPEN;
        $explode = explode($method, $tag[Tag::METHOD], 2);
        if(!isset($explode[1])){
            return $string;
        }
        $before = $parser->explode_multi(Method::NOT_BEFORE, $explode[0], 2);
        if(isset($before[1])){
            return $string;
        }
        //have method...
        if($parser->data('priya.debug') === true){
//             var_dump($explode);
//             die;
        }
        $tag[Tag::METHOD] = ltrim($explode[0], Tag::OPEN);
        $explode = explode(Tag::CLOSE . METHOD::CLOSE, strrev($explode[1]), 2);
        if(!isset($explode[1])){
            var_dump($string);
            var_dump($explode);
            die;
        }
        $tag[Tag::PARAMETER] = strrev($explode[1]);
        if($parser->data('priya.debug') === true){
//             var_dump($tag);
        }
//         var_dump($tag[Tag::PARAMETER]);
        $tag[Tag::PARAMETER] = Parameter::find($tag[Tag::PARAMETER], $parser);

        if($parser->data('priya.debug') === true){
//             var_dump($tag);
//             die;
        }
        $tag[Tag::STRING] = $string;
        $original = $tag;
        $tag = Method::execute($tag, $parser);
        $tag = Exclamation::exectute($tag, Tag::EXECUTE, $parser);
        $tag = Cast::execute($tag, Tag::EXECUTE, $parser);
        $tag = Assign::execute($tag, Tag::EXECUTE, $parser);
        if($tag === null){
            var_dump('found');
            die;
        }
        $tag = Method::replace($tag, Tag::EXECUTE, $parser);
        if($parser->data('priya.debug') === true){
//             var_Dump($tag[Tag::STRING]);
        }
        return $tag[Tag::STRING];
    }

    /**
     * rename to get
     * @param string $string
     * @param unknown $parser
     * @return boolean
     */
    public static function is($string='', $parser=null){
        $method = METHOD::OPEN;
        $explode = explode($method, $string, 2);
        if(!isset($explode[1])){
            return false;
        }
        if(empty($explode[0])){
            return  false;
        }
        $char = substr($explode[0], -1, 1);
        if(
            in_array(
                $char,
                array(
                    ' ',
                    "\t",
                    "\n",
                    "\r",
                    '+',
                    '-',
                    '/',
                    '*',
                    '%',
                    '&',
                    '|',
                    '<',
                    '>',
                    '^',
                    '(',
                    '=',
                    '!'
                )
            )
        ){
            return false;
        }
        return true;

        /*

        $before = $parser->explode_multi(Method::NOT_BEFORE, $explode[0], 2);
        if(isset($before[1])){
            return false;
        }
        var_dump($before);
        return true;
        */
    }
}