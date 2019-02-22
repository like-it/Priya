<?php

namespace Priya\Module\Parse;

class Token extends Core {
    const TYPE_NULL = 'null';
    const TYPE_STRING = 'string';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_BOOLEAN_AND = 'boolean-and';
    const TYPE_BOOLEAN_OR = 'boolean-or';
    const TYPE_INT = 'integer';
    const TYPE_OCT = 'octal';
    const TYPE_HEX = 'hexadecimal';
    const TYPE_FLOAT = 'float';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_VARIABLE = 'variable';
    const TYPE_OPERATOR = 'operator';
    const TYPE_DOT = 'dot';
    const TYPE_COLON = 'colon';
    const TYPE_DOUBLE_COLON = 'double-colon';
    const TYPE_DOUBLE_ARROW = 'double-arrow';
    const TYPE_AS = 'as';
    const TYPE_SEMI_COLON = 'semi-colon';
    const TYPE_COMMA = 'comma';
    const TYPE_MIXED = 'mixed';
    const TYPE_WHITESPACE = 'whitespace';
    const TYPE_STATEMENT = 'statement';
    const TYPE_PARENTHESE = 'parenthese';
    const TYPE_BRACKET = 'bracket';
    const TYPE_NUMBER = 'number';
    const TYPE_SET = 'set';
    const TYPE_OPEN = 'open';
    const TYPE_CLOSE = 'close';
    const TYPE_METHOD = 'method';
    const TYPE_EXCLAMATION = 'exclamation';
    const TYPE_CONTROL = 'control';
    const TYPE_WHILE = 'while';
    const TYPE_QUOTE_SINGLE = 'quote-single';
    const TYPE_QUOTE_DOUBLE = 'quote-double';
    const TYPE_BACKSLASH = 'backslash';
    const TYPE_BRACKET_SQUARE_OPEN = 'bracket-square-open';
    const TYPE_BRACKET_SQUARE_CLOSE = 'bracket-square-close';
    const TYPE_CURLY_OPEN = 'curly-open';
    const TYPE_CURLY_CLOSE = 'curly-close';
    const TYPE_PARENTHESE_OPEN = 'parenthese-open';
    const TYPE_PARENTHESE_CLOSE = 'parenthese-close';
    const TYPE_COMMENT_OPEN = 'comment-open';
    const TYPE_COMMENT_CLOSE = 'comment-close';
    const TYPE_DOC_COMMENT_OPEN = 'doc-comment-open';
    const TYPE_COMMENT_SINGLE_LINE = 'comment-single-line';
    const TYPE_COMMENT = 'comment';
    const TYPE_DOC_COMMENT = 'doc-comment';
    const TYPE_AMPERSAND = 'ampersand';
    const TYPE_QUESTION = 'question';
    const TYPE_PIPE = 'pipe';
    const TYPE_IS_OBJECT_OPERATOR = 'is-object-operator';
    const TYPE_IS_ARRAY_OPERATOR = 'is-array-operator';
    const TYPE_IS_EQUAL = 'is-equal';
    const TYPE_IS_NOT_EQUAL = 'is-not-equal';
    const TYPE_IS_GREATER_EQUAL = 'is-greater-equal';
    const TYPE_IS_SMALLER_EQUAL = 'is-smaller-equal';
    const TYPE_IS_GREATER = 'is-greater';
    const TYPE_IS_SMALLER = 'is-smaller';
    const TYPE_IS_IDENTICAL = 'is-identical';
    const TYPE_IS_NOT_IDENTICAL = 'is-not-identical';
    const TYPE_IS_GREATER_GREATER_EQUAL = 'is-greater-greater-equal';
    const TYPE_IS_SMALLER_SMALLER_EQUAL = 'is-smaller-smaller-equal';
    const TYPE_IS_GREATER_GREATER = 'is-greater-greater';
    const TYPE_IS_SMALLER_SMALLER = 'is-smaller-smaller';
    const TYPE_IS_GREATER_GREATER_GREATER = 'is-greater-greater-greater';
    const TYPE_IS_SMALLER_SMALLER_SMALLER = 'is-smaller-smaller-smaller';
    const TYPE_IS = 'is';
    const TYPE_IS_PLUS_EQUAL = 'is-plus-equal';
    const TYPE_IS_MINUS_EQUAL = 'is-minus-equal';
    const TYPE_IS_MULTIPLY_EQUAL = 'is-multiply-equal';
    const TYPE_IS_DIVIDE_EQUAL = 'is-divide-equal';
    const TYPE_IS_OR_EQUAL = 'is-or-equal';
    const TYPE_IS_MODULO_EQUAL = 'is-modulo-equal';
    const TYPE_IS_POWER_EQUAL = 'is-power-equal';
    const TYPE_IS_XOR_EQUAL = 'is-xor-equal';
    const TYPE_IS_AND_EQUAL = 'is-and-equal';
    const TYPE_IS_PLUS = 'is-plus';
    const TYPE_IS_MINUS = 'is-minus';
    const TYPE_IS_MULTIPLY = 'is-multiply';
    const TYPE_IS_DIVIDE = 'is-divide';
    const TYPE_IS_MODULO = 'is-modulo';
    const TYPE_IS_PLUS_PLUS = 'is-plus-plus';
    const TYPE_IS_MINUS_MINUS = 'is-minus-minus';
    const TYPE_IS_SPACESHIP = 'is-spaceship';
    const TYPE_IS_POWER = 'is-power';
    const TYPE_IS_COALESCE = 'is-coalesce';

    const LITERAL_OPEN = '{literal}';
    const LITERAL_CLOSE = '{/literal}';

    const TYPE_SINGLE = [
        Token::TYPE_PARENTHESE_OPEN,
        Token::TYPE_PARENTHESE_CLOSE,
        Token::TYPE_BRACKET_SQUARE_OPEN,
        Token::TYPE_BRACKET_SQUARE_CLOSE,
        Token::TYPE_CURLY_OPEN,
        Token::TYPE_CURLY_CLOSE,
        Token::TYPE_DOT,
        Token::TYPE_COMMA,
        Token::TYPE_SEMI_COLON
    ];

    const TYPE_ASSIGN = [
        '=',
        '+=',
        '-=',
        '*=',
        '%=',
        '/=',
        '++',
        '--',
        '**',
        '**=',
        '^='.
        '&=',
        '|='
    ];

    public static function all($string=''){
        $array = Token::split($string);
        $token = array();
        $row = 1;
        $column = 1;
        foreach($array as $nr => $char){
            $type = Token::type($char);
            $record = [];
            $record['value'] = $char;
            $record['type'] = $type;
            $record['column'] = $column;
            $record['row'] = $row;
            $record['is_operator'] = false;
            $token[$nr] = $record;
            $column++;
            if($record['value'] == "\n"){
                $row++;
                $column = 1;
            }
        }
        $count = $nr + 1;
        $previous_nr = null;
        $skip = 0;
        foreach($token as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            $operator = null;
            $check = null;
            $check2 = null;
            $next = null;
            $next_next = null;
            if($nr < ($count - 1)){
                $next = $nr + 1;
            }
            if($nr < ($count - 2)){
                $next_next = $nr + 2;
            }
            if(
                in_array(
                    $record['type'],
                    Token::TYPE_SINGLE
                )
            ){
                //1
                $previous_nr = $nr;
                continue;
            }
            elseif(
                $next !== null &&
                $next_next !== null &&
                $record['type'] == $token[$next]['type'] &&
                $record['type'] == $token[$next_next]['type']
            ){
                //3
                if($record['type'] == Token::TYPE_OPERATOR){
                    $operator = $record;
                    $operator['value'] .= $token[$next]['value'] . $token[$next_next]['value'];
                    $operator = Token::operator($operator, 3);
                    if($operator['type'] == Token::TYPE_OPERATOR){
                        $operator['value'] = $record['value'] . $token[$next]['value'];
                        $operator = Token::operator($operator, 2);
                        if($operator['type'] == Token::TYPE_OPERATOR){
                            $operator = $record;
                            $operator['value'] = $record['value'];
                            $operator = Token::operator($operator, 1);
                            $check = $record;
                            $check['value'] = $token[$next]['value'] . $token[$next_next]['value'];
                            $check = Token::operator($check, 2);
                            if($check['type'] == Token::TYPE_OPERATOR){
                                $check['value'] = $token[$next]['value'];
                                $check2 = $record;
                                $check2['value'] = $token[$next_next]['value'];
                                $check = Token::operator($check, 1);
                                $check2 = Token::operator($check2, 1);
                            }
                        } else {
                            $check = $record;
                            $check['value'] = $token[$next]['value'] . $token[$next_next]['value'];
                            $check = Token::operator($check, 2);
                            if($check['type'] == Token::TYPE_OPERATOR){
                                $check['value'] = $token[$next_next]['value'];
                                $check = Token::operator($check, 1);
                            } else {
                                if(
                                    $check['type'] == Token::TYPE_COMMENT_CLOSE &&
                                    $operator['type'] == Token::TYPE_COMMENT
                                ){
                                    $check = $record;
                                    $check['value'] = $token[$next_next]['value'];
                                    $check = Token::operator($check, 1);
                                }
                                elseif(
                                    $check['type'] == Token::TYPE_COMMENT_CLOSE &&
                                    $operator['type'] == Token::TYPE_IS_POWER
                                ){
                                    $operator = $record;
                                    $operator['value'] = $record['value'];
                                    $operator = Token::operator($operator, 1);
                                } else {
                                    $check = $record;
                                    $check['value'] = $token[$next_next]['value'];
                                    $check = Token::operator($check, 1);
                                }
                            }
                        }
                        $token[$nr] = $operator;
                        $token[$next] = $check;
                        if($check2 === null){
                            unset($token[$next_next]);
                            $count--;
                            $skip = 2;
                        } else {
                            $token[$next_next] = $check2;
                        }
                        continue;
                    } else {
                        $token[$nr] = $operator;
                        unset($token[$next]);
                        unset($token[$next_next]);
                        $count -= 2;
                        $skip = 2;
                        continue;
                    }
                } else {
                    if($previous_nr !== null){
                        if($token[$previous_nr]['type'] == $record['type']){
                            $token[$previous_nr]['value'] .= $record['value'] . $token[$next]['value'] . $token[$next_next]['value'];
                            unset($token[$nr]);
                            unset($token[$next]);
                            unset($token[$next_next]);
                            $count -= 3;
                            $skip = 2;
                            continue;
                        }
                    }
                    $token[$nr]['value'] .= $token[$next]['value'] . $token[$next_next]['value'];
                    unset($token[$next]);
                    unset($token[$next_next]);
                    $previous_nr = $nr;
                    $count -= 2;
                    $skip = 2;
                }
            }
            elseif(
                $next !== null &&
                $next_next !== null &&
                $record['type'] == $token[$next]['type'] &&
                $record['type'] != $token[$next_next]['type']
            ){
                //2
                if($previous_nr !== null){
                    if($token[$previous_nr]['type'] == $record['type']){
                        $token[$previous_nr]['value'] .= $record['value'] . $token[$next]['value'];
                        unset($token[$nr]);
                        unset($token[$next]);
                        $count -= 2;
                        $skip = 1;
                        continue;
                    }
                }
                $token[$nr]['value'] .= $token[$next]['value'];
                if($record['type'] == Token::TYPE_OPERATOR){
                    $token[$nr] = Token::operator($token[$nr], 2);
                    if($token[$nr]['type'] == Token::TYPE_OPERATOR){
                        $token[$nr] = Token::operator($record, 1);
                        $token[$next] = Token::operator($token[$next], 1);
                        $previous_nr = $nr;
                        $skip = 1;
                        continue;
                    }
                }
                unset($token[$next]);
                $previous_nr = $nr;
                $count--;
                $skip = 1;
            }
            elseif(
                $next !== null &&
                $record['type'] == $token[$next]['type']
            ){
                //2
                if($previous_nr !== null){
                    if($token[$previous_nr]['type'] == $record['type']){
                        $token[$previous_nr]['value'] .= $record['value'] . $token[$next]['value'];
                        unset($token[$nr]);
                        unset($token[$next]);
                        $count -= 2;
                        $skip = 1;
                        continue;
                    }
                }
                $token[$nr]['value'] .= $token[$next]['value'];
                if($record['type'] == Token::TYPE_OPERATOR){
                    $token[$nr] = Token::operator($token[$nr], 2);
                    if($token[$nr]['type'] == Token::TYPE_OPERATOR){
                        $token[$nr] = Token::operator($record, 1);
                        $token[$next] = Token::operator($token[$next], 1);
                        $previous_nr = $nr;
                        $skip = 1;
                        continue;
                    }
                }
                unset($token[$next]);
                $previous_nr = $nr;
                $count--;
                $skip = 1;
            } else {
                //1
                if($previous_nr !== null){
                    if($token[$previous_nr]['type'] == $record['type']){
                        $token[$previous_nr]['value'] .= $record['value'];
                        unset($token[$nr]);
                        $count--;
                        continue;
                    }
                }
                if($record['type'] == Token::TYPE_OPERATOR){
                    $token[$nr] = Token::operator($record, 1);
                }
                $previous_nr = $nr;
            }
        }
        $prepare = [];
        foreach($token as $nr => $record){
            $prepare[] = $record;
            unset($token[$nr]);
        }
        unset($token);
        $prepare = Token::prepare($prepare, $count);
        return $prepare;
    }

    public static function prepare($token=array(), $count=0){
        $hex = null;
        $start = null;
        $skip = 0;
        $skip_unset = 0;
        $depth = 0;
        $quote_single = null;
        $quote_single_toggle = false;
        $quote_double = null;
        $quote_double_toggle = false;
        $variable_nr = null;
        $comment_open_nr = null;
        $doc_comment_open_nr = null;
        $comment_single_line_nr = null;
        foreach($token as $nr => $record){
            $token[$nr]['depth']  = $depth;
            $next = null;
            $next_next = null;
            if($skip > 0){
                $skip--;
                continue;
            }
            if($skip_unset > 0){
                unset($token[$nr]);
                $skip_unset--;
                continue;
            }
            if($nr < ($count - 1)){
                $next = $nr + 1;
            }
            if($nr < ($count - 2)){
                $next_next = $nr + 2;
            }
            if(
                $record['type'] == Token::TYPE_COMMENT_CLOSE &&
                $quote_single_toggle === false
            ){
                if($comment_open_nr !== null){
                    $token[$comment_open_nr]['value'] .= $record['value'];
                    $comment_open_nr = null;
                    unset($token[$nr]);
                    continue;
                }
                elseif($doc_comment_open_nr !== null){
                    $token[$doc_comment_open_nr]['value'] .= $record['value'];
                    $doc_comment_open_nr = null;
                    unset($token[$nr]);
                    continue;
                }
            }
            elseif($comment_open_nr !== null){
                $token[$comment_open_nr]['value'] .= $record['value'];
                unset($token[$nr]);
                continue;
            }
            elseif($doc_comment_open_nr !== null){
                $token[$doc_comment_open_nr]['value'] .= $record['value'];
                unset($token[$nr]);
                continue;
            }
            elseif($comment_single_line_nr !== null){
                if(
                    $record['type'] == Token::TYPE_WHITESPACE &&
                    stristr($record['value'], "\n") !== false
                ){
                    $comment_single_line_nr = null;
                } else {
                    $token[$comment_single_line_nr]['value'] .= $record['value'];
                    unset($token[$nr]);
                    continue;
                }
            }
            elseif($variable_nr !== null){
                if(
                    $next !== null &&
                    $token[$next]['is_operator'] === true
                ){
                    if($token[$next]['value'] == '|'){
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $token[$variable_nr]['variable']['has_modifier'] = true;
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        continue;
                    }
                    elseif(
                        in_array(
                            $token[$next]['value'],
                            Token::TYPE_ASSIGN
                        )
                    ){
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['has_modifier'] = false;
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        continue;
                    }
                }
                if(
                    $next !== null &&
                    $next_next !== null &&
                    $token[$next]['type'] === Token::TYPE_WHITESPACE &&
                    $token[$next_next]['is_operator'] === true
                ){
                    if($token[$next_next]['value'] == '|'){
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $token[$variable_nr]['variable']['has_modifier'] = true;
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        continue;
                    }
                    elseif(
                        in_array(
                            $token[$next_next]['value'],
                            Token::TYPE_ASSIGN
                        )
                    ){
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['has_modifier'] = false;
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $variable_nr = null;
                        $skip += 2;
                        unset($token[$nr]);
                        continue;
                    }
                }
                elseif(
                    in_array(
                        $record['type'],
                        [
                            Token::TYPE_WHITESPACE,
                            Token::TYPE_QUOTE_SINGLE,
                            Token::TYPE_QUOTE_DOUBLE
                        ]
                    ) ||
                    $record['is_operator'] === true
                ){
                    $variable_nr = null;
                } else {
                    $token[$variable_nr]['variable']['name'] .= $record['value'];
                    $skip += 1;
                    unset($token[$nr]);
                    continue;
                }
            }
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $quote_single_toggle === false
            ){
                $variable_nr = $nr;
                $token[$variable_nr]['variable']['name'] = $record['value'];
                $token[$variable_nr]['variable']['is_assign'] = false;
            }
            elseif(
                $record['type'] == Token::TYPE_COMMENT &&
                $quote_single_toggle === false
            ){
                $comment_open_nr = $nr;
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_DOC_COMMENT &&
                $quote_single_toggle === false
            ){
                $doc_comment_open_nr = $nr;
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_COMMENT_SINGLE_LINE &&
                $quote_single_toggle === false
            ){
                $comment_single_line_nr = $nr;
                continue;
            }
            if($record['value'] == '\'' && $quote_double_toggle === false){
                if($quote_single_toggle === false){
                    $quote_single_toggle = true;
                } else {
                    $quote_single_toggle = false;
                }
            }
            elseif($record['value'] == '"' && $quote_single_toggle === false){
                if($quote_double_toggle === false){
                    $quote_double_toggle = true;
                } else {
                    $quote_double_toggle = false;
                }
            }
            if($quote_single_toggle){
                if($quote_single === null){
                    $quote_single = $record;
                    $quote_single['nr'] = $nr;
                    continue;
                }
                if($record['value'] == '\\' && $next !== null && $token[$next]['value'] == '\''){
                    $quote_single['value'] .= $record['value'] . $token[$next]['value'];
                    $skip += 1;
                    continue;
                } else {
                    $quote_single['value'] .= $record['value'];
                    continue;
                }
            } else {
                if($quote_single){
                    $quote_single['value'] .= $record['value'];
                    $token[$quote_single['nr']]['type'] = Token::TYPE_STRING;
                    $token[$quote_single['nr']]['value'] = $quote_single['value'];
                    for($i = ($quote_single['nr'] + 1); $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $token[$quote_single['nr']]['execute'] = str_replace('\\\'','\'', substr($token[$quote_single['nr']]['value'], 1, -1));
                    $quote_single = null;
                    continue;
                }
            }
            if($quote_double_toggle){
                if($quote_double === null){
                    $quote_double = $record;
                    $quote_double['nr'] = $nr;
                    continue;
                }
                if($record['value'] == '\\' && $next !== null && $token[$next]['value'] == '"'){
                    $skip += 1;
                }
            } else {
                if($quote_double){
                    $quote_double = null;
                }
            }
            if($record['type'] == Token::TYPE_STRING){
                if($record['value'] == 'null'){
                    $token[$nr]['execute'] = null;
                    $token[$nr]['type'] = Token::TYPE_NULL;
                }
                elseif($record['value'] == 'true'){
                    $token[$nr]['execute'] = true;
                    $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                }
                elseif($record['value'] == 'false'){
                    $token[$nr]['execute'] = false;
                    $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                }
            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_OPEN){
                $depth++;
                $token[$nr]['depth'] = $depth;
                $parenthese_open = $nr;
            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_CLOSE){
                $depth--;
                if($parenthese_open !== null){
                    $before_reverse = [];
                    $collect = false;
                    for($i = $parenthese_open - 1; $i >= 0; $i--){
                        if(isset($token[$i])){
                            if(
                                $collect === true &&
                                (
                                    $token[$i]['type'] == Token::TYPE_WHITESPACE ||
                                    $token[$i]['is_operator'] === true
                                )
                            ){
                                $token[$previous_nr]['type'] = Token::TYPE_METHOD;
                                $token[$previous_nr]['method']['name'] = trim(implode('', array_reverse($before_reverse)));
                                $before_reverse = [];
                                $collect = false;
                                $parenthese_open = null;
                                break;
                            }
                            elseif($token[$i]['type'] != Token::TYPE_WHITESPACE){
                                if(
                                    in_array(
                                        $token[$i]['type'],
                                        [
                                            Token::TYPE_PARENTHESE_OPEN,
                                            Token::TYPE_PARENTHESE_CLOSE,
                                        ]
                                    ) ||
                                    $token[$i]['is_operator'] === true
                                ){
                                    $before_reverse = [];
                                    break;
                                } else {
                                    $collect = true;
                                }
                            }
                            $previous_nr = $i;
                            $before_reverse[] = $token[$i]['value'];
                        }
                    }
                    if($parenthese_open !== null){
                        $cast = '';
                        for($i = $parenthese_open + 1; $i < $count; $i++){
                            if($token[$i]['type'] == Token::TYPE_PARENTHESE_CLOSE){
                                $cast = strtolower($cast);
                                switch($cast){
                                    case 'bool' :
                                    case 'boolean' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_BOOLEAN;
                                    break;
                                    case 'float' :
                                    case 'double' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_FLOAT;
                                    break;
                                    case 'int' :
                                    case 'integer' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_INT;
                                    break;
                                    case 'string' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_STRING;
                                    break;
                                    case 'array' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_ARRAY;
                                    break;
                                    case 'object' :
                                        $token[$parenthese_open]['cast'] = Token::TYPE_OBJECT;
                                    break;
                                }
                                break;
                            }
                            elseif($token[$i]['type'] != Token::TYPE_WHITESPACE){
                                $cast .= $token[$i]['value'];
                            }
                        }
                    }
                    $parenthese_open = null;
                }
            }
            if($hex){
                $is_hex = Token::is_hex($record);
                if($is_hex){
                    $hex['value'] .= $record['value'];
                    $hex['execute'] .= strtoupper($record['value']);
                    unset($token[$nr]);
                    continue;
                } else {
                    $token[$start] = $hex;
                    $start = null;
                    $hex = null;
                }
            }
            if(
                $record['type'] == Token::TYPE_NUMBER &&
                $next !== null &&
                $next_next !== null &&
                $token[$next]['type'] == Token::TYPE_DOT &&
                $token[$next_next]['type'] == Token::TYPE_NUMBER
            ){
                $token[$nr]['value'] .= $token[$next]['value'] . $token[$next_next]['value'];
                $token[$nr]['type'] = Token::TYPE_FLOAT;
                $token[$nr]['execute'] = $token[$nr]['value'] + 0;
                $skip_unset += 2;
                continue;
            }
            elseif(
                $record['value'] == '0' &&
                $next !== null &&
                $token[$next]['type'] == Token::TYPE_STRING &&
                strtolower(substr($token[$next]['value'], 0, 1)) == 'x'
            ){
                $hex = $record;
                $hex['value'] .= substr($token[$next]['value'], 0, 1);
                $hex['execute'] = $record['value'] . 'x';
                $tmp = $token[$next];
                $tmp['value'] = substr($token[$next]['value'], 1);
                if(!empty($tmp['value'])){
                    $is_hex = Token::is_hex($tmp);
                    if($is_hex){
                        $hex['value'] .= $tmp['value'];
                        $hex['execute'] .= strtoupper($tmp['value']);
                        $hex['type'] = Token::TYPE_HEX;
                        $skip_unset += 1;
                        continue;
                    } else {
                        $hex = null;
                    }
                }
                if($hex){
                    $start = $nr;
                }
            }
            elseif(
                $record['type'] == Token::TYPE_NUMBER &&
                substr($record['value'], 0, 1) == '0' &&
                strlen($record['value']) > 1
            ){
                //octal
                $token[$nr]['execute'] = $record['value'];
                $token[$nr]['type'] = Token::TYPE_OCT;
            } elseif(
                $record['type'] == Token::TYPE_NUMBER
            ) {
                //int
                $token[$nr]['execute'] = $record['value'] + 0;
                $token[$nr]['type'] = Token::TYPE_INT;
            }
        }
        return $token;
    }

    public static function is_hex($record=array()){
        if(empty($record['value'])){
            return false;
        }
        $array = Token::split($record['value']);
        foreach($array as $nr => $char){
            $is_hex = false;
            switch ($char){
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                case 'A':
                case 'B':
                case 'C':
                case 'D':
                case 'E':
                case 'F':
                    $is_hex = true;
                    break;
            }
            if(!$is_hex){
                return false;
            }
        }
        return true;
    }

    public static function operator($record=array(), $level=1){
        if($record['type'] != Token::TYPE_OPERATOR){
            return $record;
        }
        $record['is_operator'] = true;
        switch($level){
            case 1 :
                switch($record['value']){
                    case '=' :
                        $record['type'] = Token::TYPE_IS;
                        return $record;
                    case '+' :
                        $record['type'] = Token::TYPE_IS_PLUS;
                        return $record;
                    case '-' :
                        $record['type'] = Token::TYPE_IS_MINUS;
                        return $record;
                    case '*' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY;
                        return $record;
                    case '/' :
                        $record['type'] = Token::TYPE_IS_DIVIDE;
                        return $record;
                    case '%' :
                        $record['type'] = Token::TYPE_IS_MODULO;
                        return $record;
                    case '>' :
                        $record['type'] = Token::TYPE_IS_GREATER;
                        return $record;
                    case '<' :
                        $record['type'] = Token::TYPE_IS_SMALLER;
                        return $record;
                    case ':' :
                        $record['type'] = Token::TYPE_COLON;
                        return $record;
                    case '!' :
                        $record['type'] = Token::TYPE_EXCLAMATION;
                        return $record;
                    case '?' :
                        $record['type'] = Token::TYPE_QUESTION;
                        return $record;
                    case '&' :
                        $record['type'] = Token::TYPE_AMPERSAND;
                        return $record;
                    case '|' :
                        $record['type'] = Token::TYPE_PIPE;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
            case 2 :
                switch($record['value']){
                    case '==' :
                        $record['type'] = Token::TYPE_IS_EQUAL;
                        return $record;
                    case '!=' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        return $record;
                    case '=>' :
                        $record['type'] = Token::TYPE_IS_ARRAY_OPERATOR;
                        return $record;
                    case '->' :
                        $record['type'] = Token::TYPE_IS_OBJECT_OPERATOR;
                        return $record;
                    case '<=' :
                        $record['type'] = Token::TYPE_IS_SMALLER_EQUAL;
                        return $record;
                    case '>=' :
                        $record['type']= Token::TYPE_IS_GREATER_EQUAL;
                        return $record;
                    case '<>' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        return $record;
                    case '+=' :
                        $record['type'] = Token::TYPE_IS_PLUS_EQUAL;
                        return $record;
                    case '-=' :
                        $record['type'] = Token::TYPE_IS_MINUS_EQUAL;
                        return $record;
                    case '*=' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY_EQUAL;
                        return $record;
                    case '/=' :
                        $record['type'] = Token::TYPE_IS_DIVIDE_EQUAL;
                        return $record;
                    case '%=' :
                        $record['type'] = Token::TYPE_IS_MODULO_EQUAL;
                        return $record;
                    case '^=' :
                        $record['type'] = Token::TYPE_IS_XOR_EQUAL;
                        return $record;
                    case '&=' :
                        $record['type'] = Token::TYPE_IS_AND_EQUAL;
                        return $record;
                    case '|=' :
                        $record['type'] = Token::TYPE_IS_OR_EQUAL;
                        return $record;
                    case '<<' :
                        $record['type'] = Token::TYPE_IS_SMALLER_SMALLER;
                        return $record;
                    case '>>' :
                        $record['type'] = Token::TYPE_IS_GREATER_GREATER;
                        return $record;
                    case '++' :
                        $record['type'] = Token::TYPE_IS_PLUS_PLUS;
                        return $record;
                    case '--' :
                        $record['type'] = Token::TYPE_IS_MINUS_MINUS;
                        return $record;
                    case '**' :
                        $record['type'] = Token::TYPE_IS_POWER;
                        return $record;
                    case '::' :
                        $record['type'] = Token::TYPE_DOUBLE_COLON;
                        return $record;
                    case '&&' :
                        $record['type'] = Token::TYPE_BOOLEAN_AND;
                        return $record;
                    case '||' :
                        $record['type'] = Token::TYPE_BOOLEAN_OR;
                        return $record;
                    case '??' :
                        $record['type'] = Token::TYPE_COALESCE;
                        return $record;
                    case '//' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_COMMENT_SINGLE_LINE;
                        return $record;
                    case '/*' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_COMMENT;
                        return $record;
                    case '*/' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_COMMENT_CLOSE;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
            case 3 :
                switch($record['value']){
                    case '===' :
                        $record['type'] = Token::TYPE_IS_IDENTICAL;
                        return $record;
                    case '!==' :
                        $record['type'] = Token::TYPE_IS_NOT_IDENTICAL;
                        return $record;
                    case '/**' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_DOC_COMMENT;
                        return $record;
                    case '<=>' :
                        $record['type'] = Token::TYPE_IS_SPACESHIP;
                        return $record;
                    case '<<<' :
                        $record['type'] = Token::TYPE_IS_SMALLER_SMALLER_SMALLER;
                        return $record;
                    case '>>>' :
                        $record['type'] = Token::TYPE_IS_GREATER_GREATER_GREATER;
                        return $record;
                    case '<<=' :
                        $record['type'] = Token::TYPE_IS_SMALLER_SMALLER_EQUAL;
                        return $record;
                    case '>>=' :
                        $record['type'] = Token::TYPE_IS_GREATER_GREATER_EQUAL;
                        return $record;
                    case '**=' :
                        $record['type'] = Token::TYPE_IS_POWER_EQUAL;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
        }
        return $record;
    }

    public function type($char=null){
        switch($char){
            case '.' :
                return Token::TYPE_DOT;
            break;
            case ',' :
                return Token::TYPE_COMMA;
            break;
            case '(' :
                return Token::TYPE_PARENTHESE_OPEN;
                break;
            case ')' :
                return Token::TYPE_PARENTHESE_CLOSE;
            break;
            case '[' :
                return Token::TYPE_BRACKET_SQUARE_OPEN;
            break;
            case ']' :
                return Token::TYPE_BRACKET_SQUARE_CLOSE;
            break;
            case '{' :
                return Token::TYPE_CURLY_OPEN;
            break;
            case '}' :
                return Token::TYPE_CURLY_CLOSE;
            break;
            case '$' :
                return Token::TYPE_VARIABLE;
            break;
            case '\'' :
                return Token::TYPE_QUOTE_SINGLE;
            break;
            case '"' :
                return Token::TYPE_QUOTE_DOUBLE;
            break;
            case '\\' :
                return Token::TYPE_BACKSLASH;
                break;
            case ';' :
                return Token::TYPE_SEMI_COLON;
            break;
            case '0' :
            case '1' :
            case '2' :
            case '3' :
            case '4' :
            case '5' :
            case '6' :
            case '7' :
            case '8' :
            case '9' :
                return Token::TYPE_NUMBER;
            break;
            case '>' :
            case '<' :
            case '=' :
            case '-' :
            case '+' :
            case '/' :
            case '*' :
            case '%' :
            case '^' :
            case '!' :
            case '?' :
            case '|' :
            case '&' :
            case ':' :
                return Token::TYPE_OPERATOR;
            break;
            case ' ' :
            case "\t" :
            case "\n" :
            case "\r" :
                return Token::TYPE_WHITESPACE;
            break;
            default:
                return Token::TYPE_STRING;
            break;
        }
    }

    public static function split($string='', $length=1, $encoding='UTF-8') {
        $array = [];
        $strlen = mb_strlen($string);
        for($i=0; $i<$strlen; $i=$i+$length){
            $array[] = mb_substr($string, $i, $length, $encoding);
        }
        return $array;
    }
}