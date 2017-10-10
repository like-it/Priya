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

    public static function execute($operator=array(), Variable $variable, $parser=null){
        if(!isset($operator['type']) || $operator['type'] != 'operator'){
            return $operator;
        }
        $operator['operator'] = $operator['value'];
        if(
            !is_array($operator['left']) &&
            !is_array($operator['right'])
        ){
            $operator['execute'] = $operator['left'] . $operator['operator'] . $operator['right'];
        }
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
                $modifier = reset($operator['right_parse']);
                if(is_string($modifier['value'])){
                    $operator = Modifier::execute($operator, $variable, $parser);
                    unset($operator['left']);
                    unset($operator['right']);
                    $operator['modified_is_executed'] = true;
                } else {
                    $operator['value'] = $operator['left'] | $operator['right'];
                }
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
            case '&&' :
                $operator['value'] = $operator['left'] && $operator['right'];
            break;
            case '||' :
                $operator['value'] = $operator['left'] || $operator['right'];
            break;
            default :
                debug('undefined operator (' .  $operator['value'] . ') in execute');
            break;
        }
        if(!isset($operator['value'])){
            debug($operator, 'no value');
        }
        $operator['type'] = Variable::type($operator['value']);
        return $operator;
    }

    public static function statement($statement=array(), Variable $variable, $parser=null){
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
            if(!isset($record['value']) && $record['value'] !== null){
                continue;
            }
            if(!isset($record['type'])){
                continue;
            }
            $record = Value::get($record);
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
        $method = array();
        $method['parse'] = $left;
        $method = Token::method($method, $variable, $parser);
        $operator['left_parse'] = $method['parse'];

        $method = array();
        $method['parse'] = $right;
        $method = Token::method($method, $variable, $parser);
        $operator['right_parse'] = $method['parse'];

        $is_modifier = 	Modifier::is($operator);

        foreach($operator['left_parse']as $nr => $record){
            if($is_modifier === false){
                $record = Token::cast($record);
            }
            if(!isset($operator['left'])){
                $operator['left'] = $record['value'];
            } else {
                $operator['left'] .= $record['value'];
            }
            $operator['left_parse'][$nr] = $record;
        }
        foreach($operator['right_parse']as $nr => $record){
            if($is_modifier === false){
                $record = Token::cast($record);
            }
            if(!isset($operator['right'])){
                $operator['right'] = $record['value'];
            } else {
                $operator['right'] .= $record['value'];
            }
            $operator['right_parse'][$nr] = $record;
        }
        if(!isset($operator['value']) && empty($operator['right_parse'])){
            debug($operator, 'wrong compare');
            debug($statement, 'what is in statement');
            die;
        }
        if($operator['value'] == '&&' || $operator['value'] == '||'){
            $right_statement = $statement;
            array_unshift($right_statement, $operator['right_parse'][0]);
            if(count($right_statement) > 1){
                $right_statement = Operator::statement($right_statement, $variable, $parser);
                $right_statement_count = 0;
                while(count($right_statement) > 1 && $right_statement[0]['value'] === true){
                    $right_statement = Operator::statement($right_statement, $variable, $parser);
                    $right_statement_count++;
                    if($right_statement_count >= Operator::MAX){
                        debug('$right_statement_count >= Operator::MAX');
                        break;
                    }
                }
            }
            $operator['right_parse'][0] = $right_statement[0];
            $operator['right'] = $right_statement[0]['value'];
            $statement = array();
        }
        $operator = Operator::execute($operator, $variable, $parser);
        array_unshift($statement, $operator);
        return $statement;
    }

}