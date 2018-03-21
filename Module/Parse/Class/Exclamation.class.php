<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;

class Exclamation extends Core {

    public static function find($tag='', $attribute='', $parser=null){
        if(strpos($tag[$attribute], '!') === false){
            $tag[Tag::ATTRIBUTE_EXCLAMATION] = 0;
            return $tag;
        }
        $explode = explode('(', $tag[$attribute], 2);
        $explode[0] = str_replace('!', '', $explode[0], $exclamation);
        $tag[Tag::ATTRIBUTE_EXCLAMATION] = $exclamation ? $exclamation : 0;
        $tag[$attribute] = implode('(', $explode);
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        if($tag[Tag::ATTRIBUTE_EXCLAMATION] == 0){
            return $tag;
        }
        if(!isset($tag[$attribute])){
            $tag[$attribute] = null;
        }
        if($tag[Tag::ATTRIBUTE_EXCLAMATION] % 2 == 0){
            $tag[$attribute] = !! $tag[$attribute];
        } else {
            $tag[$attribute] = ! $tag[$attribute];
        }
        return $tag;
    }
}