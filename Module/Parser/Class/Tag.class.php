<?php

namespace Priya\Module\Parser;

//use Priya\Module\Parse\Data;

class Tag extends Core {

    public function __construct($input=null, $random=null){
        $this->input($input);
        $this->random($random);
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

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
        $input = Literal::replace($input, $this->random());
        $explode = Tag::explode($input);
//         $pattern = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        $pattern = '/\{.*\}/';
        $tagged = array();
        foreach($explode as $key => $value){
            preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);
            if(!empty($matches)){
                $match = current(current($matches));
                $tagged[][$match] = '';
            }
        }
        return $this->output($tagged);
    }
}