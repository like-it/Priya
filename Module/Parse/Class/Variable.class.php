<?php

namespace Priya\Module\Parse;

class Variable extends Core {

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
    }

    public static function type($mixed=null){
        if(is_int($mixed)){
            return Token::TYPE_INT;
        }
        elseif(is_float($mixed)){
            return Token::TYPE_FLOAT;
        }
        elseif(is_bool($mixed)){
            return Token::TYPE_BOOLEAN;
        }
        elseif(is_array($mixed)){
            return Token::TYPE_ARRAY;
        }
        elseif(is_object($mixed)){
            return Token::TYPE_OBJECT;
        }
        elseif(is_object($mixed)){
            return Token::TYPE_OBJECT;
        }
        elseif(is_string($mixed)){
            return Token::TYPE_STRING;
        } else {
            return Token::TYPE_NULL;
        }
    }

    public function replace($input=null){
        debug($input);
        debug($this->data());
        die;
    }
}