<?php

namespace Priya\Module\Parse;

class Operator extends Core {
    const MAX = 255;

    public static function has($parse=array()){
        foreach($parse as $nr => $record){
            if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
                return true;
            }
        }

        return false;
    }

    public static function compare_array(){
        return array(
            '+',
            '==',
            '===',
            '!=',
            '<>',
            '!==',
        );
    }

    public static function compare(){
        return array(
            '&&',
            '||',
            'and',
            'or',
            'xor',
            '==',
            '===',
            '<>',
            '!=',
            '!==',
            '<',
            '<=',
            '>',
            '>=',
            '<=>',
        );
    }

    public static function arithmetic(){
        return array(
            '+',
            '-',
            '*',
            '/',
            '%',
            '**',
        );
    }

    public static function is_arithmetic($needle){
        return in_array($needle, Operator::arithmetic());
    }

    public static function bitwise(){
        return array(
            '&',
            '|',
            '^',
            '~',
            '<<',
            '>>',
        );
    }

    public static function execute($operator=array()){
        if(!isset($operator['operator'])){
            return $operator;
        }
//         debug($operator, 'execute');
        //add % & ** >= <=
        switch($operator['operator']){
            case '-' :
                $operator['value'] = $operator['left'] - $operator['right'];
                break;
            case '+' :
                $operator['value'] = $operator['left'] + $operator['right'];
                break;
            case '*' :
                $operator['value'] = $operator['left'] * $operator['right'];
                break;
            case '/' :
                $operator['value'] = $operator['left'] / $operator['right'];
            case '>' :
                $operator['value'] = $operator['left'] > $operator['right'];
                break;
            case '<' :
                $operator['value'] = $operator['left'] < $operator['right'];
                break;
        }
        if(!isset($operator['value'])){
            debug($operator, 'no value');
        }
        $operator['statement'] = '';
        foreach($operator['left_parse'] as $nr => $record){
            $operator['statement'] .= $record['original'];
        }
        foreach($operator['right_parse'] as $nr => $record){
            $operator['statement'] .= $record['original'];
        }

        //replace original with math
        unset($operator['operator']);
        $operator['type'] = Variable::type($operator['value']);
//         debug($operator);
        return $operator;
    }

    public static function statement($statement=array(), $input=null){
        //add original
        $left = array();
        $right = array();
        $operator = array();
        foreach($statement as $nr => $record){
            if(Assign::is_operator($record)){
                $operator = $record;
                continue;
            }
            if(!isset($record['value'])){
                continue;
            }
            if(empty($operator)){
                $left[] = $record;
            } else {
                $right[] = $record;
            }
        }
        $operator['left_parse'] = $left;
        $operator['right_parse'] = $right;
        if(count($operator['left_parse']) > 1){
            debug($operator, 'left parse > 1');
        }
        if(count($operator['right_parse']) > 1){
            if(count($operator['right_parse'] == 2 && count($operator['left_parse']) == 1)){
                $wrong_value = array_shift($operator['left_parse']);
                $operator['left_parse'][] = array_shift($operator['right_parse']);
            } else{
                debug($operator, 'right parse > 1');
            }
        }
//         debug($statement);
        foreach($operator['left_parse']as $nr => $record){
            if(!isset($operator['left'])){
                $operator['left'] = $record['value'];
            } else {
                debug($operator, 'already set');
                debug($record, 'record');
                $operator['left'] .= $record['value'];
            }
        }
        foreach($operator['right_parse']as $nr => $record){
            if(!isset($operator['right'])){
                $operator['right'] = $record['value'];
            } else {
                debug($operator, 'already set');
                debug($record, 'record');
                $operator['right'] .= $record['value'];
            }
        }
        $operator = Operator::execute($operator);
        return $operator;
    }

}