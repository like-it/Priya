<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;

class Exclamation extends Core {
    const SIGN = '!';
    const EMPTY = '';

    public static function find($tag='', $attribute='', $parser=null){
        if(strpos($tag[$attribute], Exclamation::SIGN) === false){
            $tag[Tag::EXCLAMATION] = 0;
            return $tag;
        }
        $explode = explode(Method::OPEN, $tag[$attribute], 2);
        $explode[0] = str_replace(Exclamation::SIGN, Exclamation::EMPTY, $explode[0], $exclamation);
        $tag[Tag::EXCLAMATION] = $exclamation ? $exclamation : 0;
        $tag[$attribute] = implode(Method::OPEN, $explode);
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        if($tag[Tag::EXCLAMATION] == 0){
            return $tag;
        }
        if(!isset($tag[$attribute])){
            $tag[$attribute] = null;
        }
        if($tag[Tag::EXCLAMATION] % 2 == 0){
            $tag[$attribute] = !! $tag[$attribute];
        } else {
            $tag[$attribute] = ! $tag[$attribute];
        }
        return $tag;
    }
}