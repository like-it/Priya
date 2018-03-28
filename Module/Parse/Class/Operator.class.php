<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Exception;

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

    const LOGICAL_AND = 'and';
    const LOGICAL_OR = 'and';
    const LOGICAL_XOR = 'xor';
    const LOGICAL_NOT = '!';
    const LOGICAL_AND_AND = '&&';
    const LOGICAL_OR_OR = '||';

    const EQUALS = '=';
    const EQUALS_EQUALS= '==';
    const EQUALS_EQUALS_EQUALS= '===';

    const NOT = '!';
    const NOT_EQUALS = '!=';
    const NOT_EQUALS_EQUALS= '!==';

    /**
     * the key index is also in use (see Parameter::find)
     */
    const LIST = array(
        Operator::ASSIGN_ADD,
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
        Operator::BITWISE_SHIFT_RIGHT,
        Operator::EQUALS_EQUALS,
        Operator::EQUALS_EQUALS_EQUALS,
        Operator::NOT_EQUALS,
        Operator::NOT_EQUALS_EQUALS,
        Operator::LOGICAL_AND,
        Operator::LOGICAL_AND_AND,
        Operator::LOGICAL_NOT,
        Operator::LOGICAL_OR,
        Operator::LOGICAL_OR_OR,
        Operator::LOGICAL_XOR
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
            case '.' :
                $result = Operator::string($node['left']) . Operator::string($node['right']);
                break;

            case '===' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] === $node['right'];
                break;

            default :
                throw new Exception('Unknown operator (' . $node['operator'] . ')');
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
        if($parser->data('priya.debug2')){
//             var_dump($statement);
        }

        return false;
    }

    public static function string($string=''){
        if(is_object($string)){
            var_dump($string);
            var_dump(debug_backtrace(true));
            return $string;
//             die;
        }
        if(substr($string, 0, 1) == '\'' && substr($string, -1) == '\''){
            return substr($string, 1, -1);
        }
        elseif(substr($string, 0, 1) == '"' && substr($string, -1) == '"'){
            return substr($string, 1, -1);
        }
        return $string;
    }

    public static function statement($statement=array(), $parser=null){
        if(empty($statement)){
            return $statement;
        }
//         var_Dump($statement);
        $before = true;
        $no_statement = $statement;
        foreach($statement as $nr => $part){
            if(in_array($part, Operator::LIST)){
                if($before === false){
                    break; //might have another operator but first solve this one...
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
        if($left === null){
            var_dump($no_statement);
            var_dump($node);
            var_dump($statement);
            die;
        }
        $node['left'] = $left;
        $node['operator'] = $operator;
        $node['right'] = $right;
        $node['statement'] = $statement;
        $statement = Operator::execute($node, $parser);
//         var_dump($statement);
        return $statement;
    }

    public static function complete($node=array(), $attribute='', $parser=null){
        $statement = $node[Tag::STATEMENT];
        array_unshift($statement, $node[$attribute]);

        $counter = 0;
        while(Operator::has($statement, $parser)){
            $statement = Operator::statement($statement, $parser);
//             var_dump($statement);
            $parser->data('priya.debug3', true);
            $counter++;
            if($counter > Operator::MAX){
                throw new Exception('Operator::Complete:MAX reached...');
                break;
            }
        }
        if(isset($statement[0])){
            $node[$attribute] = $statement[0];
            $node[Tag::STATEMENT] = array();
        }
        if(isset($statement[1])){
            throw new Exception('Undefined state detected, have unknown data');
        }
        return $node;
    }
}