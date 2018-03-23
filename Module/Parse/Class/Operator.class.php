<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parser\Operator;

class Operator extends Core {
	const ASSIGN_PLUS = '+';
	const ASSIGN_MIN = '-';
	const ASSIGN_MULTIPLY = '*';
	const ASSIGN_DIVIDE = '/';
	const ASSIGN_NOT = '!';
	const ASSIGN_ADD = '.';
	const ASSIGN_EQUAL = '=';

	const ARITHMIC_PLUS = '+';
	const ARITHMIC_MIN = '-';
	const ARITHMIC_MULTIPLY = '*';
	const ARITHMIC_EXPONENTIAL = '**';
	const ARITHMIC_DIVIDE = '/';
	const ARITHMIC_MODULO = '%';

	const BITWISE_OR = '|';
	const BITWISE_AND = '&';
	const BITWISE_NOT = '~';
	const BITWISE_XOR = '^';
	const BITWISE_SHIFT_LEFT = '<<';
	const BITWISE_SHIFT_RIGHT = '>>';



    public static function find($tag='', $string='', $parser=null){
        return $tag;
    }

    public static function exectute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}