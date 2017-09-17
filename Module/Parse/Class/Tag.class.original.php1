<?php

namespace Priya\Module\Parse;

//use Priya\Module\Parse\Data;

class Tag extends Core {

    public function __construct($input=null){
        $this->input($input);
    }

    public function explode($input='', $depth=1){
        $explode = explode('{', $input);
        foreach($explode as $key => $value){
            if($key == 0){
                continue;
            }
            if(stristr($value, '}') !== false){
                $explode[$key] = '{' . $value;
            } else {
                $depth++;
                $index = $key+1;
                $record = array('{' .$value);
                while($depth > 1){
                    if(isset($explode[$index])){
                        $count = substr_count($explode[$index], '}');
                        $record[] = '{' . $explode[$index];
                        unset($explode[$index]);
                    } else {
                        debug($index);
                        debug($explode);
                        debug($count);
                        debug($depth);
                        debug('error');
                        die;
                    }

                    if($count > 0){
                        $depth -= $count;
                    }
                    if($index >= count($explode)){
                        break;
                    } else {
                        $index++;
                    }
                }
                $explode[$key] = implode('', $record);
            }
        }
        return $explode;
    }

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
        $explode = $this->explode($input);
        debug($explode);
        die;
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