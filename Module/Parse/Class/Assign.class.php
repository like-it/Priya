<?php

namespace Priya\Module\Parse;

//use Priya\Module\Parse\Data;

class Assign extends Data {

    public function __construct($data=null){
        $this->data($data);
    }

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
        if(empty($input)){
            return;
        }
        $tag = key($input);

        $explode = explode('=', trim($tag, '{}'), 2);

        if(!empty($explode[0]) && substr($explode[0], 1 == '$') && count($explode) == 2){
            $attribute = substr(rtrim($explode[0]), 1);
            $value = trim($explode[1], ' ');
            $value = trim($value, '\'"');
            $this->data($attribute, $value);
        }
    }
}