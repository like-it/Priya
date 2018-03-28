<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;

class Operator extends Core {
    const MAX = 1024;

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

    const LIST = array(
        Operator::ARITHMIC_DIVIDE,
        Operator::ARITHMIC_MIN,
        Operator::ARITHMIC_PLUS,
        Operator::ARITHMIC_MULTIPLY,
        Operator::ARITHMIC_EXPONENTIAL,
        Operator::ARITHMIC_MODULO,
        Operator::BITWISE_AND,
        Operator::BITWISE_OR,
        Operator::BITWISE_NOT,
        Operator::BITWISE_XOR,
        Operator::BITWISE_SHIFT_LEFT,
        Operator::BITWISE_SHIFT_RIGHT
    );

    public static function find($tag='', $string='', $parser=null){
        return $tag;
    }

    public static function execute($node=array(), $parser=null){
        $result = null;
        switch($node['operator']){
            case '+' :
                $result = $node['left'] + $node['right'];
            break;
            case '-' :
                $result = $node['left'] - $node['right'];
            break;
            case '/' :
                $result = $node['left'] / $node['right'];
            break;
            case '*' :
                $result = $node['left'] * $node['right'];
            break;
            case '%' :
                $result = $node['left'] % $node['right'];
            break;
            case '**' :
                $result = $node['left'] ** $node['right'];
            break;
        }
        array_unshift($node['statement'], $result);
        return $node['statement'];
    }

    public static function has($statement=array(), $parser=null){
        foreach($statement as $part){
            if(in_array($part, Operator::LIST)){
                return true;
            }
        }
        return false;
    }

    public static function statement($statement=array(), $parser=null){
        $before = true;
        foreach($statement as $nr => $part){
            if(in_array($part, Operator::LIST)){
                if($before === false){
                    break; //have another operator but first solve this one...
                } else {
                    $before = false;
                    $operator = $part;
                    unset($statement[$nr]);
                    continue;
                }

            }
            if($before === true){
                $left = $part;
                unset($statement[$nr]);
            } else {
                $right = $part;
                unset($statement[$nr]);
            }
        }
        $node = array();
        $node['left'] = $left;
        $node['operator'] = $operator;
        $node['right'] = $right;
        $node['statement'] = $statement;
        $statement = Operator::execute($node, $parser);
        return $statement;
    }
}