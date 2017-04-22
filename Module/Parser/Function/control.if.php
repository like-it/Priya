<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Application;
use Priya\Module\Handler;
use Priya\Module\Parser;
use Priya\Module\Math;

function control_if($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
    $result = '';
    $if = array();
    foreach($argumentList as $key => $argument){
        if(empty($argument['methodList'])){
//             echo 'empty methodList';
            continue;
        }
        foreach($argument['methodList'] as $nr => $methodList){
            foreach ($methodList as $method_nr => $methodCollection){
                foreach ($methodCollection as $method_collection_key => $method){
                    if(empty($method['function'])){
                        continue;
                    }
                    $function = $method['function'];
                    $url = $dir . 'function.' . $function . '.php';
                    $function = 'function_' . $function;

                    if(file_exists($url)){
                        require_once $url;
                    } else {
                        var_dump('missing file: ' . $url);
                        //remove function ?
                        continue;
                    }

                    if(function_exists($function) === false){
                        var_dump('missing function: ' . $function);
                        //trigger error?
                        continue;
                    }
                    $argList = array();
                    if(!empty($method['argumentList'])){
                        $argList = $method['argumentList'];
                    }
                    $res =  $function($method_collection_key, $argList, $parser);
                    $argument['statement'] = str_replace($method_collection_key, $res, $argument['statement']);
                }
            }
        }
        /*
         * @todo
         * only own methods can pass and we should add forbidden methods here
         * add elseif statements
         */
        $eval = 'if(' . $argument['statement'] .'){ $result = true; } else { $result = false; }';
        eval($eval);
        return $result;
        $parts = $parser->explode_single(
                array(
                        '&&',
                        '||',
                        'and',
                        'or',
                        'xor',
                ),
                $argument['statement']
        );
        $operands = $argument['statement'];
        foreach($parts as $part){
            $operands = str_replace($part, ',', $operands);
        }
        $operands= explode(',', $operands);
        $if = array();
        $node = array();
        foreach($operands as $nr => $operand){
            switch($operand){
                case '&&' :
                    /*
                    $node[] = array_shift($parts);
                    if(count($node) == 2){
                        $begin = parse(reset($node), $parser);
                        var_dump($begin);
                        $end = parse(end($node), $parser);
                        var_dump($end);
                        if($begin && $end){
                            $if[][$operand] = true;
                        } else {
                            $if[][$operand] = false;
                        }
                        $node= array();
                    } else {
                        var_dump($node);
                        trigger_error('huh');
                    }
                    */
                    break;
                default :
                    $node[] = array_shift($parts);
            }
        }
//         $if[] = $argument['statement'];
    }
//     var_dump($if);
    return $result;
}

function parse($string='', $parser=''){
    $args = $parser->explode_single(
            array(
                    '==',
                    '===',
                    '<>',
                    '!=',
                    '!==',
                    '<',
                    '<=',
                    '>',
                    '>=',
                    '<=>'
            ),
            $string
            );
    $operators = $string;
    foreach($args as $arg){
        $operators = str_replace($arg, ',', $operators);

    }
    $operators = explode(',', $operators);
    $var = array();
    foreach($operators as $operator){
        switch($operator){
            case '===' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) === end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '!==' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) !== end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '==' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    $begin = evaluate(trim(reset($var)));
                    $end = evaluate(trim(end($var)));
                    if($begin == $end){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    var_dump($var);
                    die;
                    trigger_error('huh');
                }
                break;
            case '!=' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) != end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '<' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) < end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '<=' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) <= end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '>' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) > end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '>=' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) >= end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '<>' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) <> end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            case '<=>' :
                $var[] = array_shift($args);
                if(count($var) == 2){
                    if(reset($var) <= end($var)){
                        return true;
                    } else {
                        return false;
                    }
                    $var = array();
                } else {
                    trigger_error('huh');
                }
                break;
            default :
                $var[] = array_shift($args);
        }

    }
    return false;
}

function evaluate($input=''){
    $math = new Math();
    $output = $math->calculate($input);
    return $output;

}
