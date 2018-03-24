<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;

class Set extends Core {
    //rename to open & close (without tag)
    const OPEN= '(';
    const CLOSE = ')';

    public static function find($tag='', $attribute='', $parser=null){
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}