<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parser\Operator;

class Comparison extends Core {
    const PLUS = '+';
    const MIN = '-';
    const MULTIPLY = '*';
    const DIVIDE = '/';
    const MODULO = '%';
    const IS = '=';

    const COMPARE_IS = '==';
    const COMPARE_PLUS = '+';
    const COMPARE_IS_EQUAL = '===';


    const COMPARE_ARRAY = '';
    const COMPARE = '';
    const ARITHMETIC = '';
    const BITWISE = '';

    public static function find($tag='', $attribute='', $parser=null){
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}