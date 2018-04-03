<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Set extends Core {
    const MAX = 1024;

    //rename to open & close (without tag)
    const OPEN= '(';
    const CLOSE = ')';

    public static function find($tag='', $attribute='', $parser=null){
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }

    public static function has($string='', $parser=null){
        $split = str_split($string, 1);

        $before = '';
        $found = false;
        foreach ($split as $char){
            $before .= $char; //method::is needs the "(" char
            if(
                $char == '(' &&
                Method::is($before) === false
            ){
                $found = true;
            }
            if(
                $found &&
                $char == ')'
            ){
                return true;
            }
        }
        return false;
    }

    /**
     * Methods should be parsed before entering set statements
     * @param string $string
     * @param unknown $parser
     * @return boolean
     */
    public static function statement($string='', $parser=null){
        $split = str_split($string, 1);
        $set_depth = 0;
        $max_depth = 0;
        $counter = 0;
        $before = '';
        $previous = '';
        $set = array();
        $parse = false;
        $variable = false;
//         var_dump($string);
        foreach ($split as $char){
            if($char == '"' && $parse === true){
                $set[$set_depth][$counter][] = $char;
                $parse = false;
//                 $counter++;
                continue;
            }
            if($char == '"' && $parse === false){
                $set[$set_depth][$counter][] = $char;
                $parse = true;
                continue;
            }
            if($parse === true){
                $set[$set_depth][$counter][] = $char;
                continue;
            }
            /*
            if(
                $parse === false &&
                $variable === false &&
                $char == '$'
            ){
                $set[$set_depth][$counter][] = $char;
                $variable = true;
                continue;
            }
            if(
                $parse === false &&
                $variable === true
            ){
                $set[$set_depth][$counter][] = $char;
                continue;
            }
            */
            if(
                $char == '('
            ){
                if(
                    !in_array(
                        $previous,
                        array(
                            '+',
                            '-',
                            '/',
                            '*',
                            '&',
                            '|',
                            '%',
                            '<',
                            '>',
                            '^',
                            '!',
                            '=',
                            ' ',
                            '(',
                            '',
                            "\t",
                            "\r",
                            "\n"
                        )
                    )
                ){
                    var_dump($string);
                    throw new Exception('Possible Method found where not allowed...');
                }
                $set_depth++;
                if($set_depth > $max_depth){
                    $max_depth = $set_depth;
                }
                $counter++;
//                 $set[$set_depth][$counter][] = $char;
            }
            elseif(
                $char == ')'
            ){
//                 $set[$set_depth][$counter][] = $char;
                $set_depth--;
            } else {
                $set[$set_depth][$counter][] = $char;
            }
            $previous = $char;
        }
        if($max_depth == 0){
            return false;
        }
        $result = reset($set[$max_depth]);
//         var_dump($set);
//         var_dump($result);
        $result = Operator::set($result, $parser);
        return $result;
    }

    public static function get($string='', $parser=null){
        var_dump($string);
        /**
         * get highest set
         */
    }
}