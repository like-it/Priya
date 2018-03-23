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

	const COMPARE_IS = Operator::IS . Operator::IS;
	const COMPARE_PLUS = Operator::PLUS;
	const COMPARE_IS_EQUAL = Operator::IS . Operator::IS . Operator::IS;


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