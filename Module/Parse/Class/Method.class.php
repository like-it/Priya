<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;

class Method extends Core {    

    public static function execute(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){
        return Token::method_execute($parse, $method, $token, $keep, $tag_remove);
    }
}