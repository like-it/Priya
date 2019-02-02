<?php

namespace Priya\Module\Parser;

class Literal extends Core {
    const OPEN = '{literal}';
    const CLOSE = '{/literal}';

    /**
     *
     * @param string $value
     * @return string
     */
    public static function get($value=''){
        if(!is_string($value)){
            return '';
        }
        $explode = explode(Literal::OPEN, $value, 2);
        if(count($explode) == 2){
            $temp = explode(Literal::CLOSE, $explode[1], 2);
            if(count($temp) ==2){
                return $temp[0];
            }
        }
        return '';
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function remove($value=''){
        if(is_array($value)){
            foreach($value as $key => $value_value){
                $value[$key] = Literal::remove($value_value);
            }
            return $value;
        }
        elseif(is_object($value)){
            foreach($value as $key => $value_value){
                $value->{$key} = Literal::remove($value_value);
            }
            return $value;
        }
        if(!is_string($value)){
            return $value;
        }
        return str_replace(
            array(
                Literal::OPEN,
                Literal::CLOSE
            ),
            '',
            $value
        );
    }

    /**
     *
     * @param string $value
     * @param string $random
     * @return string
     */
    public static function restore($value='', $random=''){
        $search = array(
                '[' . $random . '][literal]',
                '[' . $random . '][/literal]',
                '[' . $random . '][curly_open]',
                '[' . $random . '][curly_close]',
        );
        $replace = array(
                Literal::OPEN,
                Literal::CLOSE,
                '{',
                '}',
        );
        return str_replace($search, $replace, $value);
    }

    /**
     *
     * @param string $value
     * @param string $random
     * @return string
     */
    public static function replace($value='', $random=''){
        $literal = Literal::get($value);
        while($literal != ''){
            $literal = Literal::get($value);
            $search = Literal::OPEN . $literal . Literal::CLOSE;
            $literal = str_replace(
                array(
                    '{',
                    '}',
                ),
                array(
                    '[' . $random . '][curly_open]',
                    '[' . $random . '][curly_close]',
                ),
                $literal
            );
            $replace = '[' . $random . '][literal]' . $literal . '[' . $random .'][/literal]';
            $value = str_replace($search, $replace, $value);
        }
        return $value;
    }

    /**
     * adds extra literal tags around {}
     * @param string $value
     */
    public static function extra($value=''){
        if(is_object($value)){
            foreach ($value as $key => $val){
                $value->{$key} = Literal::extra($val);
            }
            return $value;
        }
        elseif(is_array($value)){
            foreach ($value as $key => $val){
                $value[$key] = Literal::extra($val);
            }
            return $value;
        } else {
            $search = array(
                    '{' . "\n",
                    '{' . "\r",
                    '{' . "\r\n",
                    '{' . ' ',
                    '{}',
                    "\n" . '}',
                    "\r" . '}',
                    "\r\n" . '}',
                    ' ' . '}',
            );
            $replace = array(
                    Literal::OPEN . '{' . Literal::CLOSE . "\n",
                    Literal::OPEN . '{' . Literal::CLOSE . "\r",
                    Literal::OPEN . '{' . Literal::CLOSE . "\r\n",
                    Literal::OPEN . '{' . Literal::CLOSE . ' ',
                    Literal::OPEN .'{}' . Literal::CLOSE,
                    "\n" . Literal::OPEN . '}'. Literal::CLOSE,
                    "\r" . Literal::OPEN . '}'. Literal::CLOSE,
                    "\r\n" . Literal::OPEN . '}'. Literal::CLOSE,
                    ' ' . Literal::OPEN .'}'. Literal::CLOSE,
            );
            return str_replace($search, $replace, $value);
        }
    }
}
