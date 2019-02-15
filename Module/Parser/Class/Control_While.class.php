<?php

namespace Priya\Module\Parser;

use Exception;
class Control_while extends Core {

    public static function lower($string=''){
        $string= str_ireplace('{while', '{while', $string);
        $string= str_ireplace('{/while}', '{/while}', $string);
        return $string;
    }

    public static function get($string=''){
        $depth = 0;
        $list = array();
        $record = array();
        $explode = explode('{', $string);
        $tag_start = 'while';
        $tag_end = '/' . $tag_start;
        $tag_start_length = strlen($tag_start);
        $tag_end_length = strlen($tag_end);
        $collect = false;
        $value = '';
        foreach($explode as $nr => $row){
            if(substr($row, 0, $tag_start_length) == $tag_start){
                $depth++;
                $record['depth'] = $depth;
                $collect = true;
            }
            elseif(substr($row, 0, $tag_end_length) == $tag_end){
                $record['depth'] = $depth;
                $depth--;
                if($depth == 0){
                    $value .= '{' . $row;
                    break;
                }
            }
            if($collect){
                $value .= '{' . $row;
            }
        }
        if($collect){
            return $value;
        } else {
            return '';
        }
    }

    public static function literal($value='', $parser=null){
        $search = array(
            '[' . $parser->random() . '][literal]',
            '[' . $parser->random() . '][/literal]',
            '[' . $parser->random(). '][curly_open]',
            '[' . $parser->random(). '][curly_close]',
        );
        $replace = array(
            '[literal][while:' .  $parser->random() .']',
            '[/literal][while:' .  $parser->random() .']',
            '[curly_open][while:' .  $parser->random() .']',
            '[curly_close][while:' .  $parser->random() .']'
        );
        return str_replace($search, $replace, $value);
    }

    public static function replace($value='', $parser=null){
        $search = array(
            '[literal][while:' .  $parser->random() .']',
            '[/literal][while:' .  $parser->random() .']',
            '[curly_open][while:' .  $parser->random() .']',
            '[curly_close][while:' .  $parser->random() .']'
        );
        $replace = array(
            '[' . $parser->random() . '][literal]',
            '[' . $parser->random() . '][/literal]',
            '[' . $parser->random(). '][curly_open]',
            '[' . $parser->random(). '][curly_close]',
        );
        return str_replace($search, $replace, $value);
    }

    public static function content($value=''){
        $result = [];
        $explode = explode(')}', $value, 2);
        $result['statement'] = $explode[0] . ')}';
        $reverse = strrev($explode[1]);
        $explode = explode('}elihw/{', $reverse, 2);
        if(!isset($explode[1])){
            throw new Exception('Cannot find {/while}');
        }
        $result['string'] = strrev($explode[1]);
        $result['match'] = $result['string'];
        $explode = explode('[' . $parser->random() .'][newline]', $result['string'], 2);
        $explode[0] = trim($explode[0]);
        if(empty($explode[0]) && isset($explode[1])){
            $result['string'] = $explode[1];
        }
        return $result;
    }

    public static function find($function=array(), $string='', $argumentList=array(), $parser=null){
        $statement = '{' . array_pop($argumentList) . '}';
        $while = $parser->compile($statement);
        $parser->data('parser.while.compile', $while);
        $content = '';
        $explode = explode($function['key'], $function['string'], 2);
        $before = Token::LITERAL_OPEN .$explode[0] . Token::LITERAL_CLOSE;
        $function['string'] = $function['key'] . $explode[1];
        while($while){
            if(!empty($content)){
                $before .= Token::LITERAL_OPEN . $content . Token::LITERAL_CLOSE;
                $content = '';
            }
            $compile = $parser->compile($before . $string);
            $before = '';
            $content .= $compile;
            if($parser->data('priya.parser.break.amount')){
                $amount = $parser->data('priya.parser.break.amount');
                $amount--;
                if($amount < 1){
                    $parser->data('delete', 'priya.parser.break.amount');
                } else {
                    $parser->data('priya.parser.break.amount', $amount);
                }
                break;
            }
            $while = $parser->compile($statement);
        }
        if(!empty($before)){
            $content .= Token::literal_remove($before);
        }
        $function['execute'] =  Token::literal_restore($content, $parser->random());
        return $function;
    }

    public static function finalize($content=array(), $function=array()){
        $search = $content['statement'] . $content['match'] . '{/while}';
        $explode = explode($search, $function['string'], 2);
        if(!isset($explode[1])){
            throw new Exception('Control_While::finalize:Cannot finalize while, cannot find origin');
        }
        return implode($function['execute'], $explode);
    }
}