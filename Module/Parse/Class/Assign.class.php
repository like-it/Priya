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
        Assign::QUOTE_DOUBLE
    );

    public static function remove($tag=array(), $attribute='', $parser=null){
        $method = Assign::EQUAL;
        $explode = explode($method, $tag[Tag::TAG], 2);

        if(!isset($explode[1])){
            $tag[$attribute] = $tag[Tag::TAG];
        } else {
            $tag[$attribute] = rtrim($explode[1], Parse::SPACE);
        }
        return $tag;
    }

    public static function select($tag=array(), $parser=null){
        $method = Assign::EQUAL;
        $explode = explode($method, $tag[Tag::TAG], 2);

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
        if(
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
        return $tag;
    }

    public static function find($tag=array(), $string='', $parser=null){
        $tag = Assign::select($tag, $parser);
        if($tag[Tag::ASSIGN] === null){
            return $string;
        }
          //we have assign
        if(substr($tag[Tag::VALUE], 0, 1) == Variable::SIGN){
            $tag[Tag::VALUE] = Tag::OPEN . $tag[Tag::VALUE];
        }
        elseif(substr($tag[Tag::VALUE], 0, 2) == Tag::OPEN . Variable::SIGN){
            //needs to keep the }
        }
        else {
            if(substr($tag[Tag::VALUE], -1) == Tag::CLOSE){
                $tag[Tag::VALUE] = substr($tag[Tag::VALUE], 0, -1);
            }
        }
        $tag[Tag::VALUE] = Parse::token($tag[Tag::VALUE], $parser->data(), false, $parser);
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
                default :
                    $parser->data($tag[Tag::ATTRIBUTE], $right);
                    break;
                }
            }
        }
        return $tag;
    }
}