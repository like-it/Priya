<?php

namespace Priya\Module\Parse;

//use Priya\Module\Parse\Data;

class Tag extends Core {

    public function __construct($input=null){
        $this->input($input);
    }

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
        $explode = explode('{', $input);
        foreach($explode as $key => $value){
            if($key == 0){
                continue;
            }
            $explode[$key] = '{' . $value;
        }
//         $pattern = '/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        $pattern = '/\{.*\}/';
        $tagged = array();
        foreach($explode as $key => $value){
            preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);
            if(!empty($matches)){
                $match = current(current($matches));
                $tagged[$key][$match] = '';
            }
        }
        return $this->output($tagged);
    }
}