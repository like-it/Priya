<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
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

    const EQUAL = '=';
    const EQUAL_EQUAL= '==';
    const EQUAL_EQUAL_EQUAL= '===';

    const NOT = '!';
    const NOT_EQUAL = '!=';
    const NOT_EQUAL_EQUAL= '!==';

    const LESS_THAN_GREATER_THAN = '<>';    //not equal

    const LESS_THAN = '<';
    const LESS_THAN_EQUAL = '<=';
    const GREATER_THAN = '<';
    const GREATER_THAN_EQUAL = '<=';

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
        Operator::EQUAL_EQUAL,
        Operator::EQUAL_EQUAL_EQUAL,
        Operator::NOT_EQUAL,
        Operator::NOT_EQUAL_EQUAL,
        Operator::LOGICAL_AND,
        Operator::LOGICAL_AND_AND,
        Operator::LOGICAL_OR,
        Operator::LOGICAL_OR_OR,
        Operator::LOGICAL_XOR,
        Operator::LESS_THAN_GREATER_THAN,
        Operator::LESS_THAN,
        Operator::LESS_THAN_EQUAL,
        Operator::GREATER_THAN,
        Operator::GREATER_THAN_EQUAL
    );

    public static function find($tag='', $string='', $parser=null){
        return $tag;
    }

    public static function execute($node=array(), $parser=null){
        $result = null;
        switch($node['operator']){
            case '+' :
                if(!is_numeric($node['left'])){
                    var_dump($node);
                    die;
                }
                if(!is_numeric($node['right'])){
                    var_dump($node);
                    die;
                }
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
                $result = $node['left'] . $node['right'];
            break;
            case '|' :
                $result = $node['left'] | $node['right'];
            break;
            case '&' :
                $result = $node['left'] & $node['right'];
            break;
            case '^' :
                $result = $node['left'] ^ $node['right'];
            break;
            case '==' :
//                 var_Dump($node);
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
//                 var_dump($node);
                $result = $node['left'] == $node['right'];
            break;
            case '===' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] === $node['right'];
            break;
            case '!==' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] !== $node['right'];
            break;
            case '!=' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] != $node['right'];
            break;
            case '<>' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] <> $node['right'];
            break;
            case '<=' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] <= $node['right'];
            break;
            case '>=' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] >= $node['right'];
            break;
            case '>>' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] >> $node['right'];
            break;
            case '<<' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] << $node['right'];
            break;
            case '>' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] > $node['right'];
            break;
            case '<' :
                //first complete $node['right']
                $node = Operator::complete($node, 'right', $parser);
                $result = $node['left'] < $node['right'];
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
            $start = substr($part, 0, 1);
//             var_dump($part);
            $end = substr($part, -1, 1);
            if(
                in_array(
                    $start,
                    array(
                        '\'',
                        '"'
                    )
                ) &&
                in_array(
                    $end,
                    array(
                        '\'',
                        '"'
                    )
                )
            ){
                continue;
            }
            if(in_array($part, Operator::LIST) && $part !== true){
                return true;
            }
        }
        if($parser->data('priya.debug2')){
//             var_dump($statement);
        }

        return false;
    }

    public static function variable($string='', $parser=null){
        if(substr($string, 0, 1) == '$'){
            var_dump('found');
            var_dump($string);
            die;
        }
        return $string;
    }

    public static function string($string='', $parser=null){
        $start = substr($string, 0, 1);
        $end = substr($string, -1);
        if(
            $start == '\'' &&
            $end == '\''
        ){
            return substr($string, 1, -1);
        }
        elseif(
            $start == '"' &&
            $end == '"'
        ){
            $string = substr($string, 1, -1);
            $string = Parse::token($string, $parser->data(), false, $parser);
            return $string;
        }
        return $string;
    }

    public static function statement($statement=array(), $parser=null){
        if(empty($statement)){
            return $statement;
        }
        $before = true;
        $no_statement = $statement;
        $right_negative = false;
//         var_dump($statement);
        foreach($statement as $nr => $part){
            $part = trim($part);
            if(empty($part)){
                unset($statement[$nr]);
                continue;
            }
            if(in_array($part, Operator::LIST)){
                if($before === false){
                    if(!empty($right)){
                        break; //might have another operator but first solve this one...
                    }
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
                if($right == '-'){
                    $right_negative = true;
                }
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
        $left = Operator::variable($left, $parser);
        $left = Operator::string($left, $parser);
        $right = Operator::variable($right, $parser);
        $right = Operator::string($right, $parser);
        $node['left'] = $left;
        $node['operator'] = $operator;
        if($right_negative){
            $node['right'] = -$right;
        } else {
            $node['right'] = $right;
        }
        $node['statement'] = $statement;
//         var_dump($parser->data('priya.module.parser'));
//         var_dump($node);
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
            var_dump($statement);
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

    public static function set($set=array(), $parser=null){
        $counter = 0;
        $statement = array();
        $statement[$counter] = '';
        $skip = 0;
        $parse = false;
        $variable = false;
        $debug = false;
        foreach($set as $nr => $char){
            if(
                (
                    $char == '"' ||
                    $char == '\''
                ) &&
                $parse === true
            ){
                $statement[$counter] .= $char;
                $parse = false;
                $counter++;
                continue;
            }
            if(
                (
                    $char == '"' ||
                    $char == '\''
                ) &&
                $parse === false
            ){
                if(!empty($statement[$counter])){
                    $counter++;
                    $statement[$counter] = '';
                }
                $statement[$counter] .= $char;
                $parse = true;
                continue;
            }
            if($parse === true){
                $statement[$counter] .= $char;
                continue;
            }
            if(
                $parse === false &&
                $variable === false &&
                $char == '$'
            ){
                if(!empty($statement[$counter])){
                    $counter++;
                    $statement[$counter] = '';
                }
                $statement[$counter] .= $char;
                $variable = true;
                $debug = true;
                continue;
            }
            if(
                $parse === false &&
                $variable === true
            ){
                if(
                    !in_array(
                        $char,
                        array(
                            ' ',
                            '=',
                            '+',
                            '-',
                            '/',
                            '*',
                            '^',
                            '%',
                            '&',
                            '|'
                        )
                    )
                ){
                    $statement[$counter] .= $char;
                    continue;
                } else {
                    $variable = false;
                    $counter++;
                    $statement[$counter] = '';
                }
            }
            if($skip > 0){
                $skip--;
                continue;
            }
            elseif(
                in_array(
                    $char,
                    array(
                        '+',
                        '-',
                        '/',
                        '*',
                        '&',
                        '|',
                        '=',
                        '~',
                        '<',
                        '>',
                        'a',
                        'o',
                        'x',
                        '!',
                        '^',
                        '%',
                    )
                )
            ){
                $counter++;
                $statement[$counter] = $char;
                if(isset($set[$nr + 1])){
                    $next = $set[$nr + 1];
                }
                if(isset($set[$nr + 2])){
                    $next_next = $set[$nr + 2];
                }
                if(
                    (
                        $char == '*' &&
                        $next == '*'
                    ) ||
                    (
                        $char == '<' &&
                        $next == '='
                    ) ||
                    (
                        $char == '&' &&
                        $next == '&'
                    ) ||
                    (
                        $char == '|' &&
                        $next == '|'
                    ) ||
                    (
                        $char == '>' &&
                        $next == '='
                    ) ||
                    (
                        $char == '<' &&
                        $next == '>'
                    ) ||
                    (
                        $char == '<' &&
                        $next == '<'
                    ) ||
                    (
                        $char == '>' &&
                        $next == '>'
                    ) ||
                    (
                        $char == 'o' &&
                        $next == 'r'
                    ) ||
                    (
                        $char == '=' &&
                        $next == '=' &&
                        $next_next != '='
                    ) ||
                    (
                        $char == '!' &&
                        $next == '=' &&
                        $next_next != '='
                    )
                ){
                    $statement[$counter] .= $next;
                    $skip = 1;
                }
                elseif(
                    (
                        $char == '=' &&
                        $next == '=' &&
                        $next_next == '='
                    ) ||
                    (
                        $char == '!' &&
                        $next == '=' &&
                        $next_next == '='
                    ) ||
                    (
                        $char == 'a' &&
                        $next == 'n' &&
                        $next_next == 'd'
                    ) ||
                    (
                        $char == 'x' &&
                        $next == 'o' &&
                        $next_next == 'r'
                    )
                ){
                    $statement[$counter] .= $next . $next_next;
                    $skip = 2;
                }
                $counter++;
                $statement[$counter] = '';
            } else {
                if(!isset($statement[$counter])){
                    $statement[$counter] = '';
                }
                $statement[$counter] .= $char;
            }

        }
        if($debug){
            var_dump($statement);
//             die;
        }

        if($parser->data('priya.debug') === true){
//             var_Dump($statement);
//             die;
        }
        return $statement;
    }
}