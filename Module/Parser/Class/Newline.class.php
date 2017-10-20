<?php

namespace Priya\Module\Parser;

//use Priya\Module\Parse\Data;

class Newline extends Core {

    public static function replace($input='',  $random=''){
        $input = str_replace("\r", '[' . $random . '][return]', $input);
        $input = str_replace("\n", '[' . $random . '][newline]', $input);
        return $input;
    }

    public static function restore($input='',  $random=''){
        $input = str_replace('[' . $random . '][return]', "\r", $input);
        $input = str_replace( '[' . $random . '][newline]', "\n", $input);
        return $input;
    }

}