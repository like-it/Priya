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

    public static function is($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
            return true;
        }
        return false;
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
        if(!isset($operator['type']) || $operator['type'] != 'operator'){
            return $operator;
        }
        $operator['operator'] = $operator['value'];
        $operator['execute'] = $operator['left'] . $operator['operator'] . $operator['right'];
//         debug($operator, 'execute');
        //add % & ** >= <=
        $original = $operator['value'];
        switch($operator['value']){
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
            break;
            case '>' :
                $operator['value'] = $operator['left'] > $operator['right'];
            break;
            case '<' :
                $operator['value'] = $operator['left'] < $operator['right'];
            break;
            case '<=' :
                $operator['value'] = $operator['left'] <= $operator['right'];
            break;
            case '>=' :
                $operator['value'] = $operator['left'] >= $operator['right'];
            break;
            case '<>' :
                $operator['value'] = $operator['left'] <> $operator['right'];
            break;
            case '!=' :
                $operator['value'] = $operator['left'] != $operator['right'];
            break;
            case '!==' :
                $operator['value'] = $operator['left'] !== $operator['right'];
            break;
            case '===' :
                $operator['value'] = $operator['left'] === $operator['right'];
            break;
            case '==' :
                $operator['value'] = $operator['left'] == $operator['right'];
            break;
            case '%' :
                $operator['value'] = $operator['left'] % $operator['right'];
            break;
            case '&' :
                $operator['value'] = $operator['left'] & $operator['right'];
            break;
            case '|' :
                $operator['value'] = $operator['left'] | $operator['right'];
               break;
            case '^' :
                $operator['value'] = $operator['left'] ^ $operator['right'];
               break;
            case '<<' :
                $operator['value'] = $operator['left'] << $operator['right'];
               break;
            case '>>' :
                $operator['value'] = $operator['left'] >> $operator['right'];
            break;
            default :
                debug('undefined operator in execute');
            break;
        }
        debug($operator);

        if(!isset($operator['value'])){
            debug($operator, 'no value');
        }
        $operator['type'] = Variable::type($operator['value']);
        return $operator;
    }

    public static function statement($statement=array(), $input=null){
        //add original
        $left = array();
        $right = array();
        $operator = array();
        foreach($statement as $nr => $record){
            if(Operator::is($record)){
                if(!empty($operator)){
                    break; //only 1 at a time
                }
                unset($statement[$nr]);
                $operator = $record;
                continue;
            }
            unset($statement[$nr]);
            if(!isset($record['value'])){
                continue;
            }
            if(!isset($record['type'])){
                continue;
            }
            if($record['type'] == Token::TYPE_STRING && isset($record['value']) && substr($record['value'], 0, 1) == '\'' && substr($record['value'], -1, 1) == '\''){
                $record['value'] = substr($record['value'], 1, -1);
                $record['value'] = str_replace('\\\'', '\'', $record['value']);
            }
            if($record['type'] == Token::TYPE_WHITESPACE){
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
            $record = Token::cast($record);
            if(!isset($operator['left'])){
                $operator['left'] = $record['value'];
            } else {
                $operator['left'] .= $record['value'];
            }
            $operator['left_parse'][$nr] = $record;
        }
        foreach($operator['right_parse']as $nr => $record){
            $record = Token::cast($record);
            if(!isset($operator['right'])){
                $operator['right'] = $record['value'];
            } else {
                $operator['right'] .= $record['value'];
            }
            $operator['right_parse'][$nr] = $record;
        }
//         $operator =  Token::cast($operator, 'left');
//         $operator =  Token::cast($operator, 'right');
        $operator = Operator::execute($operator);
//         debug($operator, 'operator', true);
        array_unshift($statement, $operator);
        return $statement;
    }

}