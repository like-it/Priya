<?php

namespace Priya\Module\Parser;

class Newline extends Core {

    public static function replace($input='',  $random=''){
        if(is_object($input)){
            foreach($input as $key => $value){
                $input->{$key} = Newline::replace($value, $random);
            }
            return $input;
        }
        elseif(is_array($input)){
            foreach($input as $key => $value){
                $input[$key] = Newline::replace($value, $random);
            }
            return $input;
        } else {
            $input = str_replace("\r", '[' . $random . '][return]', $input);
            $input = str_replace("\n", '[' . $random . '][newline]', $input);
            return $input;
        }

    }

    public static function restore($input='',  $random=''){
        if(is_object($input)){
            foreach($input as $key => $value){
                $input->{$key} = Newline::restore($value, $random);
            }
            return $input;
        }
        elseif(is_array($input)){
            foreach($input as $key => $value){
                $input[$key] = Newline::restore($value, $random);
            }
            return $input;
        } else {
            $input = str_replace('[' . $random . '][return]', "\r", $input);
            $input = str_replace( '[' . $random . '][newline]', "\n", $input);
            return $input;
        }
    }

}