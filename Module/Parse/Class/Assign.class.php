<?php
/**
 * @todo
 *
 * use select in find
 * add value to select (used in find)
 *
 */
namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Assign extends Core {
    //rename to SIGN
    const TAG = '$';
    const EQUAL = '=';
    const SPACE = ' ';
    const EMPTY = '';

    const QUOTE_SINGLE = '\'';
    const QUOTE_DOUBLE = '"';

    const PLUS = '+';
    const MIN = '-';
    const ADD = '.';
    const NOT = '!';
    const MULTIPLY = '*';
    const DIVIDE = '/';
    const MODULO = '%';
    const EXPONENTIAL = '**';

    const MASK =
    Assign::PLUS .
    Assign::MIN .
    Assign::ADD .
    Assign::NOT .
    Assign::MULTIPLY .
    Assign::DIVIDE .
    Assign::SPACE;

    const METHOD = array(
        Assign::PLUS,
        Assign::MIN,
        Assign::ADD,
        Assign::NOT,
        Assign::MULTIPLY,
        Assign::DIVIDE,
    );

    const NOT_BEFORE = array(
        Assign::QUOTE_SINGLE,
        Assign::QUOTE_DOUBLE,
        Method::OPEN
    );

    public static function remove($tag=array(), $attribute='', $parser=null){
        $method = Assign::EQUAL;
        $string = $tag[Tag::TAG];
        if(
            substr($string, 0, 1) == '{' &&
            substr($string, -1) == '}'
        ){
            $string = substr($string, 1, -1);
        }
        $explode = explode($method, $string, 2);

        if(!isset($explode[1])){
            $tag[$attribute] = $tag[Tag::TAG];
        } else {
            //check beginning for assign
            $before = $parser->explode_multi(Assign::NOT_BEFORE, $explode[0], 2);
            if(isset($before[1])){
                $tag[$attribute] = $tag[Tag::TAG];
                return $tag;
            }
            $variable = explode(Assign::TAG, $explode[0], 2);
            if(!isset($variable[1])){
                $tag[$attribute] = $tag[Tag::TAG];
                return $tag;
            }
            $tag[$attribute] = trim($explode[1], Parse::SPACE);
        }
        return $tag;
    }

    public static function select($tag=array(), $parser=null){
        $method = Assign::EQUAL;
        $string = $tag[Tag::TAG];
        if(
            substr($string, 0, 1) == '{' &&
            substr($string, -1) == '}'
        ){
            $string = substr($tag[Tag::TAG], 1, -1);
        }
//         var_dump($string);
        $explode = explode($method, $string, 2);

        $tag[Tag::ATTRIBUTE] = null;
        $tag[Tag::ASSIGN] = null;

        if(!isset($explode[1])){
            return $tag;
        }
        $before = $parser->explode_multi(Assign::NOT_BEFORE, $explode[0], 2);
        if(isset($before[1])){
            return $tag;
        }
        $variable = explode(Assign::TAG, $explode[0], 2);
        if(!isset($variable[1])){
            return $tag;
        }
        //we have an assign
        $check = substr($explode[0], -1);
        if(substr($explode[0], -2) == Assign::EXPONENTIAL){
            $method = Assign::EXPONENTIAL . $method;
        }
        elseif(
            in_array(
                $check,
                Assign::METHOD
            )
        ){
            $method = $check . $method;
        }

        $tag[Tag::ATTRIBUTE] = rtrim($variable[1], Assign::MASK);
        $tag[Tag::ASSIGN] = $method;
        $tag[Tag::VALUE] = trim($explode[1], Parse::SPACE);
//         var_dump($tag[Tag::VALUE]);
        return $tag;
    }

    /**
     * should improve variables to priya variables
     * nice to have methods to priya methods
     * @param array $tag
     * @param unknown $parser
     */
    public static function improve($tag=array(), $parser=null){
        if($parser->data('priya.debug3') === true){
            var_dump($parser->data('end'));
            $tag[Tag::VALUE] = Parameter::find($tag[Tag::VALUE], $parser);
            var_dump($tag);
            die;
        }


        if(substr($tag[Tag::VALUE], 0, 1) == Variable::SIGN){
            $tag[Tag::VALUE] = Tag::OPEN . $tag[Tag::VALUE] . Tag::CLOSE;
        }
        elseif(substr($tag[Tag::VALUE], 0, 2) == Tag::OPEN . Variable::SIGN){
            //needs to keep the }
        }
        else {
            if(substr($tag[Tag::VALUE], -1) == Tag::CLOSE){
                $tag[Tag::VALUE] = substr($tag[Tag::VALUE], 0, -1);
            }
        }
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $tag = Assign::select($tag, $parser);
        if($tag[Tag::ASSIGN] === null){
            return $string;
        }
        //we have assign
        $parameter = Parameter::find($tag[Tag::VALUE], $parser);
        $tag[Tag::VALUE] = $parameter[0];
        if(isset($parameter[1])){
            throw new Exception('Assign::find:Unexpected extra parameter. Can only assign 1 value...');
        }
//         var_dump($tag);
        /*

        $tag = Assign::improve($tag, $parser);

//         var_dump($tag[Tag::VALUE]);
        $parser->data('priya.module.parser.assign.operator', true);
        $tag[Tag::VALUE] = Parse::token($tag[Tag::VALUE], $parser->data(), false, $parser);
        //add sets, equations
         */
        $tag = Assign::execute($tag, Tag::VALUE, $parser);
        $temp = explode($tag[Tag::TAG], $string, 2);
        $string = implode(Assign::EMPTY, $temp);
        return $string;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
        if(
            !empty($tag[Tag::ATTRIBUTE]) &&
            !empty($tag[Tag::ASSIGN])
        ){
        if(!isset($tag[$attribute])){
            $tag[$attribute] = null;
        }
        $left = Cast::translate($parser->data($tag[Tag::ATTRIBUTE]));
        $right = Cast::translate($tag[$attribute]);
//         var_dump($tag);
//         var_dump($attribute);
//         var_Dump($right);
        $type = gettype($tag[$attribute]);

        if($type == Parse::TYPE_ARRAY){
            switch($tag[Tag::ASSIGN]){
                case Assign::PLUS:
                    $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                break;
                case Assign::NOT . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left != $right);
                break;
                default :
                    $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
            }
        }
        elseif($type == Parse::TYPE_OBJECT){
            switch($tag[Tag::ASSIGN]){
                case Assign::PLUS:
                    $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                break;
                case Assign::NOT . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left != $right);
                break;
                default :
                    $parser->data($tag[Tag::ATTRIBUTE], $right);
                break;
            }
        } else {
            switch($tag[Tag::ASSIGN]){
                case Assign::ADD . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left .= $right);
                break;
                case Assign::PLUS . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left += $right);
                break;
                case Assign::MIN . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left -= $right);
                break;
                case Assign::MULTIPLY . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left * $right);
                break;
                case Assign::DIVIDE . Assign::EQUAL:
                    if(empty($right)){
                        throw new Exception('Cannot divide to zero on line: ' . $tag[Tag::LINE] . ' column: ' . $tag[Tag::COLUMN] . ' in ' . $parser->data('priya.module.parser.document.url'));
                    }
                    $parser->data($tag[Tag::ATTRIBUTE], $left / $right);
                break;
                case Assign::NOT . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left != $right);
                break;
                case Assign::PLUS:
                    $parser->data($tag[Tag::ATTRIBUTE], $left + $right);
                break;
                case Assign::MODULO:
                    $parser->data($tag[Tag::ATTRIBUTE], $left % $right);
                break;
                case Assign::EXPONENTIAL . Assign::EQUAL:
                    $parser->data($tag[Tag::ATTRIBUTE], $left ** $right);
                break;
                default :
//                     var_Dump($tag);
//                     var_dump($right);
                    $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
                }
            }
        }
        return $tag;
    }
}