<?php

namespace Priya\Module\Parse;

use Priya\Module\Data;

class Core extends Data {
    protected $random;

    public function random($random=null){
        if($random !== null){
            if($random == 'create'){
                $this->setRandom(Core::random_create());
            } else {
                $this->setRandom($random);
            }
        }
        return $this->getRandom();
    }

    public static function random_create(){
        return rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999);
    }

    private function setRandom($random=''){
        $this->random = $random;
    }

    private function getRandom(){
        return $this->random;
    }

    public static function tag_lower($string='', $tag=''){
        if(empty($tag)){
            return $string;
        }
        $string= str_ireplace('{' . $tag, '{' . $tag, $string);
        $string= str_ireplace('{/' . $tag . '}', '{/' . $tag . '}', $string);
        return $string;
    }

}