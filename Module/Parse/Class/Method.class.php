<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;

class Method extends Core {
    CONST STATUS = 'is_variable';


    public static function execute(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true, $count=0){
        return Token::method_execute($parse, $method, $token, $keep, $tag_remove, $count);
    }

    public static function find(Parse $parse, $token=[], $tag_open_nr=null, $tag_close_nr=null, $keep=false){

    }
}