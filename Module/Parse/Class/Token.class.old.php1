<?php

namespace Priya\Module\Parse;

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
    const TYPE_STATEMENT = 'statement';
    const TYPE_PARENTHESE = 'parenthese';
    const TYPE_SET = 'set';

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
        }
        return $tokens;
    }

    public static function parse($value= ''){
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
                    $record['token'][] = $token;
                    continue;
                } else {
                    if(Token::is_whitespace($token)){
                        if(isset($record['value'])){
                            $result[] = $record;
                            $record = array();
                        }
                        continue;
                    }
                    if(Token::is_parenthese($token)){
                        $record['in_set'] = true;
                        $record['type'] = Token::TYPE_SET;
                        $result[] = $record;
                        $record = array();
                        $record['is_cast'] = false;
                        $record['type'] = Token::TYPE_PARENTHESE;
                        $record['parenthese'] = $token[1];
                        $record['token'][] = $token;
                        $result[] = $record;
                        $record = array();
                        continue;
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
                        continue;
                    }
                }
            }
            if(Token::is_whitespace($token)){
                if(isset($record['value'])){
                    $result[] = $record;
                    $record = array();
                }
                continue;
            }
            if(Token::is_parenthese($token)){
                $record['in_set'] = true;
                $record['type'] = Token::TYPE_SET;
                $result[] = $record;
                $record = array();
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_PARENTHESE;
                $record['parenthese'] = $token[1];
                $record['token'][] = $token;
                $result[] = $record;
                $record = array();
                continue;
            }
            if(isset($record['type'])){
                $type = Token::type(Token::get($token));
                if($record['type'] != $type){
                    $record['type'] = Token::TYPE_MIXED;
                }
            } else {
                $record['type'] = Token::type(Token::get($token));
            }

            if(!isset($record['value'])){
                $record['value'] = Token::value($token);
            } else {
                $record['value'] .= Token::value($token);
            }
            $record['token'][] = $token;

        }
        if(!empty($record)){
            $result[] = $record;
        }
        if(Token::has_operator($result)){
            $operator = Token::operator($result);
            if(empty($operator)){
                var_dump('__EMPTY_OPERATOR');
                //no more operators
            }
            $operator = Token::left($result, $operator);
            $operator = Token::right($result, $operator);
            var_dump('has operator-------------------------------');
//             var_export($operator);
        }
        return $result;
            /*

            if(!isset($record['is_cast'])){
                $record['is_cast'] = Token::is_cast($token);

                if(!empty($record['is_cast'])){
                    $record['cast'] = Token::type(Token::get($token));
                } else {
                    $record['type'] = Token::type(Token::get($token));
                    if($record['type'] == Token::TYPE_OPERATOR){
                        $record['left'] = Token::left($tokens, $token);
                        $left_token_record = array();
                        $left_token_record['is_cast'] = false;
                        foreach($record['left'] as $left_nr => $left_token){
                            if(Token::is_operator($left_token)){
                                $record['operator'] = $left_token[1];
                                continue;
                            }
                            if(Token::is_cast($left_token)){
                                $left_token_record['is_cast'] = true;
                                $left_token_record['cast'] = Token::type(Token::get($left_token));
                                continue;
                            }
                            $left_token_value = Token::value($left_token);
                            if($left_token_value!== null){
                                if(!isset($left_token_record['type'])){
                                    $left_token_record['type'] = Token::get($left_token);
                                }
                                elseif($left_token_record['type'] != Token::get($left_token)){
                                    var_dump('found a difficulty');
                                }
                                if(!isset($left_token_record['value'])){
                                    $left_token_record['value'] = $left_token_value;
                                } else {
                                    $left_token_record['value'] .= $left_token_value;
                                }

                                $left_token_record = Token::record($left_token_record);
                                $record['left_token_record'] = $left_token_record;
                            }
                        }
                        $record['right'] = Token::right($tokens, $token);
                        $right_token_record = array();
                        $right_token_record['is_cast'] = false;
                        foreach($record['right'] as $right_nr => $right_token){
                            if(Token::is_operator($right_token)){
                                $record['operator'] = $right_token[1];
                                continue;
                            }
                            if(Token::is_cast($right_token)){
                                $right_token_record['is_cast'] = true;
                                $right_token_record['cast'] = Token::type(Token::get($right_token));
                                continue;
                            }
                            $right_token_value = Token::value($right_token);
                            if($right_token_value!== null){
                                if(!isset($right_token_record['type'])){
                                    $right_token_record['type'] = Token::get($right_token);
                                }
                                elseif($right_token_record['type'] != Token::get($right_token)){
                                    var_dump('found a difficulty');
                                }
                                if(!isset($right_token_record['value'])){
                                    $right_token_record['value'] = $right_token_value;
                                } else {
                                    $right_token_record['value'] .= $right_token_value;
                                }

                                $right_token_record= Token::record($right_token_record);
                                $record['right_token_record'] = $right_token_record;
                            }
                        }
                        $result[] = $record;
                        $record = array();
                        continue;
                    }
                }
            }
            if(isset($record['is_cast']) && Token::is_whitespace($token)){
                if(isset($record['value'])){
                    $result[] = $record;
                    $record = array();
                }
                continue;
            }
            $value = Token::value($token);
            if($value === null){
            } else {
                if(!isset($record['value'])){
                    $record['type'] = Token::get($token);
                    $record['value'] = $value;
                } else {
                    if($record['type'] != Token::get($token)){
                        $record['type'] = Token::TYPE_MIXED;
                    }
                    $record['value'] .= $value;
                }
            }
            */
        /*
        if(!empty($record)){
            $result[] = $record;
        }
        foreach($result as $nr => $record){
            $result[$nr] = Token::record($record);
        }
        var_dump('__RESULT_IN_PARSE________________________');
        var_Export($result);
        return $result;
        */
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
        elseif(isset($token['type']) && $token['type'] == Token::TYPE_OPERATOR){
            return true;
        }
        return false;
    }

    public static function operator($parse=array()){
        foreach($parse as $nr => $record){
            if(Token::is_operator($record)){
                var_dump('HAS_OPERATOR IN PARSE');
                return $record;
            }
        }
        return array();
    }

    public static function has_operator($parse=array()){
        foreach($parse as $nr => $record){
            if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
                return true;
            }
        }

        return false;
    }

    public static function left($parse=array(), $operator=null){
        var_dump('__IN_LEFT___________________________');
        $left = array();
        foreach($parse as $nr => $record){
            if($record['type'] == $operator['type']){
                break;
            }
            $left[] = $record;
        }
        $operator['parse_left'] = $left;
        $operator['left'] = Token::get($left);
        /*
        foreach($record['left'] as $left_nr => $left_token){
            if(Token::is_operator($left_token)){
                $record['operator'] = $left_token[1];
                continue;
            }
            if(Token::is_cast($left_token)){
                $left_token_record['is_cast'] = true;
                $left_token_record['cast'] = Token::type(Token::get($left_token));
                continue;
            }
            $left_token_value = Token::value($left_token);
            if($left_token_value!== null){
                if(!isset($left_token_record['type'])){
                    $left_token_record['type'] = Token::get($left_token);
                }
                elseif($left_token_record['type'] != Token::get($left_token)){
                    var_dump('found a difficulty');
                }
                if(!isset($left_token_record['value'])){
                    $left_token_record['value'] = $left_token_value;
                } else {
                    $left_token_record['value'] .= $left_token_value;
                }

                $left_token_record = Token::record($left_token_record);
                $record['left_token_record'] = $left_token_record;
            }
        }
        */


        return $operator;
    }

    public static function right($parse=array(), $operator=null){
        var_dump('__IN_RIGHT___________________________');
        $right = array();
        $start = false;
        foreach($parse as $nr => $record){
            if($record['type'] == $operator['type']){
                $start = true;
                continue;
            }
            if($start === true){
                $right[] = $record;
            }
        }
        $operator['parse_right'] = $right;
        $operator['right'] = Token::get($right);
        return $operator;
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
            default:
                var_dump('_UNKNOWN_CAST_____________________________________');
                var_dump($record);
                break;
        }
    }

    public static function get($token=array()){
        if(isset($token[2])){
            return $token[2];
        } else {
            var_dump('__IN_GET______________________________');
            var_export($token);
        }
    }

    public static function record($record=array()){
        if(!isset($record['type'])){
            return $record;
        }
        if($record['type'] == Token::TYPE_VARIABLE){
            //need data
            var_dump('__IS_VARIABLE');
        }
//         $record['type'] = Token::type($record['type']);
        var_dump('__IN_RECORD______________________________');
        var_dump($record);
        return $record;
    }


    public static function type($type=null){
        switch ($type){
            case 'T_STRING_CAST' :
            case 'T_STRING' :
            case 'T_CONSTANT_ENCAPSED_STRING' :
            case 'T_ENCAPSED_AND_WHITESPACE' :
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
                return  Token::TYPE_OPERATOR;
            break;
            case 'T_VARIABLE' :
                return Token::TYPE_VARIABLE;
            break;
            case 'mixed' :
                return $type;
            break;

            default :
                var_dump('UNDEFINED_TYPE_________________________________________________');
                var_dump($type);
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
        ))){
            return $token[1];
        }
        //var_Dump($token);
        return null;
    }

    public static function string($tokens=array()){
        $string = '';
        foreach ($tokens as $nr=> $token){
            var_dump($token);
            if(is_bool($token[1])){
                var_dump('string.boolean-----------------------------');
            }
            elseif(is_numeric($token[1])){
                var_dump('string.numeric-----------------------------');
            }
            $string .= $token[1];
        }
        return $string;
    }
}



