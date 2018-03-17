<?php

namespace Priya\Module\Parser;

class Tag extends Core {

    const FILTER_CONTROL = array(
        'capture.append' =>
            array(
                'capture',
                'capture.append'
        ),
        'for.each' => array(
            'for.each'
        )
        //might add if too... (in the future, new version)
    );

    public static function control($list=array()){
        //add source line nr to list tag
        $close_tags = array();
        foreach($list as $nr => $value){
            $tag = strtolower(key($value));
            if(substr($tag, 1, 1) == '$'){
                continue;
            }
            if(!empty($close_tags)){
                if(in_array($tag, $close_tags)){
                    $close_tags = array();
                } else {
                    unset($list[$nr]);
                }
            } else {
                foreach(Tag::FILTER_CONTROL as $open_tag => $filter){
                    $match = substr($tag, 1, strlen($open_tag));
                    if($match == $open_tag){
                        $close_tags = $filter;
                    }
                }
            }
        }
        return $list;
    }

    public static function explode($input='', $depth=0){
        $explode = explode('{', $input);
        $open_count = 0;
        $close_count = 0;
        $record = array();
        $list = array();
        foreach ($explode as $nr => $value){
            if($nr == 0){
                $open_count++;
                continue;
            }
            $temp = explode('}', $value);
            if(count($temp) > 1){
                $close_count += count($temp) - 1;
            }
            $record[] = '{';
            if(count($temp) > 1){
                foreach($temp as $temp_nr => $temp_value){
                    $record[] = $temp_value;
                    if($temp_nr == count($temp)-1){
                        //dont add }
                    } else {
                        $record[] = '}';
                    }
                }
            } else {
                $record[] = $temp[0];
            }
            if($open_count == $close_count && $open_count > 0){
                $list[] = implode('', $record);
                $open_count = 0;
                $close_count = 0;
                $record = array();
            }
            $open_count++;
        }
        return $list;
    }

    public static function find($input=null){
        $tagged = array();
        if(!is_string($input)){
            return $tagged;
        }
//         echo __LINE__ . '::' . __FILE__ . $input;
        $explode = Tag::explode($input);
//         $pattern = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        $pattern = '/\{.*\}/';
        foreach($explode as $key => $value){
            preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);
            if(!empty($matches)){
                $match = current(current($matches));
                $tagged[][$match] = '';
            }
        }
        return $tagged;
    }
}