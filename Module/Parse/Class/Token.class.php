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
    const TYPE_LITERAL = 'literal';
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
        unset($token);
        $prepare = Token::prepare($prepare, $count);
        return $prepare;
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
                            Token::TYPE_PARENTHESE_OPEN,
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
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $token[$variable_nr]['variable']['has_modifier'] = true;
                        $token[$variable_nr]['value'] = $value; //$token[$variable_nr]['variable']['name'];
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
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['operator'] = $token[$next]['value'];
                        $token[$variable_nr]['value'] = $value; //$token[$variable_nr]['variable']['name'];
                        unset($token[$variable_nr]['variable']['has_modifier']);
                        $variable_nr = null;
                        $skip += 1;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    }
                }
                if(
                    $next !== null &&
                    $next_next !== null &&
                    $variable_nr !== null &&
                    $token[$next]['type'] === Token::TYPE_WHITESPACE &&
                    $token[$next_next]['is_operator'] === true
                ){
                    if($token[$next_next]['value'] == '|'){
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $token[$variable_nr]['variable']['has_modifier'] = true;
                        $token[$variable_nr]['value'] = $value; // $token[$variable_nr]['variable']['name'];
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
                        $token[$variable_nr]['variable']['is_assign'] = true;
                        $token[$variable_nr]['variable']['operator'] = $token[$next_next]['value'];
                        $token[$variable_nr]['value'] = $value; // $token[$variable_nr]['variable']['name'];
                        unset($token[$variable_nr]['variable']['has_modifier']);
                        $variable_nr = null;
                        $skip += 2;
                        unset($token[$nr]);
                        $previous_nr = $nr;
                        continue;
                    } else {
                        $value .= $record['value'];
                        $token[$variable_nr]['variable']['name'] .= $record['value'];
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
                    $value .= $record['value'];
                    $token[$variable_nr]['value'] = $value; // $token[$variable_nr]['variable']['name'];
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
                    $token[$quote_single['nr']]['execute'] = str_replace('\\\'','\'', substr($token[$quote_single['nr']]['value'], 1, -1));
                    $token[$quote_single['nr']]['is_executed'] = true;
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
                }
            } else {
                if($quote_double){
                    $quote_double = null;
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
                    if(
                        isset($token[$i]) &&
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
            /* wrong interpertation of octal....
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

    public static function method_prepare($method=[]){
        $argument = [];
        $count = -1;
        if(!isset($method['method']['parameter'])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            var_dump($method);
            die;
        }

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
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '+' :
                        $record['type'] = Token::TYPE_IS_PLUS;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '-' :
                        $record['type'] = Token::TYPE_IS_MINUS;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '*' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '/' :
                        $record['type'] = Token::TYPE_IS_DIVIDE;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '%' :
                        $record['type'] = Token::TYPE_IS_MODULO;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '>' :
                        $record['type'] = Token::TYPE_IS_GREATER;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '<' :
                        $record['type'] = Token::TYPE_IS_SMALLER;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case ':' :
                        $record['type'] = Token::TYPE_COLON;
                        return $record;
                    case '!' :
                        $record['type'] = Token::TYPE_EXCLAMATION;
                        $record['is_operator'] = false;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '?' :
                        $record['type'] = Token::TYPE_QUESTION;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '&' :
                        $record['type'] = Token::TYPE_AMPERSAND;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '|' :
                        $record['type'] = Token::TYPE_PIPE;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                }
                $record['is_operator'] = false;
            break;
            case 2 :
                switch($record['value']){
                    case '==' :
                        $record['type'] = Token::TYPE_IS_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '!=' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '=>' :
                        $record['type'] = Token::TYPE_IS_ARRAY_OPERATOR;
                        return $record;
                    case '->' :
                        $record['type'] = Token::TYPE_IS_OBJECT_OPERATOR;
                        return $record;
                    case '<=' :
                        $record['type'] = Token::TYPE_IS_SMALLER_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '>=' :
                        $record['type']= Token::TYPE_IS_GREATER_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '<>' :
                        $record['type'] = Token::TYPE_IS_NOT_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '+=' :
                        $record['type'] = Token::TYPE_IS_PLUS_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '-=' :
                        $record['type'] = Token::TYPE_IS_MINUS_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '*=' :
                        $record['type'] = Token::TYPE_IS_MULTIPLY_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '/=' :
                        $record['type'] = Token::TYPE_IS_DIVIDE_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '%=' :
                        $record['type'] = Token::TYPE_IS_MODULO_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '^=' :
                        $record['type'] = Token::TYPE_IS_XOR_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '&=' :
                        $record['type'] = Token::TYPE_IS_AND_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '|=' :
                        $record['type'] = Token::TYPE_IS_OR_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '<<' :
                        $record['type'] = Token::TYPE_IS_SMALLER_SMALLER;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '>>' :
                        $record['type'] = Token::TYPE_IS_GREATER_GREATER;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '++' :
                        $record['type'] = Token::TYPE_IS_PLUS_PLUS;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '--' :
                        $record['type'] = Token::TYPE_IS_MINUS_MINUS;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '**' :
                        $record['type'] = Token::TYPE_IS_POWER;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
                        return $record;
                    case '::' :
                        $record['type'] = Token::TYPE_DOUBLE_COLON;
                        return $record;
                    case '&&' :
                        $record['type'] = Token::TYPE_BOOLEAN_AND;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '||' :
                        $record['type'] = Token::TYPE_BOOLEAN_OR;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
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
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '!==' :
                        $record['type'] = Token::TYPE_IS_NOT_IDENTICAL;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '/**' :
                        $record['is_operator'] = false;
                        $record['type'] = Token::TYPE_DOC_COMMENT;
                        return $record;
                    case '<=>' :
                        $record['type'] = Token::TYPE_IS_SPACESHIP;
                        $record['direction'] = TOKEN::DIRECTION_LTR;
                        return $record;
                    case '**=' :
                        $record['type'] = Token::TYPE_IS_POWER_EQUAL;
                        $record['direction'] = TOKEN::DIRECTION_RTL;
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
        $activate = [];
        $count = 0;
        foreach($token as $nr => $record){
            $activate[] = $record;
            $count++;
        }
        if(empty($name)){
            return $activate;
        }
        $compare = strtolower($name);
        $tag_open_nr = null;
        $is_tag = false;
        $is_tagged = false;
        $is_slash = false;
        $is_literal = false;
        $is_tag_open = false;
        $minimum = 0;
        $skip = 0;
        $tag = '';
        foreach($activate as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            $next = null;
            $next_next = null;
            $next_next_next = null;
            if(($nr + 1) < $count){
                $next = $nr + 1;
            }
            if(($nr + 2) < $count){
                $next_next = $nr + 2;
            }
            if(($nr + 3) < $count){
                $next_next_next = $nr + 3;
            }
            $match = null;
            if($record['type'] == Token::TYPE_STRING){
                $match = strtolower($record['value']);
            }
            if($match != $name){
                $match = null;
            }
            if(
                $tag_open_nr !== null &&
                $is_tag === true &&
                $is_slash === false
            ){
                $activate[$tag_open_nr]['value'] .= $record['value'];
                $is_tag_open = true;
                $match_next = null;
                $match_next_next = null;
                $match_next_next_next = null;
                if($next !== null){
                    $match_next = strtolower($activate[$next]['value']);
                }
                if($next_next !== null){
                    $match_next_next = strtolower($activate[$next_next]['value']);
                }
                if($next_next_next !== null){
                    $match_next_next_next = strtolower($activate[$next_next_next]['value']);
                }
                if(
                    !(
                        $record['type'] == Token::TYPE_WHITESPACE &&
                        $match_next == $compare
                    ) &&
                    !(
                        $record['type'] == Token::TYPE_WHITESPACE &&
                        $activate[$next]['value'] == '/' &&
                        (
                            $match_next_next == $compare ||
                            $match_next_next_next == $compare
                        )
                    )
                ){
                    $tag .= $record['value'];
                }
            }
            if(
                (
                    $match == $compare &&
                    $activate[$next]['type'] == Token::TYPE_WHITESPACE &&
                    $activate[$next_next]['value'] == $tag_close
                ) ||
                (
                    $match == $compare &&
                    $activate[$next]['value'] == $tag_close
                )
            ){
                $match = null;
                for($i = $nr - 1; $i >= $minimum; $i--){
                    if($activate[$i]['type'] == Token::TYPE_WHITESPACE){
                        continue;
                    }
                    elseif($activate[$i]['value'] == '/'){
//                         var_dump($i);
                        $slash_next = null;
                        $slash_next_next = null;
                        if($i + 1 <= $count){
                            $slash_next = $activate[$i + 1];
                        }
                        if($i + 2 <= $count){
                            $slash_next_next = $activate[$i + 2];
                        }
                        if(
                            $slash_next !== null &&
                            strtolower($slash_next['value']) == $compare
                        ){
                            $is_slash = true;
                        }
                        elseif(
                            $slash_next_next !== null &&
                            strtolower($slash_next_next['value']) == $compare
                        ){
                            $is_slash = true;
                        }
                        continue;
                    }
                    elseif($activate[$i]['value'] == $tag_open){
                        //complete
                        if($tag_open_nr === null){
                            $is_tag = true;
                            $tag_open_nr = $i;
                        }
                        break;
                    } else {
                        break;
                    }
                }
                if(
                    $next !== null &&
                    $tag_open_nr !== null &&
                    $is_slash === false &&
                    $is_literal === false
                ){
                    for($i = $tag_open_nr + 1; $i <= $nr; $i++){
                        $activate[$tag_open_nr]['value'] .= $activate[$i]['value'];
                    }
                    if(
                        $next_next !== null &&
                        $activate[$next]['type'] == Token::TYPE_WHITESPACE
                    ){
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'] . $activate[$next_next]['value'];
                    } else {
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'];
                    }
                    $tag = $tag_open . $compare . $tag_close;
                    $activate[$tag_open_nr]['type'] = $compare;
                    $is_literal = true;
                }
                elseif(
                    $next !== null &&
                    $tag_open_nr !== null &&
                    $is_slash === false &&
                    $is_literal === true
                ){
                    if(
                        $next_next !== null &&
                        $activate[$next]['type'] == Token::TYPE_WHITESPACE
                    ){
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'] . $activate[$next_next]['value'];
                    } else {
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'];
                    }
                    $tag .= $tag_close;
                }
                elseif(
                    $next !== null &&
                    $tag_open_nr !== null &&
                    $is_slash === true
                 ){
                    $unset = null;
                    if(strtolower($record['value']) == $compare){
                        if($is_tag_open === false){
                            $tag = $activate[$tag_open_nr]['value'];
                            $activate[$tag_open_nr]['value'] .= '/' . $record['value'];
                            $tag .= '/' . $compare;
                            $activate[$tag_open_nr]['type'] = $compare .'-close';
                        }
                        if($activate[$next]['type'] == Token::TYPE_WHITESPACE && $activate[$next_next]['value'] == $tag_close){
                            $activate[$tag_open_nr]['value'] .= $activate[$next]['value'] . $activate[$next_next]['value'];
                            $unset = $next_next;
                        }
                        elseif($activate[$next]['value'] == $tag_close){
                            $activate[$tag_open_nr]['value'] .= $activate[$next]['value'];
                            $unset = $next;
                        }
                    }
                    elseif(
                        $next_next !== null &&
                        $activate[$next]['type'] == Token::TYPE_WHITESPACE &&
                        strtolower($activate[$next_next]['value']) == $compare
                     ){
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'] . $activate[$next_next]['value'];
                        $unset = $next_next;
                    }
                    elseif(strtolower($activate[$next]['value']) == $compare) {
                        $activate[$tag_open_nr]['value'] .= $activate[$next]['value'];
                        $unset = $next;
                    }
                    if($unset !== null){
                        $tag .= $tag_close;
                        for($i = $tag_open_nr + 1; $i <= $unset; $i++){
                            unset($activate[$i]);
                        }
                        $activate[$tag_open_nr]['tag'] = $tag;

                        $literal = [
                            $tag_open . $name . $tag_close,
                            $tag_open . '/' . $name . $tag_close
                        ];

                        $activate[$tag_open_nr]['tag'] = str_ireplace($literal, $literal, $activate[$tag_open_nr]['tag']);
                        if($is_execute){
                            if($is_tag_open){
                                $execute = explode($tag_open . $compare . $tag_close, $activate[$tag_open_nr]['tag'], 2);
                                $execute = implode('', $execute);
                                $execute = explode($tag_open . '/' . $compare . $tag_close, $execute, 2);
                                $execute = implode('', $execute);
                            } else {
                                $execute = $activate[$tag_open_nr]['tag'];
                            }
                            $activate[$tag_open_nr]['execute'] = $execute;
                        }
                        $skip += ($unset - $nr);
                    }
                    $tag_open_nr = null;
                    $is_slash  = false;
                    $is_tag = false;
                    $is_literal = false;
                    $is_tagged = true;
                    $is_tag_open = false;
                    $tag = '';
                    continue;
                }
                if(
                    $next !== null &&
                    $activate[$next]['type'] == Token::TYPE_WHITESPACE
                ){
                    $skip += 2;
                } else {
                    $skip += 1;
                }
            }
        }
        if(
            $tag_open_nr !== null &&
            $is_tagged === false &&
            $require_end === false
        ){
            for($i = $tag_open_nr + 1; $i <= $count; $i++){
                unset($activate[$i]);
                $skip += 1;
            }
            $activate[$tag_open_nr]['tag'] = $tag;
            $literal = [
                $tag_open . $name . $tag_close,
                $tag_open . '/' . $name . $tag_close
            ];
            $activate[$tag_open_nr]['tag'] = str_ireplace($literal, $literal, $activate[$tag_open_nr]['tag']);
            if($is_execute){
                $explode = explode("\n", $activate[$tag_open_nr]['tag'], 2);
                $trim = rtrim($explode[0]);
                if($trim == $tag_open . $name . $tag_close){
                    $activate[$tag_open_nr]['execute'] = str_replace($literal, '', $explode[0] . $explode[1]);
                } else {
                    $activate[$tag_open_nr]['execute'] = str_replace($literal, '', $activate[$tag_open_nr]['tag']);
                }
            }
        }
        elseif($is_tagged === false && $tag_open_nr !== null){
            throw new Exception('End tag required for: ' . $tag_open . $name . $tag_close);
        }
        return $activate;
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
        foreach($token as $nr => $record){
            if($record['value'] == '{'){
                $depth++;
                if(
                    $object_start === null &&
                    $depth == 1
                ){
                    $object_start = $nr;
                }
            }
            elseif($record['value'] == '}'){
                $string .= $record['value'];
                $object[] = $record;
                if(
                    $object_start !== null &&
                    $depth == 1
                ){
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
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'float' :
                    case 'double' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_FLOAT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'int' :
                    case 'integer' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_INT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'string' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_STRING;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'array' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_ARRAY;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
                        $is_cast = true;
                        break;
                    case 'object' :
                        $token[$parenthese_open]['value'] = $value . ')';
                        $token[$parenthese_open]['cast'] = Token::TYPE_OBJECT;
                        $token[$parenthese_open]['type'] = Token::TYPE_CAST;
                        $token[$parenthese_open]['direction'] = TOKEN::DIRECTION_RTL;
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

    public static function create_modifier($token=[]){
        if(!is_array($token)){
            return [];
        }
        $has_modifier_nr = null;
        $modifier_start_nr = null;
        foreach($token as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $has_modifier_nr === null &&
                isset($record['variable']['has_modifier'])
            ){
                $has_modifier_nr = $nr;
                continue;
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
            if($has_modifier_nr !== null && $modifier_start_nr !== null){
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
                    for($i = $has_modifier_nr + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                    $name = '';
                    $counter = -1;
                    $parameter = [];
                    $modifier_start_nr = $nr;
                    $is_parameter = false;
                    $has_parameter = false;
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
            $location = $parse->data('priya.parse.dir.modifier');
            $is_modifier = false;
            foreach($location as $dir){
                $url = $dir . $file_name;
                if($parse->data('priya.parse.modifier.' . $url) === true){
                    $is_modifier = true;
                    break;
                }
                elseif(
                    $is_modifier === false &&
                    File::exist($url)
                ){
                    $is_modifier = true;
                    require_once $url;
                    $parse->data('priya.parse.modifier.' . $url, true);
                    break;
                }
            }
            if($is_modifier === true){
                $modify['token_parameter'] = $modify['parameter'];
                foreach($modify['parameter'] as $nr => $parameter){
                    $execute = current($parameter);
                    if($execute !== null){
                        if(Token::hold_execute($parameter) === true){

                        } else {
                            $token = Token::set_execute($parse, $parameter, $execute, $token, $keep, $tag_remove);
                            $execute = $token[$execute['token']['nr']];
                            unset($token[$execute['token']['nr']]);
                            $modify['parameter'][$nr] = $execute['execute'];
                        }
                    } else {
                        var_dump($parameter);
                        die;
                    }
                }
                $token = $function_name($parse, $variable, $modify['parameter'], $token, $keep);
                $variable = $token[$variable['token']['nr']];
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
        $assign = [];
        foreach($token as $record){
            $assign[] = $record;
        }
        $count = count($assign);
        $assign_start_nr = null;
        $need_new_operator = false;
        $left_nr = null;
        $operator_nr = null;
        $depth = 0;
        $to = null;
        foreach($assign as $nr => $record){
            if(
                $record['type'] == Token::TYPE_VARIABLE &&
                $record['variable']['is_assign'] === true &&
                !isset($record['variable']['value'])
            ){
                $value = $record['value'];
                $variable_value = [];
                $direction = true; // true = left, false = right
                for($i = $nr + 1; $i < $count; $i++){
                    if(!isset($assign[$i])){
                        continue;
                    }
                    elseif($assign[$i]['type'] == Token::TYPE_WHITESPACE){
                        $value .= $assign[$i]['value'];
                        if($assign_start_nr !== null && !empty($variable_value)){
                            $variable_value[] = $assign[$i];
                        }
                        $to = $i -1;
                        continue;
                    }
                    elseif($assign[$i]['type'] == Token::TYPE_CAST){
                        $value .= $assign[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[] = $assign[$i];
                        }
                        continue;
                    }
                    elseif($assign[$i]['type'] == Token::TYPE_PARENTHESE_OPEN){
                        $value .= $assign[$i]['value'];
                        if(
                            $need_new_operator === false &&
                            $assign_start_nr !== null
                        ){
                            $variable_value[] = $assign[$i];
                            $to = $i;
                            $depth++;
                            continue;
                        }
                        elseif($need_new_operator === true){
                            $to = $i;
                            break;
                        }
                        $depth++;
                    }
                    elseif($assign[$i]['type'] == Token::TYPE_PARENTHESE_CLOSE){
                        $value .= $assign[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[] = $assign[$i];
                            $to = $i;
                            $depth--;
                            continue;
                        }
                        $depth--;
                    }
                    elseif($assign[$i]['type'] == Token::TYPE_EXCLAMATION){
                        $value .= $assign[$i]['value'];
                        if($assign_start_nr !== null){
                            $variable_value[] = $assign[$i];
                            $to = $i;
                            continue;
                        }
                    }
                    elseif(
                        $assign[$i]['is_operator'] == true &&
                        $assign[$i]['value'] == $record['variable']['operator'] &&
                        $depth == 0 &&
                        $assign_start_nr === null
                    ){
                        $value .= $assign[$i]['value'];
                        $assign_start_nr = $i;
                        $to = $i;
                        continue;
                    }
                    if($assign_start_nr !== null){
                        if($assign[$i]['is_operator'] === true){
                            if(
                                in_array(
                                    $assign[$i]['value'],
                                    Token::TYPE_ASSIGN
                                )
                            ){
                                //might throw wrong place for assignment error
                                break;
                            }
                            $to = $i;
                            $variable_value[] = $assign[$i];
                            $value .= $assign[$i]['value'];
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
                            $variable_value[] = $assign[$i];
                            $value .= $assign[$i]['value'];
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
                                $variable_value[] = $assign[$i];
                                $value .= $assign[$i]['value'];
                                $right_nr = $i;
                                $direction = true;
                                $need_new_operator = true;
                            } else {
                                break;
                            }
                        }
                    }
                }
                $end = end($variable_value);
                if($end['type'] == Token::TYPE_WHITESPACE){
                    array_pop($variable_value);
                    $value = rtrim($value);
                }
                $assign[$nr]['value'] = $value;
                $assign[$nr]['variable']['value'] = $variable_value;
                for($j = $nr + 1; $j <= $to; $j++){
                    unset($assign[$j]);
                }
                $assign_start_nr = null;
                $need_new_operator = false;
                $left_nr = null;
                $operator_nr = null;
                $to = null;
                $value = '';
            }
        }
        return $assign;
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
                } else {
                    $string .= $record['value'];
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
            }
            elseif($method_nr !== null){
                $name .= $record['value'];
                if(
                    $record['type'] == Token::TYPE_PARENTHESE_OPEN &&
                    $depth === null
                ){
                    $depth = $record['depth'];
                    $count = 0;

                    for($i = $method_nr + 1; $i <= $nr; $i++){
                        unset($token[$i]);
                    }
                }
                elseif(
                    $depth !== null &&
                    $record['type'] == Token::TYPE_COMMA &&
                    $depth == $record['depth']
                ){
                    $count++;
                }
                elseif(
                    $depth !== null &&
                    $record['type'] == Token::TYPE_PARENTHESE_CLOSE &&
                    $depth === $record['depth']
                ){
                    foreach($parameter as $parameter_nr => $parameter_value){
//                         $parameter[$parameter_nr] = Token::prepare($parameter_value, count($parameter_value));
                        $parameter[$parameter_nr] = Token::create_string($parameter[$parameter_nr]);
                        $parameter[$parameter_nr] = Token::create_cast($parameter[$parameter_nr]);
                        $parameter[$parameter_nr] = Token::create_array($parameter[$parameter_nr]);
                        $parameter[$parameter_nr] = Token::create_object($parameter[$parameter_nr]);
                        $parameter[$parameter_nr] = Token::create_method($parameter[$parameter_nr]);
                        //for now: no assign & no modifier
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
                    $parameter[$count][$record['token']['nr']] = $record;
                    unset($token[$nr]);
                }
            }
        }
        return $token;
    }

    public static function create($token=[], $tag_open_nr=null, $tag_close_nr=null){
        if(!is_array($token)){
            return [];
        }
        $create = [];
        $is_whitespace_before = false;

        if($tag_open_nr !== null && $tag_close_nr !== null){
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
            $current = current($create);
            if($current['type'] == Token::TYPE_WHITESPACE){
                array_shift($create);
            }
        }
        $end = end($create);
        if($end['type'] == Token::TYPE_WHITESPACE){
            array_pop($create);
        }
        $create = Token::create_string($create);
        $create = Token::create_cast($create);
        $create = Token::create_array($create);
        $create = Token::create_object($create);
        $create = Token::create_method($create);
        $create = Token::create_assign($create);
        $create = Token::create_modifier($create);
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
        return $operator;
    }

    public static function operator_execute($parse, $operator=[], $token=[], $keep=false, $tag_remove=true){
        if(!is_array($operator)){
            return [];
        }
        if(isset($operator['is_executed'])){
            return $operator;
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
        //can't do value type yet... ?
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

    public static function precedence($token=[]){
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
            return Token::precedence($precedence);
        }
        return $token;
    }

    public static function value_type($record=[], $attribute='execute'){
        if(!array_key_exists($attribute, $record)){
            $debug = debug_backtrace(true);
            var_Dump($debug);
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

    public static function value_string_execute(Parse $parse, $record=[], $token=[]){
        if(
            $record['type'] == Token::TYPE_STRING &&
            substr($record['value'], 0, 1) == '"' &&
            substr($record['value'], -1, 1) == '"'
        ){
            $string = substr($record['value'], 1, -1);
            $string = $parse->compile($string);
            $record['execute'] = $string;
            $record['is_executed'] = true;
            $token[$record['token']['nr']] = $record;
        }
        return $token;
    }

    public static function value_variable_execute(Parse $parse, $record=[], $token=[]){
        if(!is_array($record)){
            return [];
        }
        if(isset($record['execute'])){
            $token[$record['token']['nr']] = $record;
            return $token;
        }
        if($record['type'] != Token::TYPE_VARIABLE){
            return $token;
        }
        $attribute = substr($record['variable']['name'], 1);
        $record['execute'] = $parse->data($attribute);
        $record['is_executed'] = true;
        $record = Token::value_type($record);

//         var_dump($record);

        $token[$record['token']['nr']] = $record;


//         var_dump($record);

        /*
        if(!isset($token[$method['token']['nr']]['is_cleaned'])){
            $token = Token::method_cleanup($token[$method['token']['nr']], $token, $tag_remove);
        }
        */


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
        if(!isset($token[$method['token']['nr']])){
            $debug = debug_backtrace(true);
            var_dump($debug);

//             var_dump($token);
            var_dump($method);
            die;
            var_dump('hey uknown');
            die;
        }
        $file_name = 'Function.' . ucfirst(strtolower($method['method']['name'])) . '.php';
        $function_name = 'function_' . str_replace('.', '_', $method['method']['name']);
        $location = $parse->data('priya.parse.dir.function');
        $is_method = false;
        foreach($location as $dir){
            $url = $dir . $file_name;
            if($parse->data('priya.parse.function.' . $url) === true){
                $is_method = true;
                break;
            }
            elseif(
                $is_method === false &&
                File::exist($url)
            ){
                $is_method = true;
                require_once $url;
                $parse->data('priya.parse.modifier.' . $url, true);
                break;
            }
        }
        if($is_method === true){
            if(!isset($method['method']['parameter'])){
                var_dump(debug_backtrace(true));
                var_dump($method);
                die;
            }
//             var_dump($method);
//             die;
            $method = Token::method_prepare($method);
            $method['method']['token_parameter'] = $method['method']['parameter'];
            foreach($method['method']['parameter'] as $nr => $parameter){
                $execute = current($parameter);
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
            $token = $function_name($parse, $method, $token, $keep, $tag_remove);
            $token[$method['token']['nr']]['is_executed'] = true;
        } else {
            $source = $parse->data('priya.parse.read.url');
            if($source !== null){
                throw new Exception('Couldn\'t find function: ' . $method['method']['name'] . ' on line: ' . $method['row'] . ' column: ' . $method['column'] . ' in file: ' . $source);
            } else {
                throw new Exception('Couldn\'t find function: ' . $method['method']['name'] . ' on line: ' . $method['row'] . ' column: ' . $method['column']);
            }
        }
        if(!isset($token[$method['token']['nr']]['is_cleaned'])){
            $token = Token::method_cleanup($token[$method['token']['nr']], $token, $tag_remove);
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
        for($i = $method['token']['nr'] - 1; $i >= 0 ; $i--){
            if(isset($token[$i])){
                $explode = explode("\n", $token[$i]['value']);
                $end = array_pop($explode);
                $end = rtrim($end);
                $explode[] = $end;
                $token[$i]['value'] = implode("\n", $explode);
                break;
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
        $location = $parse->data('priya.parse.dir.function');
        $is_function = false;
        foreach($location as $dir){
            $url = $dir . $file_name;
            if($parse->data('priya.parse.function.' . $url) === true){
                $is_function = true;
                break;
            }
            elseif(
                $is_function === false &&
                File::exist($url)
            ){
                $is_function = true;
                require_once $url;
                $parse->data('priya.parse.modifier.' . $url, true);
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

    public static function value_execute(Parse $parse, $execute=[], &$token=[], $keep=false, $tag_remove=true){
        if(!is_array($execute)){
            return [];
        }
        $list = [];
        $string = '';

        /*
        if(!isset($execute['token'])){
            var_dump($execute);
//             die;
        }

        elseif(!isset($token[$execute['token']['nr']])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            var_dump('hey');
            die;
        }
        */
//         var_dump($execute);

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
            $debug = debug_backtrace(true);
            var_dump($debug);
            die;
            var_dump($execute);
            var_Dump($string);
            var_dump($list);
            die;
            $debug = debug_backtrace(true);
            var_dump($debug);
            die;
            throw new Exception('Need token record...');
            return [];
        }
        $value = array_pop($list);
//         var_dump($token);
//         var_dump($value);
//         die;
        $token = Token::value_variable_execute($parse, $value, $token, $keep, $tag_remove);

        if(!isset($token[$value['token']['nr']])){
            var_dump($value);
            var_dump($value['token']['nr']);
            var_dump($token);
            die;
            $debug = debug_Backtrace(true);
            var_dump($debug);
            die;
            var_Dump($token);
            var_dump($value);
            die;
        }



        $value = $token[$value['token']['nr']];

//         var_dump($value['token']['nr']);
//         var_dump($token[$value['token']['nr']]);
//         var_dump($token);


        $token = Token::value_method_execute($parse, $value, $token, $keep, $tag_remove);

        //value_method_execute adds unwanted token to token

        $value = $token[$value['token']['nr']];

//         var_dump($value);

        $value = Token::value_array_execute($parse, $value);
        $value = Token::value_object_execute($parse, $value);

//         var_dump($value);

        $token = Token::value_string_execute($parse, $value, $token);
//         var_dump($token);
//         die;
        $value = $token[$value['token']['nr']];
        if(!isset($value['is_executed'])){
            var_dump($value);
            die;
            throw new Exception('Value needs to be executed...');
            return $value;
        }
        if(is_array($list)){
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
//         var_dump($set);
        if(isset($set[0])){
            $debug = debug_backtrace(true);
            var_dump($debug);
            die;
        }
        if(isset($set['is_executed'])){
            return $set;
        }
        if(Token::hold_execute($set) === true){
            return $set;
        }
        $count = 0;
        $set = Token::precedence($set);
        $has_operator = false;
        while(Token::set_has($set)){
            $set_get = Token::set_get($set);
            while(Token::operator_has($set_get)){
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

                    if(!isset($token[$record['token']['nr']])){
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
            $execute = array_shift($set);
            if(!empty($set)){
                var_dump($set);
                var_dump($execute);
                die;
                throw new Exception('Set should be empty...');
                return $token;
            } else {
                if(!isset($record['token'])){
                    var_dump(debug_backtrace(true));
//                     var_dump($record);
                    die;
                }
                if($record['token']['nr'] == $execute['token']['nr']){
                    $token[$execute['token']['nr']] = $execute;
                } else {
                    $record['execute'] = $execute['execute'];
                    $record['is_executed'] = true;
                    $token[$record['token']['nr']] = $record;
                }
            }
        }
        if(isset($token[42]) && $token[42]['type'] == Token::TYPE_FLOAT){
            var_dump($token);
            die;
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