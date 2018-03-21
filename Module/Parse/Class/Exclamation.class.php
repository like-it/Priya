<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;

class Exclamation extends Core {

    public static function find($tag='', $attribute='', $parser=null){
        if(strpos($tag[$attribute], '!') === false){
            $tag['exclamation'] = 0;
            return $tag;
        }
        $explode = explode('(', $tag[$attribute], 2);
        $explode[0] = str_replace('!', '', $explode[0], $exclamation);
        $tag['exclamation'] = $exclamation ? $exclamation : 0;
        $tag[$attribute] = implode('(', $explode);
        return $tag;
    }

    public static function exectute($tag=array(), $parser=null){
        if($tag['exclamation'] == 0){
            return $tag;
        }
        if($tag['exclamation'] % 2 == 0){
            $tag['execute'] = !! $tag['execute'];
        } else {
            $tag['execute'] = ! $tag['execute'];
        }
        return $tag;
    }
}