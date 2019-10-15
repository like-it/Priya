<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Priya\Module\File;
use Exception;

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
    const TYPE_METHOD = 'method';
    const TYPE_FUNCTION = 'function';
    const TYPE_MODIFIER = 'modifier';
    const TYPE_CLASS = 'class';
    const TYPE_TRAIT = 'trait';
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
    const TYPE_LITERAL = 'tag-literal';
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
    const TYPE_IS_GREATER_GREATER = 'is-greater-greater';
    const TYPE_IS_SMALLER_SMALLER = 'is-smaller-smaller';
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
    const TYPE_REM = 'rem';
    const TYPE_CAST = 'cast';
    const LITERAL_OPEN = '{literal}';
    const LITERAL_CLOSE = '{/literal}';
    const TYPE_TAG_CLOSE = 'tag-close';

    const DIRECTION_LTR = 'ltr';
    const DIRECTION_RTL = 'rtl';

    const MODIFIER_DIRECTION = 'direction';

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

    const TYPE_NAME_BREAK = [
        Token::TYPE_WHITESPACE,
        Token::TYPE_PARENTHESE_OPEN,
        Token::TYPE_PARENTHESE_CLOSE,
        Token::TYPE_BRACKET_SQUARE_OPEN,
        Token::TYPE_BRACKET_SQUARE_CLOSE,
        Token::TYPE_CURLY_OPEN,
        Token::TYPE_CURLY_CLOSE,
        Token::TYPE_QUOTE_SINGLE,
        Token::TYPE_QUOTE_DOUBLE,
        Token::TYPE_COMMA,
        Token::TYPE_SEMI_COLON,
        Token::TYPE_COLON,
        Token::TYPE_DOUBLE_COLON,
    ];

    const TYPE_STRING_BREAK = [
        Token::TYPE_METHOD,
        Token::TYPE_VARIABLE,
        Token::TYPE_OPERATOR,
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
        $count_begin = $count;
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
            if($nr + 1 < $count_begin){
                $next = $nr + 1;
            }
            if($nr + 2 < $count_begin){
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
                        $previous_nr = $nr;
                        continue;
                    } else {
                        $token[$nr] = $operator;
                        unset($token[$next]);
                        unset($token[$next_next]);
                        $previous_nr = $nr;
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
//                 var_Dump($token[$nr]);
                $previous_nr = $nr;
            }
        }
        $prepare = [];
        foreach($token as $nr => $record){
            $prepare[] = $record;
            unset($token[$nr]);
        }
        $prepare = Token::prepare($prepare, $count);
        $token = [];
        foreach($prepare as $nr => $record){
            $token[$record['token']['nr']] = $record;
        }
        return $token;
    }

    public static function prepare($token=[], $count=0){
        $bug = false;
        $hex = null;
        $start = null;
        $skip = 0;
        $skip_unset = 0;
        $depth = 0;
        $parenthese_open = null;
        $quote_single = null;
        $quote_single_toggle = false;
        $quote_double = null;
        $quote_double_toggle = false;
        $previous_nr = null;
        $method_nr = null;
        $variable_nr = null;
        $value = null;
        $comment_open_nr = null;
        $doc_comment_open_nr = null;
        $comment_single_line_nr = null;
        $is_tag_close_nr = null;
        $tag_close = '';
        foreach($token as $nr => $record){
            $record['depth'] = $depth;
            $token[$nr]['depth'] = $depth;
            $next = null;
            $next_next = null;
            if($skip > 0){
                $skip--;
                $previous_nr = $nr;
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
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                if($comment_open_nr !== null){
                    $token[$comment_open_nr]['value'] .= $record['value'];
                    $comment_open_nr = null;
                    unset($token[$nr]);
                    $previous_nr = $comment_open_nr;
                    continue;
                }
                elseif($doc_comment_open_nr !== null){
                    $token[$doc_comment_open_nr]['value'] .= $record['value'];
                    $doc_comment_open_nr = null;
                    unset($token[$nr]);
                    $previous_nr = $doc_comment_open_nr;
                    continue;
                }
            }
            elseif($comment_open_nr !== null){
                $token[$comment_open_nr]['value'] .= $record['value'];
                unset($token[$nr]);
                $previous_nr = $comment_open_nr;
                continue;
            }
            elseif($doc_comment_open_nr !== null){
                $token[$doc_comment_open_nr]['value'] .= $record['value'];
                unset($token[$nr]);
                $previous_nr = $doc_comment_open_nr;
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
                    $previous_nr = $nr;
                    continue;
                }
            }
            elseif($is_tag_close_nr !== null){
                if(
                    in_array(
                        $record['type'],
                        Token::TYPE_NAME_BREAK
                    )
                ){
                    $token[$is_tag_close_nr]['tag']['name'] = strtolower($tag_close);
                    $is_tag_close_nr = null;
                } else {
                    $tag_close .= $record['value'];
                    $token[$is_tag_close_nr]['value'] .= $record['value'];
                    unset($token[$nr]);
                    $previous_nr = $is_tag_close_nr;
                    continue;

                }
            }
            elseif($variable_nr !== null){
                if(
                    in_array(
                        $record['type'],
                        [
                            Token::TYPE_PARENTHESE_OPEN, //used by modifier
                            Token::TYPE_PARENTHESE_CLOSE,
                            Token::TYPE_COMMA
                        ]
                    )
                ){
                    $variable_nr = null;
                }
                if(
                    $next !== null &&
                    $variable_nr !== null &&
                    $token[$next]['is_operator'] === true
                ){
                    if($token[$next]['value'] == '|'){
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $check_1 = $next_next + 1;
                        $check_2 = $next_next + 2;
                        if(
                            isset($token[$check_1]) &&
                            isset($token[$check_2]) &&
                            $token[$check_1]['type'] == Token::TYPE_WHITESPACE &&
                            $token[$check_2]['type'] == Token::TYPE_STRING
                        ){
                            $token[$variable_nr]['variable']['has_modifier'] = true;
                        }
                        elseif(
                            isset($token[$check_1]) &&
                            $token[$check_1]['type'] == Token::TYPE_STRING
                        ){
                            $token[$variable_nr]['variable']['has_modifier'] = true;
                        } else {
                            $token[$variable_nr]['variable']['has_modifier'] = false;
                        }
                        $token[$variable_nr]['value'] = $value;
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    }
                    elseif(
                        in_array(
                            $token[$next]['value'],
                            Token::TYPE_ASSIGN
                        )
                    ){
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $token[$variable_nr]['value'] = $value;
                        unset($token[$variable_nr]['variable']['has_modifier']);
                        $variable_nr = null;
                        $skip_unset += 1; //was skip
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    }
                }
                elseif(
                    $next !== null &&
                    $next_next !== null &&
                    $variable_nr !== null &&
                    $token[$next]['type'] === Token::TYPE_WHITESPACE &&
                    $token[$next_next]['is_operator'] === true
                ){
                    if($token[$next_next]['value'] == '|'){
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $check_1 = $next_next + 1;
                        $check_2 = $next_next + 2;
                        if(
                            isset($token[$check_1]) &&
                            isset($token[$check_2]) &&
                            $token[$check_1]['type'] == Token::TYPE_WHITESPACE &&
                            $token[$check_2]['type'] == Token::TYPE_STRING
                        ){
                            $token[$variable_nr]['variable']['has_modifier'] = true;
                        }
                        elseif(
                            isset($token[$check_1]) &&
                            $token[$check_1]['type'] == Token::TYPE_STRING
                        ){
                            $token[$variable_nr]['variable']['has_modifier'] = true;
                        } else {
                            $token[$variable_nr]['variable']['has_modifier'] = false;
                        }
                        $token[$variable_nr]['value'] = $value;
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    }
                    elseif(
                        in_array(
                            $token[$next_next]['value'],
                            Token::TYPE_ASSIGN
                        )
                    ){
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $token[$variable_nr]['value'] = $value;
                        unset($token[$variable_nr]['variable']['has_modifier']);
                        $variable_nr = null;
                        $skip += 2;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    } else {
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                        $token[$variable_nr]['value'] = $value;
                        unset($token[$variable_nr]['variable']['has_modifier']);
                        $variable_nr = null;
                        $skip += 2;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    }
                }
                elseif(
                    in_array(
                        $record['type'],
                        Token::TYPE_NAME_BREAK
                    ) ||
                    $record['is_operator'] === true
                ){
                    $variable_nr = null;
                }
                elseif($variable_nr !== null) {
                    $token[$variable_nr]['variable']['name'] .= $record['value'];
                    $token[$variable_nr]['variable']['attribute'] .= $record['value'];
                    $value .= $record['value'];
                    $token[$variable_nr]['value'] = $value;
                    unset($token[$nr]);
                    $previous_nr = $nr;
                    continue;
                }
            }
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                $variable_nr = $nr;
                $token[$variable_nr]['variable']['name'] = $record['value'];
                $token[$variable_nr]['variable']['attribute'] = substr($record['value'], 1);
                $token[$variable_nr]['variable']['is_assign'] = false;
                $value = $record['value'];
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_COMMENT &&
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                $comment_open_nr = $nr;
                $previous_nr = $nr;
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_DOC_COMMENT &&
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                $doc_comment_open_nr = $nr;
                $previous_nr = $nr;
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_COMMENT_SINGLE_LINE &&
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                $comment_single_line_nr = $nr;
                $previous_nr = $nr;
                continue;
            }
            elseif(
                $record['type'] == Token::TYPE_IS_DIVIDE &&
                $quote_single_toggle === false &&
                $quote_double_toggle === false
            ){
                $tag_close = $record['value'];
                if($token[$next]['type'] == Token::TYPE_STRING){
                    $is_tag_close_nr = $nr;
                    $tag_close .= $token[$next]['value'];
                    $token[$nr]['value'] .= $token[$next]['value'];
                    $token[$nr]['type'] = Token::TYPE_TAG_CLOSE;
                    $token[$nr]['is_operator'] = false;
                    $token[$nr]['tag']['name'] = strtolower($tag_close);
                    $previous_nr = $nr;
                    $skip_unset += 1;
                }
                elseif(
                    $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$next_next]['type'] == Token::TYPE_STRING
                ){
                    $is_tag_close_nr = $nr;
                    $tag_close .= $token[$next_next]['value'];
                    $token[$nr]['value'] .=
                        $token[$next]['value'] .
                        $token[$next_next]['value'];
                    $token[$nr]['type'] = Token::TYPE_TAG_CLOSE;
                    $token[$nr]['is_operator'] = false;
                    $token[$nr]['tag']['name'] = strtolower($tag_close);
                    $previous_nr = $nr;
                    $skip_unset += 2;
                }
            }
            if(
                $record['value'] == '\'' &&
                $quote_double_toggle === false
            ){
                if($quote_single_toggle === false){
                    $quote_single_toggle = true;
                } else {
                    $quote_single_toggle = false;
                }
            }
            elseif(
                $record['value'] == '"' &&
                $quote_single_toggle === false
            ){
                if($quote_double_toggle === false){
                    $quote_double_toggle = true;
                } else {
                    $quote_double_toggle = false;
                }
            }
            if($quote_single_toggle === true){
                if($quote_single === null){
                    $quote_single = $record;
                    $quote_single['nr'] = $nr;
                    $previous_nr = $nr;
                    continue;
                }
                if($record['value'] == '\\' && $next !== null && $token[$next]['value'] == '\''){
                    $quote_single['value'] .= $record['value'] . $token[$next]['value'];
                    $skip += 1;
                    $previous_nr = $nr;
                    continue;
                } else {
                    $quote_single['value'] .= $record['value'];
                    $previous_nr = $nr;
                    continue;
                }
            } else {
                if($quote_single !== null){
                    $quote_single['value'] .= $record['value'];
                    $token[$quote_single['nr']]['type'] = Token::TYPE_STRING;
                    $token[$quote_single['nr']]['value'] = $quote_single['value'];
                    for($i = ($quote_single['nr'] + 1); $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $token[$quote_single['nr']]['execute'] = str_replace(['\\\'', '\\\\'],['\'', '\\'], substr($token[$quote_single['nr']]['value'], 1, -1));
                    $token[$quote_single['nr']]['is_executed'] = true;
                    $token[$quote_single['nr']]['is_quote_single'] = true;
                    $quote_single = null;
                    $previous_nr = $nr;
                    continue;
                }
            }
            if($quote_double_toggle === true){
                if($quote_double === null){
                    $quote_double = $record;
                    $quote_double['nr'] = $nr;
                    $previous_nr = $nr;
                    continue;
                }
                if($record['value'] == '\\' && $next !== null && $token[$next]['value'] == '"'){
                    $skip += 1;
                    $previous_nr = $nr;
                    continue;
                } else {
                    $quote_double['value'] .= $record['value'];
                    $previous_nr = $nr;
                    continue;
                }
            } else {
                if($quote_double !== null){
                    $quote_double['value'] .= $record['value'];
                    $token[$quote_double['nr']]['type'] = Token::TYPE_STRING;
                    $token[$quote_double['nr']]['value'] = $quote_double['value'];
                    for($i = ($quote_double['nr'] + 1); $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $token[$quote_double['nr']]['execute'] = str_replace('\\"','"', substr($token[$quote_double['nr']]['value'], 1, -1));
                    $token[$quote_double['nr']]['is_executed'] = true;
                    $token[$quote_double['nr']]['is_quote_double'] = true;
                    $quote_double = null;
                    $previous_nr = $nr;
                    continue;
                }
            }
            if($record['type'] == Token::TYPE_STRING){
                if($record['value'] == 'null'){
                    $token[$nr]['execute'] = null;
                    $token[$nr]['is_executed'] = true;
                    $token[$nr]['type'] = Token::TYPE_NULL;
                }
                elseif($record['value'] == 'true'){
                    $token[$nr]['execute'] = true;
                    $token[$nr]['is_executed'] = true;
                    $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                }
                elseif($record['value'] == 'false'){
                    $token[$nr]['execute'] = false;
                    $token[$nr]['is_executed'] = true;
                    $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                }
            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_OPEN){
                $depth++;
            }
            $token[$nr]['depth'] = $depth;
            if($record['type'] == Token::TYPE_PARENTHESE_CLOSE){
                $depth--;
                $is_start_method = false;
                $is_whitespace = false;
                $before_reverse = [];
                for($i = $nr; $i >= 0; $i--){
                    if(isset($token[$i])){
                        if(
                            $token[$i]['type'] == Token::TYPE_PARENTHESE_OPEN &&
                            $token[$i]['depth'] == $token[$nr]['depth']
                        ){
                            $is_start_method = true;
                            continue;
                        }
                        if($is_start_method === false){
                            //catch parameter?
                        } else {
                            if(
                                $is_whitespace === false &&
                                !isset($before_reverse[0]) &&
                                $token[$i]['type'] == Token::TYPE_WHITESPACE
                            ){
                                $is_whitespace = true;
                                continue;
                            }
                            elseif(
                                in_array(
                                    $token[$i]['type'],
                                    Token::TYPE_NAME_BREAK
                                ) ||
                                $token[$i]['is_operator'] === true

                            ){
                                break;
                            }
                            $before_reverse[] = $token[$i]['value'];
                            $method_nr = $i;
                        }
                    }
                }
                if(
                    $method_nr !== null &&
                    isset($before_reverse[0])
                ){
                    $value = implode('', array_reverse($before_reverse));
                    $token[$method_nr]['type'] = Token::TYPE_METHOD;
                    $token[$method_nr]['method']['name'] = strtolower(trim($value));
//                     $token[$method_nr]['token']['parenthese_close_nr'] = $nr;
                }
            }
            if($hex){
                $is_hex = Token::is_hex($record);
                if($is_hex){
                    $hex['value'] .= $record['value'];
                    $hex['execute'] .= strtoupper($record['value']);
                    unset($token[$nr]);
                    $previous_nr = $nr;
                    continue;
                } else {
                    $token[$start] = $hex;
                    $start = null;
                    $hex = null;
                    $bug = true;
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
                $token[$nr]['is_executed'] = true;
                if(
                    isset($previous_nr) &&
                    isset($token[$previous_nr]) &&
                    $token[$previous_nr]['type'] == Token::TYPE_IS_MINUS
                    ){
                        $token[$nr]['execute'] = -$token[$nr]['execute'];
                        $token[$nr]['value'] = '-' . $token[$nr]['value'];
                        unset($token[$previous_nr]);
                }
                $skip_unset += 2;
                $previous_nr = $nr;
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
                $hex['is_executed'] = true;
                $tmp = $token[$next];
                $tmp['value'] = substr($token[$next]['value'], 1);
                if(!empty($tmp['value'])){
                    $is_hex = Token::is_hex($tmp);
                    if($is_hex){
                        $hex['value'] .= $tmp['value'];
                        $hex['execute'] .= strtoupper($tmp['value']);
                        $hex['type'] = Token::TYPE_HEX;
                        if(
                            isset($previous_nr) &&
                            isset($token[$previous_nr]) &&
                            $token[$previous_nr]['type'] == Token::TYPE_IS_MINUS
                            ){
                                $hex['execute'] = '-' . $hex['execute'];
                                $hex['value'] = '-' . $hex['value'];
                                unset($token[$previous_nr]);
                        }
                        $start = $nr;
                        $skip_unset += 1;
                        $previous_nr = $nr;
                        continue;
                    } else {
                        $hex = null;
                    }
                }
            }
            /* wrong interpertation of octal.... only in string \[0-7]{1,3} or parameter
            elseif(
                $record['type'] == Token::TYPE_NUMBER &&
                substr($record['value'], 0, 1) == '0' &&
                strlen($record['value']) > 1
            ){
                //octal
                $token[$nr]['execute'] = $record['value'];
                $token[$nr]['type'] = Token::TYPE_OCT;
                if(
                    isset($previous_nr) &&
                    isset($token[$previous_nr]) &&
                    $token[$previous_nr]['type'] == Token::TYPE_IS_MINUS
                    ){
                        $token[$nr]['execute'] = -$token[$nr]['execute'];
                        $token[$nr]['value'] = '-' . $token[$nr]['value'];
                        unset($token[$previous_nr]);
                }
            }*/ elseif(
                $record['type'] == Token::TYPE_NUMBER
            ) {
                //int
                $token[$nr]['execute'] = $record['value'] + 0;
                $token[$nr]['is_executed'] = true;
                $token[$nr]['type'] = Token::TYPE_INT;
                if(
                    isset($previous_nr) &&
                    isset($token[$previous_nr]) &&
                    $token[$previous_nr]['type'] == Token::TYPE_IS_MINUS
                ){
                    $token[$nr]['execute'] = -$token[$nr]['execute'];
                    $token[$nr]['value'] = '-' . $token[$nr]['value'];
                    unset($token[$previous_nr]);
                }
            }
            $previous_nr = $nr;
        }
        return Token::method_list($token);
    }

    // translations before prepare move this to create_method
    public static function method_prepare($method=[]){
        if($method['method']['name'] == Core_for::FOR){
            return $method;
        }
        $argument = [];
        $count = -1;
        foreach($method['method']['parameter'] as $nr => $parameter){
            $has_as = false;
            $has_is_array_operator = false;
            foreach($parameter as $token_nr => $record){
                if(
                    $record['type'] == Token::TYPE_STRING &&
                    $record['value'] == 'as'
                ){
                    $has_as = true;
                    //reorganize to 3 paramaters
                }
                elseif(
                    $has_is_array_operator === false &&
                    $record['type'] == Token::TYPE_IS_ARRAY_OPERATOR
                ){
                    $has_is_array_operator = true;
                    $count = $nr;
                    if($has_as === true){
                        foreach($parameter as $token_nr => $record){
                            if(
                                $record['type'] == Token::TYPE_STRING &&
                                $record['value'] == 'as'
                            ){
                                $count++;
                                continue;
                            }
                            elseif($record['type'] == Token::TYPE_IS_ARRAY_OPERATOR){
                                $count++;
                                continue;
                            }
                            $record['hold_execute'] = true;
                            $argument[$count][$token_nr] = $record;
                        }
                    } else {
                        foreach($parameter as $token_nr => $record){
                            if($record['type'] == Token::TYPE_IS_ARRAY_OPERATOR){
                                $count++;
                                continue;
                            }
                            $record['hold_execute'] = true;
                            $argument[$count][$token_nr] = $record;
                        }
                    }
                    continue 2;
                    //reorganize to 2 or 3 paramaters
                }
            }
            $count++;
            $argument[$count] = $parameter;
        }
        $method['method']['parameter'] = $argument;
        foreach($method['method']['parameter'] as $nr => $parameter){
            $method['method']['parameter'][$nr] = Token::create_modifier($parameter);
        }
        return $method;
    }

    public static function method_list($token=[]){
        $list = [];
        $count = 0;
        $method_nr = null;
        $depth = null;
        foreach($token as $nr => $record){
            /*
            if(
                $method_nr === null &&
                $record['type'] == Token::TYPE_METHOD
            ){
                $method_nr = $count;
            }
            elseif(
                $depth === null &&
                $method_nr !== null &&
                $record['type'] == Token::TYPE_PARENTHESE_OPEN
            ){
                $depth = $record['depth'];
            }
            elseif(
                $method_nr !== null &&
                $depth == $record['depth'] &&
                $record['type'] == Token::TYPE_PARENTHESE_CLOSE
            ){
                $list[$method_nr]['token']['parenthese_close_nr'] = $count;
                $method_nr = null;
                $depth = null;
            }
            */
            $list[$count] = $record;
            $list[$count]['token']['nr'] = $count;
            $count++;
        }
        return $list;
    }

    public static function is_hex($record=[]){
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

    public static function operator($record=[], $level=1){
        if($record['type'] != Token::TYPE_OPERATOR){
            return $record;
        }
        $record['is_operator'] = true;
        switch($level){
            case 1 :
                switch($record['value']){
                    case '=' :
                        $record['type'] = Token::TYPE_IS;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '+' :
                        $record['type'] = Token::TYPE_IS_PLUS;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '-' :
                        $record['type'] = Token::TYPE_IS_MINUS;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '*' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '/' :
                        $record['type'] = Token::TYPE_IS_DIVIDE;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '%' :
                        $record['type'] = Token::TYPE_IS_MODULO;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '>' :
                        $record['type'] = Token::TYPE_IS_GREATER;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '<' :
                        $record['type'] = Token::TYPE_IS_SMALLER;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case ':' :
                        $record['type'] = Token::TYPE_COLON;
                        return $record;
                    case '!' :
                        $record['type'] = Token::TYPE_EXCLAMATION;
                        $record['is_operator'] = false;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '?' :
                        $record['type'] = Token::TYPE_QUESTION;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '&' :
                        $record['type'] = Token::TYPE_AMPERSAND;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '|' :
                        $record['type'] = Token::TYPE_PIPE;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
            case 2 :
                switch($record['value']){
                    case '==' :
                        $record['type'] = Token::TYPE_IS_EQUAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '!=' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '=>' :
                        $record['type'] = Token::TYPE_IS_ARRAY_OPERATOR;
                        return $record;
                    case '->' :
                        $record['type'] = Token::TYPE_IS_OBJECT_OPERATOR;
                        return $record;
                    case '<=' :
                        $record['type'] = Token::TYPE_IS_SMALLER_EQUAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '>=' :
                        $record['type']= Token::TYPE_IS_GREATER_EQUAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '<>' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '+=' :
                        $record['type'] = Token::TYPE_IS_PLUS_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '-=' :
                        $record['type'] = Token::TYPE_IS_MINUS_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '*=' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '/=' :
                        $record['type'] = Token::TYPE_IS_DIVIDE_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '%=' :
                        $record['type'] = Token::TYPE_IS_MODULO_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '^=' :
                        $record['type'] = Token::TYPE_IS_XOR_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '&=' :
                        $record['type'] = Token::TYPE_IS_AND_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '|=' :
                        $record['type'] = Token::TYPE_IS_OR_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '<<' :
                        $record['type'] = Token::TYPE_IS_SMALLER_SMALLER;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '>>' :
                        $record['type'] = Token::TYPE_IS_GREATER_GREATER;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '++' :
                        $record['type'] = Token::TYPE_IS_PLUS_PLUS;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '--' :
                        $record['type'] = Token::TYPE_IS_MINUS_MINUS;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '**' :
                        $record['type'] = Token::TYPE_IS_POWER;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                    case '::' :
                        $record['type'] = Token::TYPE_DOUBLE_COLON;
                        return $record;
                    case '&&' :
                        $record['type'] = Token::TYPE_BOOLEAN_AND;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '||' :
                        $record['type'] = Token::TYPE_BOOLEAN_OR;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '??' :
                        $record['type'] = Token::TYPE_IS_COALESCE;
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
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '!==' :
                        $record['type'] = Token::TYPE_IS_NOT_IDENTICAL;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '/**' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_DOC_COMMENT;
                        return $record;
                    case '<=>' :
                        $record['type'] = Token::TYPE_IS_SPACESHIP;
                        $record['direction'] = Token::DIRECTION_LTR;
                        return $record;
                    case '**=' :
                        $record['type'] = Token::TYPE_IS_POWER_EQUAL;
                        $record['direction'] = Token::DIRECTION_RTL;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
        }
        return $record;
    }

    static public function type($char=null){
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

    public static function comment_remove($token=[]){
        if(!is_array($token)){
            return [];
        }
        foreach($token as $nr => $record){
            if(
                in_array(
                    $record['type'],
                    [
                        Token::TYPE_COMMENT,
                        Token::TYPE_DOC_COMMENT,
                        Token::TYPE_COMMENT_SINGLE_LINE
                    ]
                )
            ){
                unset($token[$nr]);
            }
        }
        return $token;
    }

        public static function tag_activate($token=[], $name='', $require_end=false, $is_execute=false, $tag_open='{', $tag_close='}'){
        if(empty($name)){
            return $token;
        }
        $compare = strtolower($name);
        $length = strlen($compare);
        $tag_nr = null;
        $tag_open_nr = null;
        $tag_close_nr = null;
        $skip = 0;
        $value = '';
        $end = end($token);
        $count = $end['token']['nr'];
        $previous = null;
        $previous_previous = null;
        $next = null;
        $next_next = null;
        $next_next_next = null;
        $has_content = false;
        $content = [];
        foreach($token as $nr => $record){
            if($skip >  0){
                $skip--;
                continue;
            }
            if(
                $tag_nr === null &&
                $record['type'] == Token::TYPE_STRING &&
                strlen($record['value']) == $length &&
                strtolower($record['value']) == $compare
            ){
                for($i = $nr - 1; $i >= 0; $i--){
                    if(isset($token[$i])){
                        if($previous === null){
                            $previous = $i;
                        }
                        elseif($previous_previous === null){
                            $previous_previous = $i;
                            break;
                        }
                    }
                }
                for($i = $nr + 1; $i <= $count; $i++){
                    if(isset($token[$i])){
                        if($next === null){
                            $next = $i;
                        }
                        elseif($next_next === null){
                            $next_next = $i;
                            break;
                        }
                    }
                }
                if(
                    $token[$previous] !== null &&
                    $token[$previous_previous] !== null &&
                    $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                ){
                    $tag_open_nr = $previous_previous;
                }
                elseif(
                    $token[$previous] !== null &&
                    $token[$previous]['type'] == Token::TYPE_CURLY_OPEN
                ){
                    $tag_open_nr = $previous;
                }
                if(
                    $next !== null &&
                    $next_next !== null &&
                    $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$next_next]['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    $tag_close_nr = $next_next;
                    if(
                        $tag_open_nr !== null
                    ){
                        $tag_nr = $nr;
                        $has_content = true;
                        $skip +=2;
                        continue;
                    } else {
                        $tag_open_nr = null;
                        $tag_close_nr = null;
                        $skip +=2;
                        continue;
                    }
                }
                elseif(
                    $next !== null &&
                    $token[$next]['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    $tag_close_nr = $next;
                    if(
                        $tag_open_nr !== null
                    ){
                        $tag_nr = $nr;
                        $has_content = true;
                        $skip +=1;
                        continue;
                    } else {
                        $tag_open_nr = null;
                        $tag_close_nr = null;
                        $skip +=1;
                        continue;
                    }
                } else {
                    $tag_open_nr = null;
                    $tag_close_nr = null;
                }
            }
            elseif($tag_nr !== null){
                if(
                    $record['type'] == Token::TYPE_TAG_CLOSE &&
                    strlen($record['tag']['name']) == $length + 1 &&
                    strtolower($record['tag']['name']) == '/' . $compare
                ){
                    $next = null;
                    $next_next =null;
                    $next_next_next = null;
                    $previous = null;
                    $previous_previous = null;
                    for($i = $nr - 1; $i >= 0; $i--){
                        if(isset($token[$i])){
                            if($previous === null){
                                $previous = $i;
                            }
                            elseif($previous_previous === null){
                                $previous_previous =$i;
                                break;
                            }
                        }
                    }
                    if(
                        $previous !== null &&
                        $previous_previous !== null &&
                        $content[$previous]['type'] == Token::TYPE_WHITESPACE &&
                        $content[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        unset($content[$previous]);
                        unset($content[$previous_previous]);
                    }
                    elseif(
                        $previous !== null &&
                        $token[$previous]['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        unset($content[$previous]);
                    }

                    /*
                    $previous = null;
                    $previous_previous = null;
                    $next = null;
                    $next_next =null;
                    for($i = $nr - 1; $i >= 0; $i--){
                        if(isset($token[$i])){
                            if($previous === null){
                                $previous = $i;
                            }
                            elseif($previous_previous === null){
                                $previous_previous =$i;
                                break;
                            }
                        }
                    }
                    for($i = $nr + 1; $i <= $count; $i++){
                        if(isset($token[$i])){
                            if($next === null){
                                $next = $i;
                            }
                            elseif($next_next === null){
                                $next_next = $i;
                                break;
                            }
                        }
                    }
                    if(
                        $previous !== null &&
                        $previous_previous !== null &&
                        $content[$previous]['type'] == Token::TYPE_WHITESPACE &&
                        $content[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        unset($content[$previous]);
                        unset($content[$previous_previous]);
                    }
                    elseif(
                        $previous !== null &&
                        $token[$previous]['type'] == Token::TYPE_CURLY_OPEN
                    ){
                        unset($content[$previous]);
                    }
                    if(
                        $next !== null &&
                        $next_next !== null &&
                        $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                        $token[$next_next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        unset($token[$next]);
                        unset($token[$next_next]);
                        $skip += 2;
                    }
                    elseif(
                        $next !== null &&
                        $token[$next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        unset($token[$next]);
                        $skip += 1;
                    }
                    for($i = $tag_open_nr; $i < $tag_nr ; $i++){
                        unset($token[$i]);
                    }
                    for($i = $tag_nr + 1; $i < $tag_close_nr; $i++){
                        unset($token[$i]);
                    }
                    unset($token[$nr]);
                    */
                    foreach($content as $content_nr => $content_record){
                        $value .= $content_record['value'];
                    }
                    $token[$tag_nr]['type'] = 'tag-' . $compare;
                    $token[$tag_nr]['token']['tag_close_nr'] = $record['token']['nr'];
                    $token[$tag_nr]['tag']['name'] = $compare;
                    $token[$tag_nr]['value'] = $value;

                    if($is_execute === true){
                        $token[$tag_nr]['execute'] = $token[$tag_nr]['value'];
                        $token[$tag_nr]['is_executed'] = true;
                        $token[$tag_nr] = Token::value_type($token[$tag_nr], 'execute');
                    }
                    for($i = $tag_open_nr; $i < $tag_nr ; $i++){
                        unset($token[$i]);
                    }
                    for($i = $tag_nr + 1; $i <= $token[$tag_nr]['token']['tag_close_nr'];  $i++){
                        unset($token[$i]);
                    }
                    for($i = $token[$tag_nr]['token']['tag_close_nr'] + 1; $i <= $count; $i++){
                        if(isset($token[$i])){
                            if($next === null){
                                $next = $i;
                            }
                            elseif($next_next === null){
                                $next_next = $i;
                            }
                            elseif($next_next_next === null){
                                $next_next_next = $i;
                                break;
                            }
                        }
                    }
                    if(
                        $next !== null &&
                        $next_next !== null &&
                        $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                        $token[$next_next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        unset($token[$next]);
                        unset($token[$next_next]);
                        if(
                            $next_next_next !== null &&
                            $token[$next_next_next]['type'] == Token::TYPE_WHITESPACE
                        ){
                            $explode = explode("\n", $token[$next_next_next]['value'], 2);
                            if(isset($explode[1])){
                                $token[$next_next_next]['value'] = $explode[1];
                            }
                        }

                        $skip += 2;
                    }
                    elseif(
                        $next !== null &&
                        $token[$next]['type'] == Token::TYPE_CURLY_CLOSE
                    ){
                        unset($token[$next]);

                        if(
                            $next_next !== null &&
                            $token[$next_next]['type'] == Token::TYPE_WHITESPACE
                        ){
                            $explode = explode("\n", $token[$next_next]['value'], 2);
                            if(isset($explode[1])){
                                $token[$next_next]['value'] = $explode[1];
                            }
                        }

                        $skip += 1;
                    }
                    $tag_nr = null;
                    $tag_open_nr = null;
                    $tag_close_nr = null;
                    $value = '';
                    $content = [];
                    $previous = null;
                    $previous_previous = null;
                    $next = null;
                    $next_next = null;
                    $next_next_next = null;
                    continue;
                }
                $content[$record['token']['nr']] = $record;
            }
        }
            // throw new Exception('End tag required for: ' . $tag_open . $name . $tag_close);
        return $token;
    }

    public static function parameter_fix_whitespace($token=[]){
        return $token;
        foreach($token as $nr => $parameter){
            var_dump($parameter);
            $end = array_pop($parameter);
            var_Dump($end);
            die;

        }
    }

    public static function create_array($token=[]){
        if(!is_array($token)){
            return [];
        }
        $depth = 0;
        $string = '';
        $array = [];
        $array_start = null;
        foreach($token as $nr => $record){
            if(
                $record['value'] == '['
            ){
                $depth++;
                if(
                    $array_start === null &&
                    $depth == 1
                ){
                    $array_start = $nr;
                }
            }
            elseif($record['value'] == ']'){
                $string .= $record['value'];
                $array[]  = $record;
                if(
                    $array_start !== null &&
                    $depth == 1
                ){
                    $create = $token[$array_start];
                    $create['value'] = $string;
                    $create['token']['array'] = $array;
                    $create['type'] = Token::TYPE_ARRAY;

                    $token[$array_start] = $create;
                    for($i = $array_start + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $array_start = null;
                    $array = [];
                    $string = '';
                }
                $depth--;
            }
            if($array_start){
                $string .= $record['value'];
                $array[] = $record;
            }
        }
        return $token;
    }

    public static function create_object($token=[]){
        if(!is_array($token)){
            return [];
        }
        $depth = 0;
        $string = '';
        $object = [];
        $object_start = null;
        // var_dump($token);
        foreach($token as $nr => $record){
            if(
                $object_start !== null &&
                $record['value'] == '|'
            ){
                $object_start = null;
                $object = [];
                $string = '';
            }
            elseif(
                $object_start !== null &&
                $record['type'] == Token::TYPE_TAG_CLOSE){
                $object_start = null;
                $object = [];
                $string = '';
            }
            elseif($record['value'] == '{'){
                $depth++;
                if(
                    $object_start === null &&
                    $depth == 1
                ){
                    $object_start = $nr;
                }
            }
            elseif($record['value'] == '}'){
                $object[] = $record;
                if(
                    $object_start !== null &&
                    $depth == 1
                ){
                    $string .= $record['value'];
                    $create = $token[$object_start];
                    $create['value'] = $string;
                    $create['token']['object'] = $object;
                    $create['type'] = Token::TYPE_OBJECT;
                    $token[$object_start] = $create;
                    for($i = $object_start + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $object_start = null;
                    $object = [];
                    $string = '';
                }
                $depth--;
            }
            if($object_start){
                $string .= $record['value'];
                $object[] = $record;
            }
        }
        return $token;
    }

    public static function create_cast($token=[], $count=0){
        if(!is_array($token)){
            return [];
        }
        $depth = 0;
        $parenthese_open = null;
        $parenthese_close = null;
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_PARENTHESE_OPEN){
                $depth++;
                $parenthese_open = $nr;
            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_CLOSE){
                if($depth == $record['depth']){
                    $parenthese_close = $nr;
                }
                $depth--;
            }
            $count = 0;
            if(
                $parenthese_open !== null &&
                $parenthese_close !== null &&
                $parenthese_close > $parenthese_open
            ){
                $cast = [];
                $value = '(';
                for($i=$parenthese_open +1; $i < $parenthese_close; $i++){
                    if(!isset($token[$i])){
                        continue;
                    }
                    $value .= $token[$i]['value'];
                    if($token[$i]['type'] == Token::TYPE_WHITESPACE){
                        continue;
                    }
                    $cast = $token[$i]['value'];
                    $count++;
                }
            }
            $is_cast = false;
            if($count == 1){
                $switch = strtolower($cast);
                switch($switch){
                    case 'bool' :
                    case 'boolean' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_BOOLEAN;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'float' :
                    case 'double' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_FLOAT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'int' :
                    case 'integer' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_INT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'string' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_STRING;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'array' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_ARRAY;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'object' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_OBJECT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = Token::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                }
                if($is_cast){
                    for($i = $parenthese_open +1; $i <= $parenthese_close; $i++){
                        unset($token[$i]);
                    }
                }
                $count = 0;
                $parenthese_open = null;
                $parenthese_close = null;
            }
        }
        return $token;
    }

    public static function create_variable($token=[]){
        if(!is_array($token)){
            return [];
        }
        $token = Token::create_assign($token);
        $token = Token::create_modifier($token);
        return $token;
    }

    public static function create_modifier($token=[]){
        if(!is_array($token)){
            return [];
        }
        $has_modifier_nr = null;
        $modifier_start_nr = null;
        $value = [];
        foreach($token as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $has_modifier_nr === null &&
                isset($record['variable']['has_modifier'])
            ){
                $has_modifier_nr = $nr;
                continue;
            }
            if($has_modifier_nr !== null){
                $value[$nr] = $record;
            }
            if(
                $has_modifier_nr !== null &&
                $modifier_start_nr === null
            ){
                if(
                    $record['value'] == '|'
                ){
                    $modifier_start_nr = $nr;
                    $is_parameter = false;
                    $has_parameter = false;
                    $parameter = [];
                    $name = '';
                    $counter = -1;
                    continue;
                }
            }
            elseif(
                $has_modifier_nr !== null &&
                $modifier_start_nr !== null
            ){
                if(
                    $record['is_operator'] === false &&
                    $record['type'] != Token::TYPE_WHITESPACE &&
                    $record['value'] != ':' &&
                    $record['value'] != ')' &&
                    $record['value'] != '(' &&
                    $is_parameter === false &&
                    !empty($parameter)
                ){
                    $name = rtrim($name);
                    $modifier = [];
                    $modifier['name'] = $name;
                    $modifier['parameter'] = $parameter;
                    $token[$has_modifier_nr]['variable']['modifier']['list'][] = $modifier;
//                     $token[$has_modifier_nr]['variable']['modifier'] = Token::modifier_direction($token[$has_modifier_nr]['variable']['modifier']);
                    foreach($value as $key => $item){
                        $token[$has_modifier_nr]['variable']['value'][$key] = $item;
                    }
                    for($i = $has_modifier_nr + 1; $i < $nr; $i++){
                        unset($token[$i]);
                    }
                    $name = '';
                    $counter = -1;
                    $parameter = [];
                    $modifier_start_nr = null;
                    $is_parameter = false;
                    $has_parameter = false;
                    if(
                        $record['type'] == Token::TYPE_VARIABLE &&
                        isset($record['variable']['has_modifier'])
                    ){
                        $has_modifier_nr = $nr;
                        continue;
                    } else {
                        $has_modifier_nr = null;
                        continue;
                    }
                }
                if(
                    $record['type'] == Token::TYPE_WHITESPACE &&
                    empty($name)
                ){
                    continue;
                }
                if(
                    $record['value'] == '|'
                ){
                    $name = rtrim($name);
                    $modifier = [];
                    $modifier['name'] = $name;
                    $modifier['parameter'] = $parameter;

                    $token[$has_modifier_nr]['variable']['modifier']['list'][] = $modifier;
//                     $token[$has_modifier_nr]['variable']['modifier'] = Token::modifier_direction($token[$has_modifier_nr]['variable']['modifier']);
                    foreach($value as $key => $item){
                        $token[$has_modifier_nr]['variable']['value'][$key] = $item;
                    }
                    for($i = $has_modifier_nr + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $name = '';
                    $counter = -1;
                    $parameter = [];
                    $modifier_start_nr = $nr;
                    $is_parameter = false;
                    $has_parameter = false;
                    $value = [];
                    continue;
                }
                elseif($record['value'] == ':'){
                    $counter++;
                    $is_parameter = true;
                    continue;
                }
                if($is_parameter === false){
                    if($has_parameter === false){
                        $name .= $record['value'];
                    } else {
                        if($record['is_operator'] === true){
                            $parameter[$counter][$record['token']['nr']] = $record;
                            $is_parameter = true;
                            continue;
                        }
                        if(
                            in_array(
                                $record['type'],
                                [
                                    Token::TYPE_PARENTHESE_OPEN,
                                    Token::TYPE_PARENTHESE_CLOSE,
                                    Token::TYPE_EXCLAMATION,
                                    Token::TYPE_CAST,
                                    Token::TYPE_WHITESPACE
                                ]
                            )
                        ){
                            $parameter[$counter][$record['token']['nr']] = $record;
                            $is_parameter = true;
                            continue;
                        }
                    }
                } else {
                    $parameter[$counter][$record['token']['nr']] = $record;
                    $has_parameter = true;
                    if(
                        in_array(
                            $record['type'],
                            [
                                Token::TYPE_PARENTHESE_OPEN,
                                Token::TYPE_PARENTHESE_CLOSE,
                                Token::TYPE_EXCLAMATION,
                                Token::TYPE_CAST,
                                Token::TYPE_WHITESPACE
                            ]
                        )
                    ){
                        $is_parameter = true;
                        continue;
                    }
                    $is_parameter = false;
                }
            }
        }
        if(isset($name) && $name != ''){
            $name = rtrim($name);
            $modifier = [];
            $modifier['name'] = $name;
            $modifier['parameter'] = $parameter;
            $token[$has_modifier_nr]['variable']['modifier']['list'][] = $modifier;
//             $token[$has_modifier_nr]['variable']['modifier'] = Token::modifier_direction($token[$has_modifier_nr]['variable']['modifier']);
            foreach($value as $key => $item){
                $token[$has_modifier_nr]['variable']['value'][$key] = $item;
            }
            for($i = $has_modifier_nr + 1; $i <= $nr; $i++){
                unset($token[$i]);
            }
        }
        return $token;
    }

    public static function modifier_execute(Parse $parse, $variable=[], $token=[], $keep=false, $tag_remove=true){
        if($variable['type'] != Token::TYPE_VARIABLE){
            return $token;
        }
        if(!isset($variable['variable']['has_modifier'])){
            $token[$variable['token']['nr']] = $variable;
            return $token;
        }
        if(isset($variable['variable']['is_modifier_execute'])){
            $token[$variable['token']['nr']] = $variable;
            return $token;
        }
        $modifier = Token::modifier_direction($parse, $variable['variable']['modifier']);
        if($modifier['direction'] == Token::DIRECTION_RTL){
            $list = array_reverse($modifier['list']);
        } else {
            $list = $modifier['list'];
        }
        foreach($list as $nr => $modify){
            $file_name = 'Modifier.' . ucfirst(strtolower($modify['name'])) . '.php';
            $function_name = 'modifier_' . str_replace('.', '_', $modify['name']);
            $location = $parse->data(Parse::DATA_DIR_MODIFIER);
            $is_modifier = false;
            $search = [];
            foreach($location as $dir){
                $url = $dir . $file_name;
                $search[] = $url;
                $data_modifier = $parse->data(Parse::DATA_MODIFIER);
                if($data_modifier === null){
                    $data_modifier = [];
                }
                if(
                    in_array(
                        $url,
                        $data_modifier
                    )
                ){
                    $is_modifier = true;
                    break;
                }
                elseif(
                    $is_modifier === false &&
                    File::exist($url)
                ){
                    $is_modifier = true;
                    require_once $url;

                    $data_modifier = $parse->data(Parse::DATA_MODIFIER);
                    $data_modifier[] = $url;

                    $parse->data(Parse::DATA_MODIFIER, $data_modifier);
                    break;
                }
            }
            if($is_modifier === true){
                $modify['token_parameter'] = $modify['parameter'];
                $variable_value = $variable['variable']['value'];
                foreach($modify['parameter'] as $nr => $parameter){
                    $execute = reset($parameter);
                    if($execute !== null){
                        if(Token::hold_execute($parameter) === true){

                        } else {
                            $variable_value = Token::set_execute($parse, $parameter, $execute, $variable_value, $keep, $tag_remove);
                            $execute = $variable_value[$execute['token']['nr']];
                            // unset($token[$execute['token']['nr']]);
                            $modify['parameter'][$nr] = $execute['execute'];
                        }
                    } else {
                        var_dump($parameter);
                        die;
                    }
                }
                $variable['execute'] = $function_name($parse, $modify['parameter'], $token, $variable, $modify);
                if($variable['execute'] === null){
                    $check = null;
                    for($i = $variable['token']['nr'] - 1; $i >= 0; $i--){
                        if(isset($token[$i])){
                            $check = $token[$i];
                            break;
                        }
                    }
                    if(
                        $check !== null &&
                        $check['type'] == Token::TYPE_WHITESPACE
                    ){
                        $explode = explode("\n", $check['execute']);
                        if(isset($explode[1])){
                            array_pop($explode);
                            $token[$check['token']['nr']]['execute'] = implode("\n", $explode);
                        } else {
                            $token[$check['token']['nr']]['execute'] = '';
                        }
                    }
                }
                $token[$variable['token']['nr']] = $variable;
            } else {
                var_dump($search);
                throw new Exception('Parse error: Modifier: ' . $modify['name'] . ' not found...');
            }
        }
        $variable['variable']['is_modifier_execute'] = true;
        $token[$variable['token']['nr']] = $variable;
        return $token;
    }

    public static function modifier_direction(Parse $parse, $modifier=[]){
        if(!isset($modifier['direction'])){
            $modifier['direction'] = Token::DIRECTION_LTR;
        }
        if(isset($modifier['list'])){
            foreach($modifier['list'] as $nr => $record){
                if(strtolower($record['name']) == Token::MODIFIER_DIRECTION){
                    $direction = $record['parameter'][0];
                    $direction = Token::set_execute($parse, $direction);
                    if(isset($direction[1])){
                        throw new Exception('Could not determine modifier direction, please use rtl or ltr...');
                    }
                    $direction = $direction[0];
                    if(strtolower($direction['execute']) == Token::DIRECTION_RTL){
                        $modifier['direction'] = Token::DIRECTION_RTL;
                        unset($modifier['list'][$nr]);
                        break;
                    }
                }
            }
        }
        return $modifier;
    }

    public static function create_assign($token=[]){
        if(!is_array($token)){
            return [];
        }
        $has_assign = false;
        $assign_start_nr = null;
        $need_new_operator = false;
        $left_nr = null;
        $operator_nr = null;
        $to = null;
        $end = end($token);
        $is_debug = false;
        foreach($token as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $record['variable']['is_assign'] === true &&
                !isset($record['variable']['value'])
            ){
                $has_assign = true;
                $value = $record['value'];
                $variable_value = [];
                $direction = true; // true = left, false = right
                for($i = $nr + 1; $i <= $end['token']['nr']; $i++){
                    if(!isset($token[$i])){
                        continue;
                    }
                    elseif($token[$i]['type'] == Token::TYPE_WHITESPACE){
                        $value .= $token[$i]['value'];
                        if($assign_start_nr !== null && !empty($variable_value)){
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                        }
                        $to = $i;
                        continue;
                    }
                    elseif($token[$i]['type'] == Token::TYPE_CAST){
                        $value .= $token[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                        }
                        continue;
                    }
                    elseif($token[$i]['type'] == Token::TYPE_PARENTHESE_OPEN){
                        $value .= $token[$i]['value'];
                        if(
                            $need_new_operator === false &&
                            $assign_start_nr !== null
                        ){
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                            $to = $i;
                            continue;
                        }
                        elseif($need_new_operator === true){
                            $to = $i;
                            break;
                        }
                    }
                    elseif($token[$i]['type'] == Token::TYPE_PARENTHESE_CLOSE){
                        $value .= $token[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                            $to = $i;
                            continue;
                        }
                    }
                    elseif($token[$i]['type'] == Token::TYPE_EXCLAMATION){
                        $value .= $token[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                            $to = $i;
                            continue;
                        }
                    }
                    elseif(
                        $token[$i]['is_operator'] == true &&
                        $token[$i]['value'] == $record['variable']['operator'] &&
                        $token[$i]['depth'] == $record['depth'] &&
                        $assign_start_nr === null
                    ){
                        $value .= $token[$i]['value'];
                        $assign_start_nr = $i;
                        $to = $i;
                        continue;
                    }
                    if($assign_start_nr !== null){
                        if($token[$i]['is_operator'] === true){
                            if(
                                in_array(
                                    $token[$i]['value'],
                                    Token::TYPE_ASSIGN
                                )
                            ){
                                break;
                                /*
                                //might throw wrong place for assignment error
                                $assign_start_nr = $i;
                                $to = $i;
                                $variable_end = end($variable_value);
                                if($variable_end['type'] == Token::TYPE_WHITESPACE){
                                    array_pop($variable_value);
                                    $value = rtrim($value);
                                }
                                $token[$nr]['value'] = $value;
                                $token[$nr]['variable']['value'] = $variable_value;
                                for($j = $nr + 1; $j <= $to; $j++){
                                    if(
                                        isset($token[$j]) &&
                                        $token[$j]['type'] == Token::TYPE_VARIABLE &&
                                        $record['variable']['is_assign'] === true
                                    ){
                                        continue;
                                    }
                                    unset($token[$j]);
                                }
                                $need_new_operator = false;
                                $left_nr = null;
                                $operator_nr = null;
                                $to = null;
                                $value = '';



                                var_dump($token);
                                var_dump($assign_start_nr);
var_Dump($end);

                                die;
                                // $token = Token::create_assign($token, $counter++);
                                break;
                                */
                            }
                            $to = $i;
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                            $value .= $token[$i]['value'];
                            $operator_nr = $i;
                            if($need_new_operator === true){
                                $need_new_operator = false;
                            }
                            continue;
                        }
                        if($direction === true){
                            if($need_new_operator === true){
                                break;
                            }
                            $to = $i;
                            $variable_value[$token[$i]['token']['nr']] = $token[$i];
                            $value .= $token[$i]['value'];
                            $direction = false;
                            $left_nr = $i;
                            if(
                                $operator_nr !== null &&
                                $operator_nr < $left_nr
                            ){
                                $need_new_operator = true;
                            }
                        } else {
                            if($need_new_operator === true){
                                break;
                            }
                            elseif(
                                $operator_nr !== null &&
                                $operator_nr > $left_nr
                            ){
                                $to = $i;
                                $variable_value[$token[$i]['token']['nr']] = $token[$i];
                                $value .= $token[$i]['value'];
                                $right_nr = $i;
                                $direction = true;
                                $need_new_operator = true;
                            } else {
                                var_dump('fpou');
                                die;
                                break;
                            }
                        }
                    }
                }
                $variable_end = end($variable_value);
                if($variable_end['type'] == Token::TYPE_WHITESPACE){
                    array_pop($variable_value);
                    $value = rtrim($value);
                }
                $token[$nr]['value'] = $value;
                $token[$nr]['variable']['value'] = $variable_value;
                for($j = $nr + 1; $j <= $to; $j++){
                    if(
                        isset($token[$j]) &&
                        $token[$j]['type'] == Token::TYPE_VARIABLE &&
                        $token[$j]['variable']['is_assign'] === true //was $record['variable]['is_asign']
                    ){
                        var_dump($j);
                        continue;
                    }
                    unset($token[$j]);
                }
                $assign_start_nr = null;
                $need_new_operator = false;
                $left_nr = null;
                $operator_nr = null;
                $to = null;
                $value = '';
            }
        }
        if($has_assign === true){
            foreach($token as $nr => $record){
                $token[$nr]['direction'] = Token::DIRECTION_RTL;
            }
        }
        return $token;
    }

    public static function create_token($type=null, $token=[]){
        if(!array_key_exists('value', $token)){
            $token['value'] = null;
        }
        switch($type){
            case Token::TYPE_CURLY_OPEN :
                $token['type'] = Token::TYPE_CURLY_OPEN;
                $token['value'] = '{';
            break;
            case Token::TYPE_CURLY_CLOSE :
                $token['type'] = Token::TYPE_CURLY_CLOSE;
                $token['value'] = '}';
            break;
        }
        if(!array_key_exists('column', $token)){
            $token['column'] = 0;
        }
        if(!array_key_exists('row', $token)){
            $token['row'] = 0;
        }
        if(!array_key_exists('is_operator', $token)){
            $token['is_operator'] = false;
        }
        if(!array_key_exists('depth', $token)){
            $token['depth'] = 0;
        }
        if(
            !array_key_exists('token', $token) ||
            !array_key_exists('nr', $token['token'])
        ){
            $token['token']['nr'] = 0;
        }
        return $token;
    }

    public static function create_string($token=[]){
        if(!is_array($token)){
            return [];
        }
        $string = '';
        $previous_nr = null;
        $string_start_nr = null;
        foreach($token as $nr => $record){
            if(
                $previous_nr !== null &&
                isset($token[$previous_nr]) &&
                $token[$previous_nr]['type'] == Token::TYPE_BACKSLASH &&
                $record['type'] == Token::TYPE_QUOTE_DOUBLE
            ){
                $string .= $record['value'];
                continue;
            }
            if($record['type'] == Token::TYPE_QUOTE_DOUBLE){
                if($string_start_nr === null){
                    $string_start_nr = $nr;
                    $string = $record['value'];
                    $string = '';
                } else {
                    // $string .= $record['value'];
                    $token[$string_start_nr]['value'] = $string;
                    $token[$string_start_nr]['type'] = Token::TYPE_STRING;

                    for($i= $string_start_nr + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $string_start_nr = null;
                }
            } else {
                $string .= $record['value'];
            }
            $previous_nr = $nr;
        }
        return $token;
    }

    public static function create_method($token=[]){
        if(!is_array($token)){
            return [];
        }
        $method_nr = null;
        $depth = null;
        $name = '';
        $parameter = [];
        foreach($token as $nr => $record){
            if(
                $method_nr === null &&
                $record['type'] == Token::TYPE_METHOD &&
                !isset($record['method']['parameter'])
            ){
                $method_nr = $nr;
                $name .= $record['value'];
                $is_for = false;
                $is_foreach = false;
            }
            elseif($method_nr !== null){
                $name .= $record['value'];
                if(
                    $record['type'] == Token::TYPE_PARENTHESE_OPEN &&
                    $depth === null
                ){
                    $depth = $record['depth'];
                    $count_parameter = 0;
                    $count_expression = 0;
                    for($i = $method_nr + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    if($token[$method_nr]['method']['name'] == Core_for::FOR){
                        $is_for = true;
                    }
                    elseif($token[$method_nr]['method']['name'] == Core_foreach::FOREACH){
                        $is_foreach = true;
                    }
                }
                elseif(
                    $depth !== null &&
                    $record['type'] == Token::TYPE_COMMA &&
                    $depth == $record['depth']
                ){
                    if($is_for === true){
                        $count_expression++;
                    } else {
                        $count_parameter++;
                    }

                    unset($token[$nr]);
                }
                elseif(
                    $is_for === true &&
                    $depth !== null &&
                    $record['type'] == Token::TYPE_SEMI_COLON &&
                    $depth == $record['depth']
                ){
                    $count_parameter++;
                    $count_expression = 0;
                    unset($token[$nr]);
                }
                elseif(
                    $depth !== null &&
                    $record['type'] == Token::TYPE_PARENTHESE_CLOSE &&
                    $depth === $record['depth']
                ){
                    if($is_for === true){
                        foreach($parameter as $parameter_nr => $parameter_value){
                            foreach($parameter_value as $expression_nr => $expression_value){
                                $parameter[$parameter_nr][$expression_nr] = Token::create($parameter[$parameter_nr][$expression_nr]);
                                if($parameter_nr == 2){
                                    foreach($parameter[$parameter_nr][$expression_nr] as $token_nr => $token_value){
                                        $parameter[$parameter_nr][$expression_nr][$token_nr]['hold_execute'] = true;
                                    }
                                }
                            }
                        }
                    } else {
                        foreach($parameter as $parameter_nr => $parameter_value){
                            $parameter[$parameter_nr] = Token::create($parameter[$parameter_nr]);
                        }
                    }
                    $token[$method_nr]['method']['parameter'] = $parameter;
                    $token[$method_nr]['value'] = $name;
                    $token[$method_nr]['token']['parenthese_close_nr'] = $nr;
                    $method_nr = null;
                    $depth = null;
                    $name = '';
                    $parameter = [];
                    unset($token[$nr]);
                }
                elseif($depth !== null){
                    if($is_for === true){
                        $parameter[$count_parameter][$count_expression][$record['token']['nr']] = $record;
                    } else {
                        $parameter[$count_parameter][$record['token']['nr']] = $record;
                    }

                    unset($token[$nr]);
                }
            }
        }
        return $token;
    }

    public static function create($token=[], $tag_open_nr=null, $tag_close_nr=null, $is_debug=false){
        // var_dump($token);
        if(!is_array($token)){
            return [];
        }
        if(
            $tag_open_nr !== null &&
            !isset($token[$tag_open_nr])
        ){
            throw new Exception('Cannot find tag open in token');
            die;
        }
        if(
            $tag_close_nr !== null &&
            !isset($token[$tag_close_nr])
        ){
            throw new Exception('Cannot find tag close in token');
            die;
        }
        $create = [];
        $is_whitespace_before = false;
        if(
            $tag_open_nr !== null &&
            $tag_close_nr !== null
        ){
            foreach($token as $nr => $record){
                if(
                    $nr > $tag_open_nr &&
                    $nr < $tag_close_nr
                    ){
                        if(
                            $record['type'] == Token::TYPE_WHITESPACE &&
                            $is_whitespace_before === false &&
                            empty($create)
                            ){
                                $is_whitespace_before = true;
                                continue;
                        }
                        $create[$record['token']['nr']] = $record;
                }
                elseif($nr >= $tag_close_nr){
                    break;
                }
            }
        } else {
            $create = $token;
            $current = reset($create);
            if($current['type'] == Token::TYPE_WHITESPACE){
                Token::array_shift($create, true);
            }
        }
        $end = end($create);
        if($end['type'] == Token::TYPE_WHITESPACE){
            array_pop($create);
        }
        // var_dump($tag_open_nr);
        // var_dump($tag_close_nr);
        // var_dump($create);
        // var_dump($token);
        $create = Token::create_string($create);
        $create = Token::create_cast($create);
        $create = Token::create_array($create);
        $create = Token::create_object($create);
        $create = Token::create_method($create);
        $create = Token::create_variable($create);
        return $create;
    }

    public static function content($token=[], $tag_open_nr=null, $tag_close_nr=null){
        $content = [];
        $part = null;
        $is_before = true;
        $is_whitespace_before = false;
        for($i=$tag_open_nr+1; $i < $tag_close_nr; $i++){
            if(isset($token[$i])){
                if(
                    $token[$i]['type'] == Token::TYPE_WHITESPACE &&
                    $is_before === true &&
                    $is_whitespace_before === false
                ){
                    $is_whitespace_before = true;
                    continue;
                }
                $is_before = false;
                $part = $token[$i];
                $content[] = $part;
            }
        }
        if(
            $part !== null &&
            $part['type'] == Token::TYPE_WHITESPACE
        ){
            array_pop($content);
        }
        return $content;
    }

    public static function depth_highest($token=[]){
        $depth = 0;
        foreach ($token as $nr => $record){
            if($record['depth'] > $depth){
                $depth = $record['depth'];
            }
        }
        return $depth;
    }

    public static function set_get($token=[]){
        $set = [];
        $is_set = false;
        $highest = Token::depth_highest($token);
        foreach($token as $nr => $record){
            if($record['depth'] == $highest){
                $is_set = true;
            }
            if($is_set === true){
                $set[$record['token']['nr']] = $record;
                if($record['value'] == ')' && $record['depth'] == $highest){
                    break;
                }

            }
        }
        return $set;
    }

    public static function set_has($token=[]){
        if(!is_array($token)){
            return false;
        }
        foreach ($token as $nr => $record){
            if($record['value'] == '('){
                return  true;
            }
        }
        return false;
    }

    public static function operator_has($token=[]){
        if(!is_array($token)){
            return false;
        }
        foreach($token as $nr => $record){
            if($record['is_operator'] === true){
                return true;
            }
        }
        return false;
    }

    public static function operator_get($token=[]){
        $left_start_nr = null;
        $left = [];
        $right = [];
        $operator = [];
//         $close_nr = null;
        foreach($token as $nr => $record){
            if($record['is_operator'] === true){
                if(!empty($operator)){
                    break;
                }
                $operator = $record;
                unset($token[$nr]);
                continue;
            }
            if(
                in_array(
                    $record['value'],
                    [
                        '(',
                        ')'
                    ]
                    )
                ){
                    unset($token[$nr]);
                    continue;
            }
            if(empty($operator)){
                $left[$record['token']['nr']] = $record;
                if($left_start_nr === null){
                    $left_start_nr = $record['token']['nr'];
                }
            } else {
                $right[$record['token']['nr']] = $record;
//                 $close_nr = $record['token']['nr'];
            }
            unset($token[$nr]);
        }
        if(isset($operator['token'])){
            $end = end($right);
            if($end['type'] == Token::TYPE_WHITESPACE){
                array_pop($right);
            }
            $operator['token']['left'] = $left;
            $operator['token']['right'] = $right;

            $operator['token']['left_token'] = $left;
            $operator['token']['right_token'] = $right;

        }
        if($left_start_nr !== null){
            $operator['token']['nr'] = $left_start_nr;
        }
        /*
        if($close_nr !== null){
            $operator['token']['close_nr'] = $close_nr;

        }
        */
        return $operator;
    }

    public static function operator_execute($parse, $operator=[], $token=[], $keep=false, $tag_remove=true){
        if(!is_array($operator)){
            return [];
        }
        if(isset($operator['is_executed'])){
            return $operator;
        }
        if($operator['value'] == '='){
            $debug = debug_backtrace(true);
            // var_dump($debug);
            var_dump($operator);
            var_dump($operator['token']['left']);
            var_dump($operator['token']['right']);
            die;
        }

        if(isset($operator['token']['left'])){
            try {
                $operator['token']['left'] = Token::value_execute($parse, $operator['token']['left'], $token, $keep, $tag_remove);
                if(!isset($operator['token']['left']['is_executed'])){
                    throw new Exception('Left hand side isn\'t executed.');
                }
            } catch (Exception $e) {
                echo $e;
            }
        } else {
            $operator['token']['left'] = [
                'execute' => null,
                'is_executed' => true
            ];
        }
        if(isset($operator['token']['right'])){
            try {
                $operator['token']['right'] = Token::value_execute($parse, $operator['token']['right'], $token, $keep, $tag_remove);
                if(!isset($operator['token']['right']['is_executed'])){
                    throw new Exception('Right hand side isn\'t executed.');
                }
            } catch (Exception $e) {
                echo $e;
            }
        } else {
            $operator['token']['right'] = [
                'execute' => null,
                'is_executed' => true
            ];
        }

        if(!isset($operator['token']['left']['value'])){
            var_dump($operator['token']['left']);
            throw new Exception('Left hand side value not executed.');
        }
        if(!isset($operator['token']['right']['value'])){
            // var_dump($operator['token']['right']);
            // die;
            throw new Exception('Right hand side value not executed.');
        }

        switch($operator['value']){
            case '+' :
                if(
                    $operator['token']['left']['type'] == Token::TYPE_STRING ||
                    $operator['token']['right']['type'] == Token::TYPE_STRING
                ){
                    if($operator['token']['left']['type'] == Token::TYPE_OBJECT){
                        if(method_exists($operator['token']['left']['execute'], '__toString')){
                            $operator['execute'] = $operator['token']['left']['execute'] . $operator['token']['right']['execute'];
                        } else {
                            throw new Exception('Unsupported operand types');
                        }
                    }
                    elseif($operator['token']['right']['type'] == Token::TYPE_OBJECT){
                        if(method_exists($operator['token']['right']['execute'], '__toString')){
                            $operator['execute'] = $operator['token']['left']['execute'] . $operator['token']['right']['execute'];
                        } else {
                            throw new Exception('Unsupported operand types');
                        }
                    }
                    elseif(
                        $operator['token']['left']['type'] == Token::TYPE_ARRAY ||
                        $operator['token']['right']['type'] == Token::TYPE_ARRAY
                    ){
                        throw new Exception('Unsupported operand types');
                    } else {
                        $operator['execute'] = $operator['token']['left']['execute'] . $operator['token']['right']['execute'];
                    }
                    break;
                }
                if($operator['token']['left']['type'] == Token::TYPE_NULL){
                    if($operator['token']['right']['type'] == Token::TYPE_OBJECT){
                        if(method_exists($operator['token']['right']['execute'], '__toString')){
                            $operator['execute'] = (string) $operator['token']['right']['execute'];
                        } else {
                            throw new Exception('Unsupported operand types');
                        }
                    }
                    elseif($operator['token']['right']['type'] == Token::TYPE_ARRAY){
                        $operator['execute'] = $operator['token']['right']['execute'];
                    } else {
                        $operator['execute'] = null + $operator['token']['right']['execute'];
                    }
                    break;
                }
                elseif($operator['token']['right']['type'] == Token::TYPE_NULL){
                    if($operator['token']['left']['type'] == Token::TYPE_OBJECT){
                        if(method_exists($operator['token']['left']['execute'], '__toString')){
                            $operator['execute'] = (string) $operator['token']['left']['execute'];
                        } else {
                            throw new Exception('Unsupported operand types');
                        }
                    }
                    elseif($operator['token']['left']['type'] == Token::TYPE_ARRAY){
                        $operator['execute'] = $operator['token']['left']['execute'];
                    } else {
                        $operator['execute'] = $operator['token']['left']['execute'] + null;
                    }
                    break;
                }
                if(
                    $operator['token']['left']['type'] != $operator['token']['right']['type'] &&
                    !(
                        $operator['token']['left']['type'] == Token::TYPE_INT &&
                        $operator['token']['right']['type'] == Token::TYPE_FLOAT
                    ) &&
                    !(
                        $operator['token']['left']['type'] == Token::TYPE_FLOAT &&
                        $operator['token']['right']['type'] == Token::TYPE_INT
                    )
                ){
                    var_dump($operator['token']);
                    die;
                    throw new Exception('Unsupported operand types');
                }
                elseif($operator['token']['left']['type'] == Token::TYPE_ARRAY){
                    $operator['execute'] = Token::array_calculate(
                        $operator['token']['left'],
                        $operator['token']['right'],
                        '+'
                    );
                }
                elseif($operator['token']['left']['type'] == Token::TYPE_OBJECT){
                    if(
                        method_exists($operator['token']['left']['execute'], '__toString') &&
                        method_exists($operator['token']['right']['execute'], '__toString')
                    ){
                        $operator['execute'] = $operator['token']['left']['execute'] . $operator['token']['right']['execute'];
                    } else {
                        throw new Exception('Unsupported operand types');
                    }
                } else {
                    $operator['execute'] = $operator['token']['left']['execute'] + $operator['token']['right']['execute'];
                }
            break;
            case '-' :
                if(
                    in_array(
                        $operator['token']['left']['type'],
                        [
                            Token::TYPE_BOOLEAN,
                            Token::TYPE_OBJECT
                        ]
                    ) ||
                    in_array(
                        $operator['token']['right']['type'],
                        [
                            Token::TYPE_BOOLEAN,
                            Token::TYPE_OBJECT
                        ]
                    )
                ){
                    throw new Exception('Unsupported operand types');
                }
                elseif(
                    in_array(
                        $operator['token']['left']['type'],
                        [
                            Token::TYPE_INT,
                            Token::TYPE_FLOAT
                        ]
                    ) &&
                    in_array(
                        $operator['token']['right']['type'],
                        [
                            Token::TYPE_INT,
                            Token::TYPE_FLOAT
                        ]
                    )
                ){
                    $operator['execute'] = $operator['token']['left']['execute'] - $operator['token']['right']['execute'];
                }
                elseif($operator['token']['left']['type'] == Token::TYPE_NULL){
                    if($operator['token']['right']['type'] == Token::TYPE_ARRAY){
                        $operator['execute'] = [];
                    } else {
                        $operator['execute'] = 0 - $operator['token']['right']['execute'];
                    }
                }
                elseif($operator['token']['right']['type'] == Token::TYPE_NULL){
                    if($operator['token']['left']['type'] == Token::TYPE_ARRAY){
                        $operator['execute'] = $operator['token']['left']['execute'];
                    } else {
                        $operator['execute'] = $operator['token']['left']['execute'] - 0;
                    }
                }
                elseif(
                    $operator['token']['left']['type'] != $operator['token']['right']['type'] &&
                    !(
                        $operator['token']['left']['type'] == Token::TYPE_INT &&
                        $operator['token']['right']['type'] == Token::TYPE_FLOAT
                    ) &&
                    !(
                        $operator['token']['left']['type'] == Token::TYPE_FLOAT &&
                        $operator['token']['right']['type'] == Token::TYPE_INT
                    )
                ){
                    var_dump($operator['token']['left']);
                    var_dump($operator['token']['right']);
                    die;
                    throw new Exception('Unsupported operand types');
                } else {
                    if($operator['token']['left']['type'] == Token::TYPE_ARRAY){
                        //unsetting right stuff in left
                        // * = return both in left & right
                        // / = return !both in left & right
                        $operator['execute'] = Token::array_calculate($operator['token']['left'], $operator['token']['right'], '-');
                    } else {
                        $operator['execute'] = $operator['token']['left']['execute'] - $operator['token']['right']['execute'];
                    }
                }
            break;
            case '*' :
                $operator['execute'] = $operator['token']['left']['execute'] * $operator['token']['right']['execute'];
            break;
            case '**' :
                $operator['execute'] = $operator['token']['left']['execute'] ** $operator['token']['right']['execute'];
            break;
            case '/' :
                $operator['execute'] = $operator['token']['left']['execute'] / $operator['token']['right']['execute'];
            break;
            case '%' :
                $operator['execute'] = $operator['token']['left']['execute'] % $operator['token']['right']['execute'];
            break;
            case '==' :
                $operator['execute'] = $operator['token']['left']['execute'] == $operator['token']['right']['execute'];
            break;
            case '!=' :
                $operator['execute'] = $operator['token']['left']['execute'] != $operator['token']['right']['execute'];
            break;
            case '===' :
                $operator['execute'] = $operator['token']['left']['execute'] === $operator['token']['right']['execute'];
            break;
            case '!==' :
                $operator['execute'] = $operator['token']['left']['execute'] !== $operator['token']['right']['execute'];
            break;
            case '<>' :
                $operator['execute'] = $operator['token']['left']['execute'] <> $operator['token']['right']['execute'];
            break;
            case '>' :
                $operator['execute'] = $operator['token']['left']['execute'] > $operator['token']['right']['execute'];
            break;
            case '>>' :
                $operator['execute'] = $operator['token']['left']['execute'] >> $operator['token']['right']['execute'];
            break;
            case '>=' :
                $operator['execute'] = $operator['token']['left']['execute'] >= $operator['token']['right']['execute'];
            break;
            case '<' :
                $operator['execute'] = $operator['token']['left']['execute'] < $operator['token']['right']['execute'];
            break;
            case '<<' :
                $operator['execute'] = $operator['token']['left']['execute'] / $operator['token']['right']['execute'];
            break;
            case '<=' :
                $operator['execute'] = $operator['token']['left']['execute'] / $operator['token']['right']['execute'];
            break;
            case '<=>' :
                //breaks php5 compatibility...

                $operator['execute'] = $operator['token']['left']['execute'] <=> $operator['token']['right']['execute'];
            break;
            case '&&' :
                $operator['execute'] = $operator['token']['left']['execute'] && $operator['token']['right']['execute'];
            break;
            case '||' :
                $operator['execute'] = $operator['token']['left']['execute'] || $operator['token']['right']['execute'];
            break;
            case '&' :
                $operator['execute'] = $operator['token']['left']['execute'] & $operator['token']['right']['execute'];
            break;
            case '|' :
                $left = $operator['token']['left'];
                $right = $operator['token']['right'];
                if(!is_numeric($left['execute'])){
                    if(
                        $left['type'] == Token::TYPE_OBJECT &&
                        method_exists($left['execute'], '__toString')
                    ){
                        $left['execute'] = $left['execute'] + 0;
                    } else {
                        throw new Exception('Left hand side needs to be numeric...');
                    }
                }
                if(!is_numeric($right['execute'])){
                    if(
                        $right['type'] == Token::TYPE_OBJECT &&
                        method_exists($right['execute'], '__toString')
                        ){
                            $right['execute'] = $right['execute'] + 0;
                    } else {
                        throw new Exception('Right hand side needs to be numeric...');
                    }
                }
                $operator['execute'] = $left['execute'] | $right['execute'];
            break;
            case '^' :
                $operator['execute'] = $operator['token']['left']['execute'] ^ $operator['token']['right']['execute'];
                break;
        }
        if(!isset($operator['token']['left']['value'])){
            var_dump($operator['token']['left']);
            var_dump($operator);
            die;
        }
        if(!isset($operator['token']['right']['value'])){
            var_dump($operator);
            die;
        }
        if(!isset($operator['value'])){
            var_dump($operator);
            die;
        }
        $operator['value'] = $operator['token']['left']['value'] . $operator['value'] . $operator['token']['right']['value'];
        $operator['is_executed'] = true;
        $operator = Token::value_type($operator);
        $token = Token::operator_replace($token, $operator);
        return $token;
    }

    public static function operator_replace($token=[], $replace=[]){
        $is_replace = false;
        $left = $replace['token']['left_token'];
        $right = $replace['token']['right_token'];
        foreach($token as $nr => $record){
            if(
                $is_replace === false &&
                $nr == $replace['token']['nr']
            ){
                $end = end($replace['token']['right_token']);
                for($i = $nr + 1; $i <= $end['token']['nr']; $i++){
                    unset($token[$i]);
                }
                $token[$nr] = $replace;
                $is_replace = true;
                unset($left[$replace['token']['nr']]);
            }

            if($is_replace ===true){
                foreach($left as $key => $value){
                    unset($left[$key]);
                    unset($token[$key]);
                }
                foreach($right as $key => $value){
                    unset($right[$key]);
                    unset($token[$key]);
                }
                break;
            }
        }
        return $token;
    }


    public static function token_operator_remove($operator=[], $token=[]){

    }

    public static function operator_remove($token=[], $replace=[]){
        $is_replace = false;
        $left = $replace['token']['left_token'];
        $right = $replace['token']['right_token'];


        $operator = [];
        $result = [];
        foreach($token as $nr => $record){
            foreach($replace['token']['left_token'] as $key => $value){
                if($nr == $key){
                    continue 2;
                }
            }
            foreach($replace['token']['right_token'] as $key => $value){
                if($nr == $key){
                    continue 2;
                }
            }
            if($nr == $replace['token']['nr']){
                var_dump($nr);
                var_dump($token);
                var_dump($replace);

                die;

                var_dump($replace['token']['left']['token']);
//                 die;
                if(isset($replace['token']['left']['token'])){
                        //moving replace to the most left parameter instead of operator position (needed somewhere else)
                        $replace['token']['nr'] = $replace['token']['left']['token']['nr'];

                        if(isset($token[$replace['token']['nr']])){
                            $result[$replace['token']['nr']] = $replace;
                        } else {
                            var_dump($token);
                            var_dump($replace);
                            die;
                        }


                        continue;

                }
            }
            $result[$nr] = $record;
        }
        return $result;
    }

    public static function operator_precedence($token=[]){
        return $token;
        if(!is_array($token)){
            return [];
        }
        $count = count($token);
        $parenthese_open = [
            'value' => '(',
            'type' => Token::TYPE_PARENTHESE_OPEN,
            'column' => null,
            'row' => null,
            'is_operator' => false,
            'is_precedence' => true,
            'depth' => 0,
            'token' => [
                'nr' => null
            ]
        ];
        $parenthese_close = $parenthese_open;
        $parenthese_close['value'] = ')';
        $parenthese_close['type'] = Token::TYPE_PARENTHESE_CLOSE;
        $precedence = [];
        $left = [];
        $right = [];
        $operator = [];
        foreach($token as $nr => $record){
            if(!isset($record['is_operator'])){
                var_Dump(debug_backtrace(true));
                var_dump($token);
                die;
            }
            $token[$nr] = $record;
            if($record['is_operator'] === true){
                if($operator){
                    break;
                }
                if(
                    in_array(
                        $record['value'],
                        [
                            '*',
                            '/',
                            '%'
                        ]
                    ) &&
                    !isset($record['is_precedence'])
                ){
                    $operator = $record;
                    continue;
                } else {
                    if(!isset($record['is_precedence'])){
                        $left = [];
                    } else {
                        $left[] = $record;
                    }
                    continue;
                }
            }
            if(empty($operator)){
                if(empty($left)){
                    $left_start_nr = $nr;
                }
                $left[] = $record;
            } else {
                $right[] = $record;
                $right_end_nr = $nr;
            }
        }
        if(!empty($right)){
            for($i = 0; $i < $left_start_nr; $i++){
                $precedence[] = $token[$i];
            }
            $is_open = false;
            $is_close = false;
            foreach($left as $part){
                if($is_open === false){
                    $parenthese_open['depth'] = $part['depth']+1;
                    $parenthese_close['depth'] = $parenthese_open['depth'];
                    $precedence[] = $parenthese_open;
                    $is_open = true;
                }
                $part['depth'] = $part['depth']+1;
                $precedence[] = $part;
            }
            $operator['depth'] = $operator['depth']+1;
            $operator['is_precedence'] = true;
            $precedence[] = $operator;
            foreach($right as $part){
                $part['depth'] = $part['depth']+1;
                $precedence[] = $part;
            }
            $precedence[] = $parenthese_close;
            for($i = $right_end_nr + 1; $i < $count; $i++){
                $precedence[] = $token[$i];
            }
            return Token::operator_precedence($precedence);
        }
        return $token;
    }

    public static function value_type($record=[], $attribute='execute'){
        if(!array_key_exists($attribute, $record)){
            $debug = debug_backtrace(true);
            var_Dump($debug);
            var_dump($attribute);
            var_dump($record);
            die;
        }
        $type = strtolower(gettype($record[$attribute]));
        switch($type){
            case 'bool' :
            case 'boolean' :
                $record['type'] = Token::TYPE_BOOLEAN;
                $record['is_operator'] = false;
            break;
            case 'null' :
                $record['type'] = Token::TYPE_NULL;
                $record['is_operator'] = false;
            break;
            case 'int' :
            case 'integer' :
                $record['type'] = Token::TYPE_INT;
                $record['is_operator'] = false;
            break;
            case 'float' :
            case 'double' :
                $record['type'] = Token::TYPE_FLOAT;
                $record['is_operator'] = false;
            break;
            case 'string' :
                $record['type'] = Token::TYPE_STRING;
                $record['is_operator'] = false;
            break;
            case 'array' :
                $record['type'] = Token::TYPE_ARRAY;
                $record['is_operator'] = false;
            break;
            case 'object' :
                $record['type'] = Token::TYPE_OBJECT;
                $record['is_operator'] = false;
            break;
        }
        return $record;
    }

    /**
     * \[0-7]{1,3}     the sequence of characters matching the regular expression is a character in octal notation, which silently overflows to fit in a byte (e.g. "\400" === "\000")
     *
     *
     *
     * @param Parse $parse
     * @param array $record
     * @param array $token
     * @return boolean
     */

    public static function value_string_escape($execute='', $char=''){
        $search = '\\\\' . $char;
        $escape = md5($search);
        $escape = Token::TYPE_REM . '-' . $escape;
        $execute = str_replace($search, $escape, $execute);
        $search = '\\' . $char;

        switch($char){
            case 'f' :
                $replace = "\f";
            break;
            case 'r' :
                $replace = "\r";
            break;
            case 'e' :
                $replace = "\e";
            break;
            case 'n' :
                $replace = "\n";
            break;
            case 't' :
                $replace = "\t";
            break;
        }
        $execute = str_replace($search, $replace, $execute);
        $replace = "\\" . $char;
        $execute = str_replace($escape, $replace, $execute);
        return $execute;
    }

    public static function value_string_execute(Parse $parse, $record=[], $token=[]){
        if(
            $record['type'] == Token::TYPE_STRING &&
            !isset($record['is_executed'])
        ){
            $execute = $record['value'];
            if(!empty($record['is_quote_double'])){
                $execute = Token::value_string_escape($execute, 'f');
                $execute = Token::value_string_escape($execute, 'r');
                $execute = Token::value_string_escape($execute, 'e');
                $execute = Token::value_string_escape($execute, 'n');
                $execute = Token::value_string_escape($execute, 't');
                $execute = $parse->compile($execute);
            }
            elseif(!empty($record['is_quote_single'])) {
                var_dump($execute);
                die;
            }
            $record['execute'] = $execute;
            $record['is_executed'] = true;
            $token[$record['token']['nr']] = $record;
        }
        elseif(
            in_array(
                $record['type'],
                [
                    Token::TYPE_DOT,
                    Token::TYPE_QUOTE_SINGLE
                ]
            ) &&
            !isset($record['is_executed'])
        ){
            $execute = $record['value'];
            if($execute = '\'\''){
                $execute = '';
                $record['type'] = Token::TYPE_STRING;
            }

            // var_dump($parse->data('module'));
            // die;

            $record['execute'] = $execute;
            $record['is_executed'] = true;
            $token[$record['token']['nr']] = $record;
        }
        return $token;
    }

    public static function value_variable_execute(Parse $parse, $record=[], $token=[]){
        if(!is_array($record)){
            return [];
        }
        if(isset($record['is_executed'])){
            /*
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $record['variable']['is_assign'] === true
            ){
                var_dump($record);
                die;
            }
            */
            $token[$record['token']['nr']] = $record;
            return $token;
        }
        if($record['type'] != Token::TYPE_VARIABLE){
            $token[$record['token']['nr']] = $record;
            return $token;
        }
        $attribute = substr($record['variable']['name'], 1);
        $record['execute'] = $parse->data($attribute);
        $keep = false;
        $token = Variable::modify($parse, $record, $token, $keep);
        $record = $token[$record['token']['nr']];
        $record['is_executed'] = true;
        $record = Token::value_type($record);
        $token[$record['token']['nr']] = $record;
        return $token;
    }

    public static function method_execute(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){
        if(!isset($method['type'])){
            return $token;
        }
        if($method['type'] != Token::TYPE_METHOD){
            return $token;
        }
        if(isset($method['method']['is_executed'])){
            var_dump('found');
            die;
            return $token;
        }
        if(substr($method['method']['name'], 0, 1) == '$'){
            $attribute = substr($method['method']['name'], 1);
            $method['method']['name'] = $parse->data($attribute);
            /*
            $keep = false;
            $token = Variable::modify($parse, $record, $token, $keep);
            $record = $token[$record['token']['nr']];
            $variable = substr($method['method']['name'], 1);
            */
        }
        $file_name = 'Function.' . ucfirst(strtolower($method['method']['name'])) . '.php';
        $function_name = 'function_' . str_replace('.', '_', $method['method']['name']);
        $location = $parse->data(Parse::DATA_DIR_FUNCTION);
        $is_method = false;


        var_dump(Parse::DATA_DIR_FUNCTION);
        die;

        foreach($location as $dir){
            $url = $dir . $file_name;
            $data_function = $parse->data(Parse::DATA_FUNCTION);
            if($data_function === null){
                $data_function =[];
                $parse->data(Parse::DATA_FUNCTION, $data_function);
            }
            if(
                in_array(
                    $url,
                    $data_function
                )
            ){
                $is_method = true;
                break;
            }
            elseif(
                $is_method === false &&
                File::exist($url)
            ){
                $is_method = true;
                require_once $url;

                $data_function = $parse->data(Parse::DATA_FUNCTION);
                $data_function[] = $url;
                $parse->data(Parse::DATA_FUNCTION, $data_function);
                break;
            }
        }
        if($is_method === true){
            if(!isset($method['method']['parameter'])){

                var_dump($token[$method['token']['nr']]);


                var_dump(debug_backtrace(true));
//                 var_dump($method);
                die;
            }
//             var_dump($method);
//             die;
            $method = Token::method_prepare($method);
            $method['method']['token_parameter'] = $method['method']['parameter'];

            if($method['method']['name'] == Core_for::FOR){
                foreach($method['method']['parameter'] as $nr => $parameter){
                    foreach($parameter as $expression_nr => $expression){
                        $execute = current($expression);
                        if($execute !== null){
                            if(Token::hold_execute($expression) === true){
                                //do nothing...
                            } else {
                                $token = Token::set_execute($parse, $expression, $execute, $token);
                                $execute = $token[$execute['token']['nr']];
                                unset($token[$execute['token']['nr']]);
                                $method['method']['parameter'][$nr][$expression_nr] = $execute['execute'];
                            }
                        } else {
                            var_dump($parameter);
                            die;
                        }
                    }

                }
            } else {
                foreach($method['method']['parameter'] as $nr => $parameter){
                    $execute = reset($parameter);
                    if($execute !== null){
                        if(Token::hold_execute($parameter) === true){

                        } else {
                            $token = Token::set_execute($parse, $parameter, $execute, $token);
                            $execute = $token[$execute['token']['nr']];
                            unset($token[$execute['token']['nr']]);
                            $method['method']['parameter'][$nr] = $execute['execute'];
                        }
                    } else {
                        var_dump($parameter);
                        die;
                    }
                }
            }
            $token[$method['token']['nr']]['execute'] = $function_name($parse, $method['method']['parameter'], $token, $method);
            $token[$method['token']['nr']]['is_executed'] = true;
        } else {
            $source = $parse->data('priya.parse.read.url');
            if($source !== null){
                throw new Exception('Couldn\'t find function: ' . $method['method']['name'] . ' on line: ' . $method['row'] . ' column: ' . $method['column'] . ' in file: ' . $source);
            } else {
                throw new Exception('Couldn\'t find function: ' . $method['method']['name'] . ' on line: ' . $method['row'] . ' column: ' . $method['column']);
            }
            $token[$method['token']['nr']]['is_executed'] = true;
            $token[$method['token']['nr']]['execute'] = null;
            $token[$method['token']['nr']]['type'] = Token::TYPE_NULL;
        }
        if(!isset($token[$method['token']['nr']]['is_cleaned'])){
            $token = Token::method_cleanup($token[$method['token']['nr']], $token, $tag_remove);
        }
        return $token;
    }

    public static function variable_is_assign($token=[]){
        foreach($token as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                !empty($record['variable']['is_assign'])
            ){
                return true;
            }
        }
        return false;
    }

    public static function variable_cleanup($variable=[], $token=[], $tag_remove=true){
        if(!isset($variable['token'])){
            return $token;
        }
        if($tag_remove === true){
            $before = null;
            $before_before =null;
            foreach($token as $nr => $record){
                if($nr == $variable['token']['nr']){
                    for($i = $nr - 1; $i >= 0; $i--){
                        if(isset($token[$i])){
                            if($before === null){
                                $before = $i;
                            }
                            elseif($before_before === null){
                                $before_before = $i;
                                break;
                            }
                        }
                    }
                }
            }
            if(
                isset($before) &&
                $token[$before]['type'] == Token::TYPE_CURLY_OPEN
                ){
                    unset($token[$before]);
                    /*
                    if(
                        !empty($variable['variable']['is_assign']) &&
                        isset($before_before) &&
                        $token[$before_before]['type'] == Token::TYPE_WHITESPACE
                        ){
                            //remove whitespace before
                            $explode = explode("\n", $token[$before_before]['value']);
                            if(isset($explode[1])){
                                $end = array_pop($explode);
                                $end = rtrim($end);
                                $explode[] = $end;
                                $token[$before_before]['value'] = implode("\n", $explode);
                            }
                    }
                    */
            }
            elseif(
                isset($before) &&
                isset($before_before) &&
                $token[$before]['type'] == Token::TYPE_WHITESPACE &&
                $token[$before_before]['type'] == Token::TYPE_CURLY_OPEN
            ){
                unset($token[$before]);
                unset($token[$before_before]);
                /*
                if(
                    !empty($variable['variable']['is_assign']) &&
                    isset($before_before_before) &&
                    $token[$before_before_before]['type'] == Token::TYPE_WHITESPACE
                ){
                    //remove whitespace before
                    $explode = explode("\n", $token[$before_before_before]['value']);
                    if(isset($explode[1])){
                        $end = array_pop($explode);
                        $end = rtrim($end);
                        $explode[] = $end;
                        $token[$before_before_before]['value'] = implode("\n", $explode);
                    }
                }
                */
            }
        }
        if(
            !empty($variable['variable']['is_assign']) ||
            !empty($variable['variable']['has_modifier'])
        ){
            if(!empty($variable['variable']['value'])){
                $end = end($variable['variable']['value']);
                for($i = $variable['token']['nr'] + 1; $i <= $end['token']['nr']; $i++){
                    unset($token[$i]);
                }
            }
        } else {
            //do nothing for now...
        }
        $end = end($token);
        if($tag_remove === true){
            $current = null;
            $current_current = null;
            for($i = $variable['token']['nr'] + 1; $i <= $end['token']['nr']; $i++){
                if(isset($token[$i])){
                    if($current === null){
                        $current = $i;
                    }
                    elseif($current_current === null){
                        $current_current = $i;
                        break;
                    }
                }
            }

            if($current !== null){
                if($token[$current]['type'] == Token::TYPE_CURLY_CLOSE){
                    unset($token[$current]);
                }
            }
            if($current_current !== null){
                if(
                    $token[$current]['type'] == Token::TYPE_WHITESPACE &&
                    $token[$current_current]['type'] == Token::TYPE_CURLY_CLOSE
                ){
                    unset($token[$current]);
                    unset($token[$current_current]);
                }
            }
        } else {
            throw new Exception('Tag remove is false and not implemented...');
            die;
        }
        $next = null;
        for($i = $variable['token']['nr'] + 1; $i <= $end['token']['nr']; $i++){
            if(
                $next === null &&
                isset($token[$i]
            )){
                $next = $i;
                break;
            }
        }
        if(
            $next !== null &&
            $token[$next]['type'] == Token::TYPE_WHITESPACE
        ){
            $explode = explode("\n", $token[$next]['value'], 2);
            if(isset($explode[1])){
                $token[$next]['value'] = $explode[1];
            }
        }
        if($variable['execute'] === null){
            $previous = null;
            for($i = $variable['token']['nr'] - 1; $i >= 0; $i--){
                if(
                    $previous === null &&
                    isset($token[$i]
                )){
                    $previous = $i;
                    break;
                }
            }
            if(
                $previous !== null &&
                $token[$previous]['type'] == Token::TYPE_WHITESPACE
            ){
                $explode = explode("\n", $previous['execute']);
                if(isset($explode[1])){
                    array_pop($explode);
                    $token[$previous]['execute'] = implode("\n", $explode);
                } else {
                    $token[$previous]['execute'] = $explode[0];
                }
            }
        } else {
            // var_dump($variable);
        }
        return $token;
    }

    public static function method_cleanup($method=[], $token=[], $tag_remove=true){
        if(!isset($method['token'])){
            return $token;
        }
        if(
            !isset($method['token']['nr']) ||
            !isset($method['token']['parenthese_close_nr'])
        ){
            return $token;
        }
        if(isset($method['token']['tag_close_nr'])){
            $close_nr = $method['token']['tag_close_nr'];
        } else {
            $close_nr = $method['token']['parenthese_close_nr'];
        }

        for($i = $method['token']['nr'] + 1; $i <= $close_nr; $i++){
            unset($token[$i]);
        }
        if($tag_remove === true){
            $previous = null;
            $previous_previous = null;
            for($i = $method['token']['nr'] - 1; $i >= 0 ; $i--){
                if(
                    $previous === null &&
                    isset($token[$i])
                ){
                    $previous = $i;
                    continue;
                }
                elseif(
                    $previous !== null &&
                    isset($token[$i])
                ){
                    $previous_previous = $i;
                    break;
                }
            }
            if(
                $previous !== null &&
                $previous_previous !== null &&
                $token[$previous]['type'] == Token::TYPE_WHITESPACE &&
                $token[$previous_previous]['type'] == Token::TYPE_CURLY_OPEN
            ){
                unset($token[$previous]);
                unset($token[$previous_previous]);
            }
            elseif(
                $previous !== null &&
                $token[$previous]['type'] == Token::TYPE_CURLY_OPEN
            ){
                unset($token[$previous]);
            }
            $end = end($token);
            $next = null;
            $next_next = null;
            for($i = $method['token']['parenthese_close_nr'] + 1; $i <= $end['token']['nr']; $i++){
                if(
                    $next === null &&
                    isset($token[$i])
                ){
                    $next = $i;
                    continue;
                }
                elseif(
                    $next !== null &&
                    isset($token[$i])
                ){
                    $next_next = $i;
                    break;
                }
            }
            if(
                $next !== null &&
                $next_next !== null &&
                $token[$next]['type'] == Token::TYPE_WHITESPACE &&
                $token[$next_next]['type'] == Token::TYPE_CURLY_CLOSE
            ){
                unset($token[$next]);
                unset($token[$next_next]);
            }
            elseif(
                $next !== null &&
                $token[$next]['type'] == Token::TYPE_CURLY_CLOSE
            ){
                unset($token[$next]);
            }
        }
        $next = null;
        for($i = $method['token']['nr'] + 1; $i <= $end['token']['nr']; $i++){
            if(isset($token[$i])){
                $next = $i;
                break;
            }
        }
        if($next !== null){
            if($token[$next]['type'] == Token::TYPE_WHITESPACE){
                $explode = explode("\n", $next['value'], 2);
                if(isset($explode[1])){
                    $token[$next]['token']['nr']['value'] = $explode[1];
                }
            }
        }
        if($method['execute'] === null){
            $previous = null;
            for($i = $method['token']['nr'] - 1; $i >= 0; $i++){
                if(isset($token[$i])){
                    $previous = $i;
                    break;
                }
            }
            if($token[$previous]['type'] == Token::TYPE_WHITESPACE){
                $explode = explode("\n", $next['value']);
                if(isset($explode[1])){
                    $token[$next]['token']['nr']['value'] = implode("\n", $explode);
                } else {
                    $token[$next]['token']['nr']['value'] = '';
                }
            }
        }

        /*
        if(!empty($method['token']['tag_close_nr'])){
            for($i = $method['token']['tag_close_nr'] - 1; $i >= 0; $i--){
                if(isset($token[$i])){
                    var_Dump($token[$i]);
//                     die;
                    $token[$i] = Token::remove_empty_line($token[$i], 'value', false);
                    break;
                }
            }
        }
        */
        return $token;
    }

    public static function value_method_execute(Parse $parse, $record=[], $token=[], $keep=false, $tag_remove=true){
        return Token::method_execute($parse, $record, $token, $keep, $tag_remove);
        if(!is_array($record)){
            return [];
        }
        if(isset($record['is_executed'])){
            if(
                isset($record['token']) &&
                isset($record['token']['nr']) &&
                isset($token[$record['token']['nr']])
            ){
                $token[$record['token']['nr']] = $record;
            }
            return $token;
        }
        if($record['type'] != Token::TYPE_METHOD){
            if(
                isset($record['token']) &&
                isset($record['token']['nr']) &&
                isset($token[$record['token']['nr']])
            ){
                $token[$record['token']['nr']] = $record;
            }
            return $token;
        }
        if(!isset($token[$record['token']['nr']])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            var_dump('hey uknown');
            die;
        }
        $keep = false;
        $file_name = 'Function.' . ucfirst(strtolower($record['method']['name'])) . '.php';
        $function_name = 'function_' . str_replace('.', '_', $record['method']['name']);
        $location = $parse->data(Parse::DATA_DIR_FUNCTION);
        $is_function = false;
        foreach($location as $dir){
            $url = $dir . $file_name;
            if($parse->data(Parse::DATA_FUNCTION . '.'  . $url) === true){
                $is_function = true;
                break;
            }
            elseif(
                $is_function === false &&
                File::exist($url)
            ){
                $is_function = true;
                require_once $url;
                $parse->data(Parse::DATA_FUNCTION . '.'  . $url, true);
                break;
            }
        }
        foreach($record['method']['parameter'] as $nr => $parameter){
            $execute = current($parameter);
            if($execute !== null){
                $token = Token::set_execute($parse, $parameter, $execute, $token);
                $execute = $token[$execute['token']['nr']];
                unset($token[$execute['token']['nr']]);
                $record['method']['parameter'][$nr] = $execute['execute'];
            }
        }
        $token = $function_name($parse, $record, $token, $keep);
        $token[$record['token']['nr']]['is_executed'] = true;
        return $token;
    }

    public static function hold_execute($execute=[]){
        $hold_execute = false;
        foreach($execute as $nr => $record){
            if(!empty($record['hold_execute'])){
                $hold_execute = true;
                break;
            }
        }
        return $hold_execute;
    }

    public static function value_string_add_dot($string=[], &$token=[]){
        $next = null;
        $next_next = null;
        $is_string = false;
        $is_improve = false;
        $end = end($token);
        $count = $end['token']['nr'];
        $skip = 0;
        for($i = $string['token']['nr'] + 1; $i <= $count; $i++){
            if(isset($token[$i])){
                if($next === null){
                    $next = $i;
                }
                elseif($next_next === null){
                    $next_next = $i;
                    break;
                }
            }
        }
        if(
            $next !== null &&
            $next_next !== null &&
            $token[$next]['type'] == Token::TYPE_WHITESPACE &&
            $token[$next_next]['type'] == Token::TYPE_DOT
        ){
            $is_improve = true;
            $string['value'] .= $token[$next_next]['value'];
            unset($token[$next]);
            unset($token[$next_next]);
        }
        elseif(
            $next !== null &&
            $token[$next]['type'] == Token::TYPE_DOT
        ){
            $is_improve = true;
            $string['value'] .= $token[$next]['value'];
            unset($token[$next]);
        }
        if($is_improve === true){
            foreach($token as $nr => $record){
                if($skip > 0){
                    $skip -= 1;
                    continue;
                }
                if(
                    $is_string === false &&
                    $record['token']['nr'] == $string['token']['nr']
                ){
                    $is_string = true;
                }
                elseif($is_string){
                    if(
                        in_array(
                            $record['type'],
                            Token::TYPE_STRING_BREAK
                        )
                    ){
                        break;
                    }
                    $string['value'] .= $record['value'];
                    unset($token[$nr]);
                }
            }
            $token[$string['token']['nr']] = $string;
        }
        return $string;
    }

    public static function value_string_improve($token=[]){
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_STRING){
                $record = Token::value_string_add_dot($record, $token);


            }
        }
        return $token;
    }

    public static function value_execute(Parse $parse, $execute=[], &$token=[], $keep=false, $tag_remove=true){
        if(!is_array($execute)){
            return [];
        }
        $list = [];
        $string = '';
        $execute = Token::value_string_improve($execute);
        foreach($execute as $nr => $record){
            if(
                isset($record['token']) &&
                isset($execute[$record['token']['nr']])
            ){
                $string .= $record['value'];
                if($record['type'] == Token::TYPE_WHITESPACE){
                    continue;
                }
                $list[] = $record;
            }
        }
        if(!isset($list[0])){
            var_dump($execute);
            $debug = debug_backtrace(true);
            var_dump($debug);
            die;
            throw new Exception('Need token record...');
            return [];
        }
        $value = array_pop($list);
        $token = Token::value_variable_execute($parse, $value, $token, $keep, $tag_remove);
        // die;
        $value = $token[$value['token']['nr']];
        $token = Token::value_method_execute($parse, $value, $token, $keep, $tag_remove);
        $value = $token[$value['token']['nr']];


        $value = Token::value_array_execute($parse, $value);
        $value = Token::value_object_execute($parse, $value);
        $token = Token::value_string_execute($parse, $value, $token);
        $value = $token[$value['token']['nr']];
        if(!isset($value['is_executed'])){
            var_dump($value);
            throw new Exception('Value needs to be executed...');
            return $value;
        }
        if(is_array($list) && !empty($list)){
            var_dump($string);
            var_dump($list);
            var_dump($value);
            die;
            $rtl = array_reverse($list);

            foreach($rtl as $nr => $record){
                if(
                    isset($record['direction']) &&
                    $record['direction'] == Token::DIRECTION_RTL
                ){
                    $value = Token::value_determine($value, $record);
                    unset($rtl[$nr]);
                }
            }
            $ltr = array_reverse($rtl);
            foreach($ltr as $nr => $record){
                if(
                    isset($record['direction']) &&
                    $record['direction'] == Token::DIRECTION_RTL
                ){
                    continue;
                }
                $value = Token::value_determine($value, $record);
                unset($ltr[$nr]);
            }
        }
        $value['value'] = $string;
        $token[$value['token']['nr']] = $value;
        // var_dump($token);
        return $value;
    }

    public static function value_array_execute(Parse $parse, $value=[]){
        if(!is_array($value)){
            return [];
        }
        elseif($value['type'] != Token::TYPE_ARRAY){
            return $value;
        }
        elseif(isset($value['execute'])){
            unset($value['token']['array']);
            return $value;
        }
        elseif(!isset($value['token'])){
            return $value;
        }
        elseif(!isset($value['token']['array'])){
            return $value;
        }
        $array = '';

        $value['token']['array'] = Token::create_object($value['token']['array']);
        $value['token']['array'] = Token::create_method($value['token']['array']);
        $value['token']['array'] = Token::create_assign($value['token']['array']);
        $value['token']['array'] = Token::create_modifier($value['token']['array']);

        foreach($value['token']['array'] as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            elseif($record['type'] == Token::TYPE_VARIABLE){
                $array .= $record['value'];
            }
            elseif($record['type'] == Token::TYPE_METHOD){
                $array .= $record['value'];
            }
            elseif(isset($record['execute'])){
                //add TYPE_STRING_PARSE for unparsed strings
                if($record['type'] == Token::TYPE_STRING){
                    $array .= '"' . str_replace('"', '\"', $record['execute']) . '"';
                } else {
                    $array .= $record['execute'];
                }

            } else {
                $array .= $record['value'];
            }
        }
        $array = json_decode($array);
        if(is_array($array)){
            $value['execute'] = $array;
        } else {
            $value['execute'] = [];
        }
        $value['is_executed'] = true;
        return $value;
    }

    public static function value_object_execute(Parse $parse, $value=[]){
        if(!is_array($value)){
            return [];
        }
        return $value;
    }

    public static function value_exclamation($value=[], $execute=[]){
        if(!is_array($value)){
            return [];
        }
        if(!is_array($execute)){
            return $value;
        }
        if(!isset($value['execute'])){
            $value['execute'] = $value['value'];
        }
        if(
            isset($execute['type']) &&
            $execute['type'] == Token::TYPE_EXCLAMATION
        ){
            $value['execute'] = ! (bool) $value['execute'];
            $value['type'] = Token::TYPE_BOOLEAN;
        }
        return $value;
    }

    public static function value_cast($value=[], $execute=[]){
        if(!is_array($value)){
            return [];
        }
        if(!is_array($execute)){
            return $value;
        }
        if(!isset($value['execute'])){
            $value['execute'] = $value['value'];
        }
        if(
            isset($execute['type']) &&
            $execute['type'] == Token::TYPE_CAST
        ){
            switch($execute['cast']){
                case Token::TYPE_BOOLEAN:
                    $value['execute'] = (bool) $value['execute'];
                    $value['type'] == Token::TYPE_BOOLEAN;
                break;
                case Token::TYPE_INT:
                    $value['execute'] = (int) $value['execute'];
                    $value['type'] = Token::TYPE_INT;
                break;
                case Token::TYPE_FLOAT:
                    $value['execute'] = (float) $value['execute'];
                    $value['type'] = Token::TYPE_FLOAT;
                break;
                case Token::TYPE_STRING:
                    $value['execute'] = (string) $value['execute'];
                    $value['type'] = Token::TYPE_STRING;
                break;
                case Token::TYPE_ARRAY:
                    $value['execute'] = (array) $value['execute'];
                    $value['type'] = Token::TYPE_ARRAY;
                break;
                case Token::TYPE_OBJECT:
                    $value['execute'] = (object) $value['execute'];
                    $value['type'] = Token::TYPE_OBJECT;
                break;
            }
        }
        return $value;
    }

    public static function value_determine($value=[], $execute=[]){
        $value = Token::value_exclamation($value, $execute);
        $value = Token::value_cast($value, $execute);
        return $value;
    }

    public static function array_calculate($left=[], $right=[], $calculation='+'){
        switch($calculation){
            case '+' :
                // array + array : add
                if(
                    $left['type'] == Token::TYPE_ARRAY &&
                    $right['type'] == Token::TYPE_ARRAY
                ){
                    $execute = $left['execute'];
                    foreach($right['execute'] as $right_nr => $right_value){
                        if(is_string($right_nr)){
                            if(isset($execute[$right_nr])){
                                continue;
                            } else {
                                $execute[$right_nr] = $right_value;
                            }
                        } else {
                            $execute[] = $right_value;
                        }
                    }
                    return $execute;
                }
                elseif(
                    $left['type'] == Token::TYPE_NULL &&
                    $right['type'] == Token::TYPE_ARRAY
                ){
                    return $right['execute'];
                }
                elseif(
                    $left['type'] == Token::TYPE_ARRAY &&
                    $right['type'] == Token::TYPE_NULL
                ){
                    return $left['execute'];
                }
            break;
            case '-' :
                if(
                    // array + array : if array value exists in both skip else add
                    // array - array : if array value exists in both unset else keep
                    // array * array : if array value exists in both keep else unset
                    // array / array : if array value exists in both keep else unset

                    // object + object : if object attribute exists in both skip else add
                    // object - object : if object attribute exists in both unset else keep
                    // object * object : if object attribute exists in both if value does not matter keep else unset
                    // object / object : if object attribute exists in both if value does matter keep else unset
                    // object - object : if array value in right array is present in array left value, unset array left value

                    $left['type'] == Token::TYPE_ARRAY &&
                    $right['type'] == Token::TYPE_ARRAY
                ){
                    $execute = $left['execute'];
                    foreach($right['execute'] as $right_nr => $right_value){
                        if(is_int($right_value)){

                        }
                        foreach($left['execute'] as $left_nr => $left_value){
                            if($left_value === $right_value){
                                unset($execute[$left_nr]);
                                break;
                            }
                        }
                    }
                    var_dump($right);
                    var_dump($execute);
                    die;
                    return $left['execute'] + $right['execute'];
                }
                elseif(
                    $left['type'] == Token::TYPE_NULL &&
                    $right['type'] == Token::TYPE_ARRAY
                    ){
                        return [];
                }
                elseif(
                    $left['type'] == Token::TYPE_ARRAY &&
                    $right['type'] == Token::TYPE_NULL
                ){
                        return $left['execute'];
                }
            break;
            case '*' :
            break;
            case '/' :
            break;
        }
        var_dump($calculation);
        var_dump($left);
        var_dump($right);
        var_dump($execute);
        die;
    }

    public static function set_remove($token=[], $replace=[]){
        $set = [];
        $is_set = false;
        $is_replaced = false;
        $highest = Token::depth_highest($token);
        foreach($token as $nr => $record){
            if($record['depth'] == $highest){
                $is_set = true;
            }
            if($is_set === true){
                if($is_replaced === false){
                    $list = [];
                    foreach($replace as $value){
                        $list[] = $value;
                    }
                    if(
                        isset($list[0]) &&
                        isset($list[1]) &&
                        isset($list[2]) &&
                        $list[0]['type'] == Token::TYPE_PARENTHESE_OPEN &&
                        $list[2]['type'] == Token::TYPE_PARENTHESE_CLOSE
                    ){
                        $token[$nr] = $list[1];
                        $token[$nr]['depth'] -= 1;
                    }
                    $is_replaced = true;
                    continue;
                }
                unset($token[$nr]);
                if($record['value'] == ')' && $record['depth'] == $highest){
                    break;
                }
            }
        }
        return $token;
    }

    /*
    public static function token_set_execute(Parse $parse, $record=[], $token=[], $keep=false, $tag_remove=true){
        return $token;
        var_dump($record);

        if(!isset($record['token'])){
            var_dump($record);
            die;
        }
//         $token[$record['token']['nr']] = $record;
//         var_dump($token);
        die;
        $count = 0;
        $record = Token::precedence($record);
        $has_operator = false;
        while(Token::set_has($record)){
            $set = Token::set_get($record);
            while(Token::operator_has($set)){
                $has_operator = true;
                $operator = Token::operator_get($set);
                try {
                    $token = Token::operator_execute($parse, $operator, $token);
                    $operator = $token[$operator['token']['nr']];
                    if(!isset($operator['is_executed'])){
                        var_Dump($operator);
                    } else {
                        var_dump($operator);
                    }
                    $set = Token::operator_remove($set, $operator);
                } catch (Exception $e) {
                    echo $e;
                    return $record;
                }
            }
            $record = Token::set_remove($record, $set);
            $count++;
        }
        while(Token::operator_has($record)){
            $has_operator = true;
            $operator = Token::operator_get($record);
            try {
                $token = Token::operator_execute($parse, $operator, $token);
                $operator = $token[$operator['token']['nr']];

                var_dump($record);
                die;

                $record = Token::operator_remove($record, $operator);
            } catch (Exception $e) {
                echo $e;
                return $record;
            }
        }
        if($has_operator === false){
            //             var_dump($record);

            if(Token::value_hold_execute($record) === true){
                $execute = Token::value_execute($parse, $record, $token);
            }
            //             var_Dump($execute);
            //             die;
            $list = [];
            $list[] = $execute;
        } else {
            $list = [];
            foreach($record as $nr => $value){
                $list[] = $value;
            }
        }
    }
    */
    public static function whitespace_remove($token=[]){
        $result = [];
        foreach($token as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                continue;
            }
            $result[$nr] = $record;
        }
        return $result;
    }


    public static function set_execute(Parse $parse, $set=[], $record=[], $token=[], $keep=false, $tag_remove=true){
        if(isset($set['is_executed'])){
            return $token;
        }
        if(Token::hold_execute($set) === true){
            return $token;
        }
        if(Token::variable_is_assign($set)){
            $rtl = array_reverse($set);
            foreach($rtl as $nr => $item){
                $item = $rtl[$nr];
                if(
                    $item['type'] == Token::TYPE_VARIABLE &&
                    !empty($item['variable']['is_assign'])
                ){
                    $set = Variable::assign($parse, $item, $set, $keep);
                    $set = Variable::cleanup($item, $set, false);
                    $item = $set[$item['token']['nr']];
                    $rtl[$nr] = $item;
                    foreach($rtl as $key => $value){
                        if(
                            $value['type'] == Token::TYPE_VARIABLE &&
                            !empty($value['variable']['is_assign'])
                        ){
                          foreach($value['variable']['value'] as $variable_value_nr => $variable_value_item){
                              if($variable_value_nr == $item['token']['nr']){
                                    $item['execute'] = $item['variable']['execute'];
                                    $rtl[$key]['variable']['value'][$variable_value_nr] = $item;
                              }
                          }
                        }
                    }
                }
            }
            // $set = assign set, one by one, might be in reverse...
        }
        $count = 0;
        $count_set = 0;
        $count_operator = 0;
        //operator_precedence is buggy at the moment
        $set = Token::operator_precedence($set);
        $has_operator = false;
        while(Token::set_has($set)){
            $count_set++;
            $set_get = Token::set_get($set);
            while(Token::operator_has($set_get)){
                $count_operator++;
                $has_operator = true;
                $operator = Token::operator_get($set_get);
                try {
                    $token = Token::operator_execute($parse, $operator, $token, $keep, $tag_remove);
                    if(!isset($token[$operator['token']['nr']])){
                        var_dump($token);
                        var_dump($operator);
                        die;
                    }
                    $operator = $token[$operator['token']['nr']];
                    $set_get = Token::operator_replace($set_get, $operator);
                } catch (Exception $e) {
                    echo $e;
                    return $token;
                }
            }
            $set = Token::set_remove($set, $set_get);
            $count++;
        }
        $count = 0;
        while(Token::operator_has($set)){
            $has_operator = true;
            $count++;
            $operator = Token::operator_get($set);
            try {
                $token = Token::operator_execute($parse, $operator, $token, $keep, $tag_remove);
                $operator = $token[$operator['token']['nr']];
                $set = Token::operator_replace($set, $operator);
            } catch (Exception $e) {
                echo $e;
                return $token;
            }
        }
        if($has_operator === false){
            $execute = Token::value_execute($parse, $set, $token, $keep, $tag_remove);
            if(!empty($record)){
                if($record['token']['nr'] == $execute['token']['nr']){
                    $token[$execute['token']['nr']] = $execute;
                } else {
                    $record['execute'] = $execute['execute'];
                    $record['is_executed'] = true;
                    $record['type'] = $execute['type'];
                    $token_nr = $record['token']['nr'];
                    // $record = $execute;
                    // $record['token']['nr'] = $token_nr;
                    /*

                    var_dump($record);
                    */
                    if(!isset($token[$record['token']['nr']])){
                        // var_dump(debug_backtrace)
                        var_dump($token);
                        var_dump($record);

                        // var_dump($token);
                        die;
                        throw new Exception('Parse error: record not available in token, please make sure the record is in the token...');
                    }
                    $token[$record['token']['nr']] = $record;
                }
            } else {
                var_dump('empty record');
                var_dump($execute);
                die;
            }
        } else {
            $set = Token::whitespace_remove($set);
            $execute = Token::array_shift($set, true);
            if(!empty($set)){
                var_dump($set);
                throw new Exception('Set should be empty...');
                return $token;
            } else {
                if($record['token']['nr'] == $execute['token']['nr']){
                    $token[$execute['token']['nr']] = $execute;
                } else {
                    if($record === false){
                        var_dump(debug_backtrace(true));
                        die;
                    }
                    $record['execute'] = $execute['execute'];
                    $record['is_executed'] = true;
                    $token[$record['token']['nr']] = $record;
                }
            }
        }
        return $token;
    }

    public static function string($token=[]){
        $string = '';
        $remove_whitespace = false;
        foreach($token as $nr => $record){
            if(isset($record['in_execution'])){
                $string .= $record['execute'];
                continue;
            }
            if(!isset($record['is_executed'])){
                if(isset($record['value'])){
                    $record['execute'] = $record['value'];
                }
            }
            if($record['type'] == Token::TYPE_LITERAL){
                $string .= $record['tag'];
                continue;
            }
            if(is_object($record['execute'])){
                var_Dump($string);
                var_dump($record);
                die;
            }
            $string .= $record['execute'];
        }
        return $string;
    }

    public static function literal_remove($string='', $list=[]){
        $is_literal = false;
        $shorten = 0;
        foreach($list as $nr => $tag){
            foreach($tag as $key => $position){
                if(
                    $key == '{literal}' &&
                    $is_literal == false
                ){
                    $explode = explode("\n", $string);
                    $is_replace = false;

                    foreach($explode as $explode_nr => $explode_value){
                        if(
                            $explode_nr == $position['row'] - 1 &&
                            stristr($explode[$explode_nr], $key) !== false
                        ){
                            $is_replace = true;
                        }
                        if($is_replace){
                            $shorten = strlen($key);
                            $explode[$explode_nr] =
                            substr($explode_value, 0, $position['column'] - 1) .
                            substr($explode_value, $position['column'] - 1 + $shorten);

                            unset($list[$nr]);
                            foreach($list as $list_nr => $list_value){
                                foreach($list_value as $list_key => $list_position){
                                    if($list_position['row'] -1 == $position['row'] - 1){
                                        $list[$list_nr][$list_key]['column'] = $list[$list_nr][$list_key]['column'] - $shorten;
                                    } else {
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                    $string = implode("\n", $explode);
                    $is_literal = true;
                }
                if(
                    $is_literal === true &&
                    $key == '{/literal}'
                ){
                    $explode = explode("\n", $string);
                    $is_replace = false;
                    foreach($explode as $explode_nr => $explode_value){
                        if(
                            $explode_nr == $position['row'] - 1 &&
                            stristr($explode[$explode_nr], $key) !== false
                        ){
                            $is_replace = true;
                        }
                        if($is_replace){
                            $shorten = strlen($key);
                            $explode[$explode_nr] =
                            substr($explode_value, 0, $list[$nr][$key]['column'] - 1) .
                            substr($explode_value, $list[$nr][$key]['column'] - 1 + $shorten);
                            unset($list[$nr]);
                            foreach($list as $list_nr => $list_value){
                                foreach($list_value as $list_key => $list_position){
                                    if($list_position['row'] - 1 == $position['row'] - 1){
                                        $list[$list_nr][$list_key]['column'] = $list[$list_nr][$list_key]['column'] - $shorten;
                                    } else {
                                        break 2;
                                    }
                                }
                            }
                            break;
                        }
                    }
                    $string = implode("\n", $explode);
                    $is_literal = false;
                }
            }
        }
        return $string;
    }

    public static function tag_explode($string=''){
        $explode = explode('{', $string);
        $open_count = 0;
        $close_count = 0;
        $record = [];
        $list = [];
        foreach ($explode as $nr => $value){
            if($nr == 0){
                $open_count++;
                continue;
            }
            $temp = explode('}', $value);
            $count = count($temp);
            if($count > 1){
                $close_count += $count - 1;
            }
            $record[] = '{';
            if($count > 1){
                foreach($temp as $temp_nr => $temp_value){
                    $record[] = $temp_value;
                    if($temp_nr == $count - 1){
                        //dont add }
                    } else {
                        $record[] = '}';
                    }
                }
            } else {
                $record[] = $temp[0];
            }
            if($open_count == $close_count && $open_count > 0){
                $list[] = implode('', $record);
                $open_count = 0;
                $close_count = 0;
                $record = array();
            }
            $open_count++;
        }
        return $list;
    }

    public static function tag_find($string=''){
        $tagged = array();
        if(!is_string($string)){
            return $tagged;
        }
        $explode = Token::tag_explode($string);
        $pattern = '/\{.*\}/s';
        $length = 0;
        $compare = $string;
        foreach($explode as $key => $value){
            $temp = explode($value, $compare, 2);

            $length += strlen($temp[0]) + strlen($value);
            preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);
            if(!empty($matches)){
                $match = current(current($matches));
                $part = substr($string, 0, $length);
                $temp = explode($match, $part);
                $end = array_pop($temp);
                $temp = implode($match, $temp);
                $temp = explode("\n", $temp);
                $row = count($temp);
                $column = strlen(end($temp)) + 1;
                $tagged[][$match] = [
                    'row' => $row,
                    'column' => $column
                ];
            }
            $compare = substr($string, $length);
        }
        return $tagged;
    }

    public static function remove_empty_line($record=[], $attribute='execute', $keep_bottom=true){
        if(
            isset($record['type']) &&
            isset($record[$attribute]) &&
            $record['type'] == Token::TYPE_WHITESPACE
        ){
            if($keep_bottom === true){
                $explode = explode("\n", $record[$attribute], 2);
                if(
                    isset($explode[1]) &&
                    trim($explode[0]) == ''
                ){
                    $record[$attribute] = $explode[1];
                }
            } else {
                $explode = explode("\n", $record[$attribute]);
                if(isset($explode[1])){
                    $end = array_pop($explode);
                    if(trim($end) == ''){
                        $record[$attribute] = implode("\n", $explode);
                    }
                }
            }
        }
        return $record;
    }
}