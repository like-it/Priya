<?php

namespace Priya\Module\Parser;

use Exception;
class Control_Foreach extends Core {

    public static function list($function=array(), $parser=null){
        $result = array();
        if(isset($function['parameter'][0])){
            $attribute = $function['parameter'][0]['variable'];
            if(substr($attribute, 0, 1) == '$'){
                $attribute = substr($attribute, 1);
            }
            $result =  $parser->data($attribute);
        }
        if(
            empty($result) ||
            (
                !is_object($result) &&
                !is_array($result)
            )
        ){
            throw new Exception('invalid array/object in for.each');
        }
        return $result;
    }

    public static function key($function=array()){
        $collect = false;
        $result = null;
        $requirement  = false;
        foreach($function['parameter'] as  $key => $value){
            if($value['type'] == 'as'){
                $collect = true;
                continue;
            }
            if($collect && isset($value['variable'])){
                $result = $value['variable'];
            }
            if($value['type'] == 'double-arrow'){
                $requirement = true;
                break;
            }
        }
        if($collect === false){
            if(
                isset($function['parameter'][2]) &&
                isset($function['parameter'][1]) &&
                isset($function['parameter'][1]['variable'])
            ){
                $result= $function['parameter'][1]['variable'];
                $requirement = true;
            }
        }
        if($requirement){
            if(substr($result, 0, 1) == '$'){
                $result= substr($result, 1);
            }
            return $result;
        }
    }

    public static function record($function=array()){
        $collect = false;
        $result = null;
        foreach($function['parameter'] as  $key => $value){
            if($value['type'] == 'as'){
                $collect = true;
                continue;
            }
            if($collect && isset($value['variable'])){
                $result = $value['variable'];
            }
        }
        if($collect === false){
            if(
                isset($function['parameter'][2]) &&
                isset($function['parameter'][2]['variable'])
            ){
                $result= $function['parameter'][2]['variable'];

            }
            elseif(
                isset($function['parameter'][1]) &&
                isset($function['parameter'][1]['variable'])
            ){
                $result= $function['parameter'][1]['variable'];
            }
        }
        if(substr($result, 0, 1) == '$'){
            $result= substr($result, 1);
        }
        return $result;
    }

    public static function lower($string=''){
        $string= str_ireplace('{for.each', '{for.each', $string);
        $string= str_ireplace('{/for.each}', '{/for.each}', $string);
        return $string;
    }

    public static function literal($value='', $parser=null){
        $search = array(
            '[' . $parser->random() . '][literal]',
            '[' . $parser->random() . '][/literal]',
            '[' . $parser->random(). '][curly_open]',
            '[' . $parser->random(). '][curly_close]',
        );
        $replace = array(
            '[literal][for.each:' .  $parser->random() .']',
            '[/literal][for.each:' .  $parser->random() .']',
            '[curly_open][for.each:' .  $parser->random() .']',
            '[curly_close][for.each:' .  $parser->random() .']'
        );
        return str_replace($search, $replace, $value);
    }

    public static function replace($value='', $parser=null){
        $search = array(
                '[literal][for.each:' .  $parser->random() .']',
                '[/literal][for.each:' .  $parser->random() .']',
                '[curly_open][for.each:' .  $parser->random() .']',
                '[curly_close][for.each:' .  $parser->random() .']'
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
        $explode = explode('{for.each(', $value, 2);
        $string = '';
        if(isset($explode[1])){
            $foreach = explode('}hcae.rof/{', strrev($explode[1]), 2);
            $foreach[0] = strrev($foreach[0]);
            if(isset($foreach[1])){
                $foreach[1] = strrev($foreach[1]);
            } else {
                throw new Exception('Control_Foreach::content:Missing {/for.each} in {for.each} tag');
            }
            $string = $foreach[1]; //not 0 (reverse)
            return $string;
        } else {
            return false;
        }
    }

    public static function finalize($value='', $function=array()){
        $search = '{for.each(' . $value . '{/for.each}';
        $explode = explode($search, $function['string'], 2);
        if(!isset($explode[1])){
            throw new Exception('Control_Foreach::finalize:Cannot finalize for.each, cannot find origin');
        }
        return implode($function['execute'], $explode);
    }

    public static function find($string='', $list=array(), $key=null, $record=null, $parser=null){
        $tmp = explode(')}',$string, 2);
        //might add randomizer to be in scope of the foreach only...
        $internal = '';

        foreach($list as $internal_key => $internal_value){
            $parser->data($key, $internal_key);
            $parser->data($record, $internal_value);
            $compile = $parser->compile($tmp[1]);
            $internal .= $compile;
        }
        return $internal;
    }

    public static function get($string=''){
        $depth = 0;
        $list = array();
        $record = array();
        $explode = explode('{', $string);
        $tag_start = 'for.each';
        $tag_end = '/' . $tag_start;
        $collect = false;
        $value = '';
        foreach($explode as $nr => $row){
            if(substr($row,0, strlen($tag_start)) == $tag_start){
                $depth++;
                $record['depth'] = $depth;
                $collect = true;
            }
            elseif(substr($row,0, strlen($tag_end)) == $tag_end){
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
            return $string;
        }
    }
}