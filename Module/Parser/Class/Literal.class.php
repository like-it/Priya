<?php

namespace Priya\Module\Parser;

class Literal extends Core {
    const OPEN = '{literal}';
    const CLOSE = '{/literal}';

    public static function get($value=''){
        $explode = explode(Literal::OPEN, $value, 2);
        if(count($explode) == 2){
            $temp = explode(Literal::CLOSE, $explode[1], 2);
            if(count($temp) ==2){
                return $temp[0];
            }
        }
        return '';
    }

    public static function remove($value=''){
        return str_replace(
            array(
                Literal::OPEN,
                Literal::CLOSE
            ),
            '',
            $value
        );
    }

    public static function restore($value='', $random=''){
        $search = array(
                '[' . $random .'][literal]',
                '[' . $random .'][/literal]',
                '[' . $random .'][curly_open]',
                '[' . $random .'][curly_close]'
        );
        $replace = array(
                Literal::OPEN,
                Literal::CLOSE,
                '{',
                '}'
        );
        return str_replace($search, $replace, $value);
    }

    public static function replace($value='', $random=''){
        $literal = Literal::get($value);
        while($literal != ''){
            $literal = Literal::get($value);
            $search = Literal::OPEN . $literal . Literal::CLOSE;
            $literal= str_replace(array('{', '}'), array(
                    '[' . $random . '][curly_open]',
                    '[' . $random . '][curly_close]'

            ), $literal);
            $replace = '[' . $random . '][literal]' . $literal . '[' . $random .'][/literal]';
            $value = str_replace($search, $replace, $value);
        }
        return $value;
    }
}