<?php

namespace Priya\Module\Parse;

use Priya\Module\Core\Object;
use stdClass;

class Token extends Core {
    const TYPE_NULL = 'null';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_VARIABLE = 'variable';
    const TYPE_OPERATOR = 'operator';
    const TYPE_MIXED = 'mixed';
    const TYPE_WHITESPACE = 'whitespace';
    const TYPE_STATEMENT = 'statement';
    const TYPE_PARENTHESE = 'parenthese';
    const TYPE_SET = 'set';
    const TYPE_OPEN = 'open';
    const TYPE_CLOSE = 'close';

    public static function all($token=''){
        $tokens = token_get_all('<?php $variable=' . $token . ';');
        array_shift($tokens); //remove php tag
        array_shift($tokens); //remove $variable
        array_shift($tokens); //remove =
        array_pop($tokens); //remove ;

        foreach ($tokens as $key => $token){
            if(is_array($token)){
                $tokens[$key][2] = token_name($token[0]);
            } else {
                $tokens[$key] = array(0 => -1, 1 => $token);
            }
            if(empty($tokens[$key][2])){
                switch($tokens[$key][1]){
                    case '(' :
                        $tokens[$key][2] = 'T_PARENTHESE_OPEN';
                    break;
                    case ')' :
                        $tokens[$key][2] = 'T_PARENTHESE_CLOSE';
                    break;
                    case '[' :
                        $tokens[$key][2] = 'T_SQUARE_BRACKET_OPEN';
                    break;
                    case ']' :
                        $tokens[$key][2] = 'T_SQUARE_BRACKET_CLOSE';
                    break;
                    case '{' :
                        $tokens[$key][2] = 'T_BRACKET_OPEN';
                    break;
                    case '}' :
                        $tokens[$key][2] = 'T_BRACKET_CLOSE';
                    break;
                    case ',' :
                        $tokens[$key][2] = 'T_COMMA';
                    break;
                    case ';' :
                        $tokens[$key][2] = 'T_SEMI_COLON';
                    break;
                    case '"' :
                        $tokens[$key][2] = 'T_DOUBLE_QUOTE';
                    break;
                    case ':' :
                        $tokens[$key][2] = 'T_COLON';
                    case '$' :
                        $tokens[$key][2] = 'T_DOLLAR_SIGN';
                    break;
                }
            }
            if(empty($tokens[$key][2])){
                $operators = Operator::Arithmetic();
                if(in_array($tokens[$key][1], $operators)){
                    $tokens[$key][0] = -2;
                    $tokens[$key][2] = 'T_OPERATOR_ARITHMETIC';
                } else {
                    $operators = Operator::Bitwise();
                    if(in_array($tokens[$key][1], $operators)){
                        $tokens[$key][0] = -3;
                        $tokens[$key][2] = 'T_OPERATOR_BITWISE';
                    } else {
                        $operators = Operator::COMPARE();
                        if(in_array($tokens[$key][1], $operators)){
                            $tokens[$key][0] = -4;
                            $tokens[$key][2] = 'T_OPERATOR_COMPARE';
                        }
                    }
                }
            }
            if(isset($token[1]) && $token[1] == 'null'){
                $tokens[$key][0] = -5;
                $tokens[$key][1] = null;
                $tokens[$key][2] = Token::TYPE_NULL;
            }
            elseif(isset($token[1]) && $token[1] == 'false'){
                $tokens[$key][0] = -6;
                $tokens[$key][1] = false;
                $tokens[$key][2] = Token::TYPE_BOOLEAN;
            }
            elseif(isset($token[1]) && $token[1] == 'true'){
                $tokens[$key][0] = -6;
                $tokens[$key][1] = true;
                $tokens[$key][2] = Token::TYPE_BOOLEAN;
            }
        }
        return $tokens;
    }

    public static function parse($value= ''){
        if(is_array($value)){
            $tokens = $value;
        } else {
            $tokens = Token::all($value);
        }
        //debug($value, 'value in parse');
        //         debug($tokens, 'tokens in parse');
        $result = array();
        $record = array();
        $set_depth = 0;
        $parse = '';
        foreach($tokens as $nr => $token){
            if(isset($token[1])){
                $parse .= $token[1];
            }
            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);
                if($record['is_cast'] === true){
                    $record['cast'] = Token::type(Token::get($token));
                    $record['token'][] = $token;
                    continue;
                }
            }
            if(!isset($record['parse'])){
                $record['parse'] = $parse;
            } else {
                $record['parse'] .= $parse;
            }
            $record['value'] = $token[1];
            if(isset($record['type'])){
                $type = Token::type(Token::get($token));
                if($record['type'] != $type){
                    $record['type'] = Token::TYPE_MIXED;
                }
            } else {
                $record['type'] = Token::type(Token::get($token));
            }
            if($record['type'] == token::TYPE_INT){
                $record['value'] += 0;
            }
            if(!isset($result['set'])){
                $record['set'] = array();
            }
            if(Token::is_parenthese($token)){
                if($token[1] == '('){
                    $set_depth++;
                    $record['set']['depth'] = $set_depth;
                } else {
                    $record['set']['depth'] = $set_depth;
                    $set_depth--;
                }
            } else {
                $record['set']['depth'] = $set_depth;
            }

            $result[] = $record;
            $record = array();
        }
        $result = Token::parse_fix_cast($result);
        return $result;
    }

    public static function parse_fix_cast($parse=array()){
        foreach($parse as $nr => $record){
            $to = next($parse);
            if($record['is_cast'] === true && $record['type'] == Token::TYPE_WHITESPACE){
                while($to['type'] == Token::TYPE_WHITESPACE){
                    $to = next($parse);
                }
                $to['is_cast'] = $record['is_cast'];
                $to['cast'] = $record['cast'];

                $record['is_cast'] = false;
                unset($record['cast']);
                $key = key($parse);

                $parse[$nr] = $record;
                $parse[$key] = $to;
            }
        }
        return $parse;
    }

    public static function parse2($value= ''){
        if(is_array($value)){
            $tokens = $value;
        } else {
            $tokens = Token::all($value);
        }
        //debug($value, 'value in parse');
//         debug($tokens, 'tokens in parse');
        $result = array();
        $record = array();
        $set_depth = 0;
        $original = '';
        debug($value, 'value');
        debug(debug_backtrace(true));
        die;
        foreach($tokens as $nr => $token){
//             debug($token);
            if(isset($token[1])){
                $original .= $token[1];
            }
            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);
                $record['original'] = $original;
                if($record['is_cast'] === true){
                    $record['cast'] = Token::type(Token::get($token));
                    $record['token'][] = $token;
                    continue;
                }
            }
            if(Token::is_whitespace($token)){
                if(isset($record['value'])){
                    $record['original'] = $original;
                    $result[] = $record;
                    $record = array();
                    $original = '';
                }
                continue;
            }
            if(Token::is_parenthese($token)){
                if($token[1] == '('){
                    $set_depth++;
                    $record['in_set'] = true;
                    $record['original'] = $original;
                    //$record['type'] = Token::TYPE_SET;
                    $result[] = $record;
                    $record = array();
                    $record['is_cast'] = false;
                    $record['is_set'] = true;
                    $record['type'] = Token::TYPE_PARENTHESE;
                    $record['parenthese'] = $token[1];
                    $record['set'] = array();
                    $record['set']['depth'] = $set_depth;
                    $record['token'][] = $token;
                    $result[] = $record;
                    $record = array();
                    $original = '';
                    continue;
                } else {
                    if(isset($record['original'])){
                        $record['original'] .= $original;
                    } else {
                        $record['original'] = $original;
                    }
                    $result[] = $record;
                    $record = array();

                    $record['is_cast'] = false;
                    $record['is_set'] = true;
                    $record['type'] = Token::TYPE_PARENTHESE;
                    $record['parenthese'] = $token[1];
                    $record['set'] = array();
                    $record['set']['depth'] = $set_depth;
                    $record['token'][] = $token;
                    $result[] = $record;
                    $record = array();
                    $original = '';
                    $set_depth--;
                    continue;
                }
            }
            if(isset($record['type'])){
                $type = Token::type(Token::get($token));
                if($record['type'] != $type){
                    $record['type'] = Token::TYPE_MIXED;
                }
            } else {
                $record['type'] = Token::type(Token::get($token));
            }
            if($record['type'] == Token::TYPE_OPERATOR){
                $record['operator'] = $token[1];
                $record['token'][] = $token;
                $result[] = $record;
                $record = array();
                $original = '';
                continue;
            } else {
                if(!isset($record['value'])){
                    $record['value'] = Token::value($token);
                } else {
                    $add = Token::value($token);
                    if($add){
                        $record['value'] .= $add;
                    }
                }
                $record['token'][] = $token;
                if($record['type'] == token::TYPE_INT){
                    $record['value'] += 0;
                }
            }
        }
        if(!empty($record)){
            $record['original'] = $original;
            $result[] = $record;
        }
        //debug($result, 'result');
        return $result;
    }

    public static function create_object($value= ''){
        if(is_array($value)){
            $tokens = $value;
        } else {
            $tokens = Token::all($value);
        }
        $result = array();
        $record = array();
        foreach($tokens as $nr => $token){
            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);
                if($record['is_cast'] === true){
                    $record['cast'] = Token::type(Token::get($token));
                    continue;
                }
            }
            if(Token::is_bracket($token, Token::TYPE_OPEN) && Token::is_bracket(end($tokens) ,  Token::TYPE_CLOSE)){
                $record['type'] = Token::TYPE_OBJECT;
                if($record['is_cast'] === true){
                    $explode = explode($record['cast'], $value, 2);
                    $record['value'] = ltrim($explode[1], ') ');
                } else {
                    $record['value'] = $value;
                }
                $record['original'] = $record['value'];
                $record['token'] = $tokens;
                $record['value'] = Token::object($record['value']);
                return $record;
            }
        }
        return array();
    }

    public static function create_array($value= ''){
        if(is_array($value)){
            $tokens = $value;
        } else {
            $tokens = Token::all($value);
        }
        $result = array();
        $record = array();
        foreach($tokens as $nr => $token){
            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);
                if($record['is_cast'] === true){
                    $record['cast'] = Token::type(Token::get($token));
                    continue;
                }
            }
            if(Token::is_square_bracket($token, Token::TYPE_OPEN) && Token::is_square_bracket(end($tokens) ,  Token::TYPE_CLOSE)){
                $record['type'] = Token::TYPE_ARRAY;
                if($record['is_cast'] === true){
                    $explode = explode($record['cast'], $value, 2);
                    $record['value'] = ltrim($explode[1], ') ');
                } else {
                    $record['value'] = $value;
                }
                $record['original'] = $record['value'];
                $record['token'] = $tokens;
                $record['value'] = Token::object($record['value'], 'array');
                return $record;
            }
        }
        return array();
    }

    public static function has_equation($value=''){
        $plus = strpos($value, '+');
        $min = strpos($value, '-');
        $mul = strpos($value, '*');
        $div = strpos($value, '/');
        $mod = strpos($value, '/');
        $exp = strpos($value, '**');
        //add % & **
        if(
            $plus === false &&
            $min === false &&
            $mul === false &&
            $div === false &&
            $mod === false &&
            $exp === false
        ) {
            return false;
        } else {
            if($plus > 0){
                $explode = explode('+', $value);
            }
            elseif($min> 0){
                $explode = explode('-', $value);
            }
            elseif($mul> 0){
                $explode = explode('*', $value);
            }
            elseif($div> 0){
                $explode = explode('/', $value);
            }
            elseif($mod> 0){
                $explode = explode('%', $value);
            }
            elseif($exp> 0){
                $explode = explode('**', $value);
            }
            $search = array(
                '(',
                ')',
                '+',
                '-',
                '*',
                '/',
                '%',
                ' ',
                "\r",
                "\n",
                'int',
                'integer',
                'float',
                'double'
            );
            $replace = '';
            foreach($explode as $nr => $subject){
                $explode[$nr] = str_replace($search, $replace, $subject);
            }
            foreach($explode as $nr => $is_numeric){
                if(is_numeric($is_numeric)){
                    continue;
                }
                return false;
            }
            return true;
        }

    }

    public static function variable($parse=array(), Variable $variable){
        foreach ($parse as $nr => $record){
            if(!isset($record['value'])){
                continue;
            }
            if(!isset($record['type'])){
                continue;
            }
            if($record['type'] != Token::TYPE_VARIABLE){
                continue;
            }
            $record['value'] = $variable->replace($record['value']);
            $record['type'] = Variable::type($record['value']);
            $record = Token::cast($record);
            $parse[$nr] = $record;
        }
        return $parse;
    }

    public static function create_equation($value= '', $input=null, $variable=null){
        if(is_array($value)){
            $tokens = $value;
        } else {
            $tokens = Token::all($value);
        }
        $parse = Token::parse($tokens);
        $parse = Token::variable($parse, $variable);
        $set_counter = 0;

        if(Operator::has($parse) === false){
            return false;
        }
        while(Set::has($parse)){
            $set_counter++;
            $set = Set::get($parse);
            $statement = Set::statement($set);
            $operator_counter = 0;
            while (Operator::has($statement)){
                $operator_counter++;
                $statement = Operator::statement($statement);
                if($operator_counter > Operator::MAX){
                    break;
                }
            }
            $record = reset($statement);
            $record['set']['depth']--;
            $parse = Set::replace($parse, $set, $record);
            if($set_counter > Set::MAX){
                break;
            }
        }
        $operator_counter = 0;
        while (Operator::has($parse)){
            $operator_counter++;
            $parse = Operator::statement($parse);
            if($operator_counter > Operator::MAX){
                break;
            }
        }
        if(count($parse) == 1){
            $record = array_shift($parse);
            if(is_numeric($record['value'])){
                return $record['value'] + 0;
            } else {
                return false;
            }
        } else {
            debug($parse, 'output in create_equation');
            return false;
        }
    }

    public static function is_whitespace($token=array()){
        if(isset($token[2]) && in_array($token[2], array(
                'T_WHITESPACE',
        ))){
            return true;
        }
        return false;
    }

    public static function is_operator($token=array()){
        if(isset($token[2]) && in_array($token[2], array(
                'T_OPERATOR_COMPARE',
        ))){
            return true;
        }
        return false;
    }

    public static function is_parenthese($token=array()){
        if(isset($token[2]) && in_array($token[2], array(
                'T_PARENTHESE_OPEN',
                'T_PARENTHESE_CLOSE',
        ))){
            return true;
        }
        return false;
    }

    public static function is_bracket($token=array(), $type=null){
        if($type === null){
            if(isset($token[2]) && in_array($token[2], array(
                    'T_BRACKET_OPEN',
                    'T_BRACKET_CLOSE',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_OPEN){
            if(isset($token[2]) && in_array($token[2], array(
                    'T_BRACKET_OPEN',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_CLOSE){
            if(isset($token[2]) && in_array($token[2], array(
                    'T_BRACKET_CLOSE',
            ))){
                return true;
            }
        }
        return false;
    }

    public static function is_square_bracket($token=array(), $type=null){
        if($type === null){
            if(isset($token[2]) && in_array($token[2], array(
                'T_SQUARE_BRACKET_OPEN',
                'T_SQUARE_BRACKET_CLOSE',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_OPEN){
            if(isset($token[2]) && in_array($token[2], array(
                'T_SQUARE_BRACKET_OPEN',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_CLOSE){
            if(isset($token[2]) && in_array($token[2], array(
                'T_SQUARE_BRACKET_CLOSE',
            ))){
                return true;
            }
        }
        return false;
    }

    public static function is_cast($token=array()){
        if(isset($token[2]) && in_array($token[2], array(
            'T_ARRAY_CAST',
            'T_BOOL_CAST',
            'T_DOUBLE_CAST',
            'T_INT_CAST',
            'T_OBJECT_CAST',
            'T_STRING_CAST',
            'T_UNSET_CAST',
        ))){
            return true;
        }
        return false;
    }

    public static function cast($record=array()){
        if(empty($record['is_cast'])){
            return $record;
        }
        switch ($record['cast']){
            case Token::TYPE_STRING :
                if(empty($record['value'])){
                    $record['value'] = '';
                } else {
                    $record['value'] = (string) $record['value'];
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_STRING;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_INT:
                if(!isset($record['value'])){
                    $record['value'] = 0;
                } else {
                    $record['value'] = intval($record['value']);
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_INT;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_FLOAT:
                if(!isset($record['value'])){
                    $record['value'] = 0.0;
                } else {
                    $record['value'] = floatval($record['value']);
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_FLOAT;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_BOOLEAN:
                if(!empty($record['value'])){
                    $record['value'] =  true;
                } else {
                    $record['value'] =  false;
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_BOOLEAN;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_ARRAY:
                if(!empty($record['value'])){
                    $record['value'] =  Token::object($record['value'], 'array');
                } else {
                    $record['value'] =  array();
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_ARRAY;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_OBJECT:
                if(!empty($record['value'])){
                    $record['value'] =  Token::object($record['value']);
                } else {
                    $record['value'] =  new stdClass();
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_OBJECT;
                unset($record['cast']);
                return $record;
                break;
            default:
                debug('unknown cast');
                break;
        }
    }

    public static function get($token=array()){
        if(isset($token[2])){
            return $token[2];
        } else {
            debug($token);
        }
    }

    public static function type($type=null){
        switch ($type){
            case 'T_STRING_CAST' :
            case 'T_STRING' :
            case 'T_CONSTANT_ENCAPSED_STRING' :
            case 'T_ENCAPSED_AND_WHITESPACE' :
            case 'T_NS_SEPARATOR' :
            case 'T_DOUBLE_QUOTE' :
                return Token::TYPE_STRING;
            break;
            case 'T_LNUMBER' :
            case 'T_INT_CAST' :
                return Token::TYPE_INT;
            break;
            case 'T_DOUBLE_CAST':
            case 'T_DNUMBER' :
                return Token::TYPE_FLOAT;
            break;
            case 'T_BOOL_CAST' :
                return  Token::TYPE_BOOLEAN;
            break;
            case 'T_OPERATOR_COMPARE' :
            case 'T_OPERATOR_ARITHMETIC' :
                return  Token::TYPE_OPERATOR;
            break;
            case 'T_VARIABLE' :
                return Token::TYPE_VARIABLE;
            break;
            case 'T_ARRAY_CAST' :
                return Token::TYPE_ARRAY;
            break;
            case 'T_OBJECT_CAST' :
                return Token::TYPE_OBJECT;
            break;
            case 'T_PARENTHESE_OPEN' :
            case 'T_PARENTHESE_CLOSE':
                return Token::TYPE_PARENTHESE;
            break;
            case 'T_WHITESPACE' :
                return Token::TYPE_WHITESPACE;
            break;
            case Token::TYPE_STRING:
            case Token::TYPE_MIXED:
            case Token::TYPE_NULL:
            case Token::TYPE_BOOLEAN:
                return $type;
            break;
            default :
                debug($type, 'undefined type');
                debug(debug_backtrace());
        }
        return $type;
    }


    public static function value($token=array()){
        if(isset($token[2]) && in_array($token[2], array(
                'T_CONSTANT_ENCAPSED_STRING',
                'T_DNUMBER',
                'T_ENCAPSED_AND_WHITESPACE',
                'T_LNUMBER',
                'T_NUM_STRING',
                'T_VARIABLE',
                'T_STRING',
                'T_STRING_VARNAME',
                'T_NS_SEPARATOR',
                TOKEN::TYPE_STRING,
        ))){
            return $token[1];
        }
        return null;
    }

    public static function restore_return($value='', $random=''){
        $search = array();
        $search[] = '[' . $random . '][return]';
        $search[] = '[' . $random . '][newline]';
        $replace = array();
        $replace[] = "\r";
        $replace[] = "\n";
        $value = str_replace($search, $replace, $value);
        return $value;
    }
}



