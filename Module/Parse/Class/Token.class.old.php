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
    const TYPE_COMMENT = 'comment';
    const TYPE_DOC_COMMENT = 'doc-comment';
    const TYPE_AMPERSAND = 'ampersand';
    const TYPE_QUESTIONMARK = 'questionmark';
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
    const TYPE_IS_MULTIPLY_EQUAL = 'is-plus-equal';
    const TYPE_IS_DIVIDE_EQUAL = 'is-plus-equal';
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
            $token[$nr] = $record;
            $column++;
            if($record['value'] == "\n"){
                $row++;
                $column = 1;
            }
        }
        $current = null;
        $count = $nr;
        $skip = 0;
        /*
        $previous_nr = null;
        $previous_record = null;
        $previous_previous_nr = null;
        $previous_previous_record = null;
        */
        foreach($token as $nr => $record){
            if($skip > 0){
                $skip--;
                continue;
            }
            if($current === null){
                $current = $nr;
            }
            $next = null;
            $next_next = null;
            if($nr < ($count - 1)){
                $next = $nr + 1;
            }
            if($nr < ($count - 2)){
                $next_next = $nr + 2;
            }
            if($next !== null && $token[$next]['type'] == $record['type']){
                if(
                    !in_array(
                        $record['type'],
                        [
                            Token::TYPE_PARENTHESE_OPEN,
                            Token::TYPE_PARENTHESE_CLOSE,
                            Token::TYPE_BRACKET_SQUARE_OPEN,
                            Token::TYPE_BRACKET_SQUARE_CLOSE,
                            Token::TYPE_CURLY_OPEN,
                            Token::TYPE_CURLY_CLOSE,
                        ]
                    )
                ){
                    $token[$current]['value'] .= $token[$next]['char'];
                    unset($token[$next]);
                    $skip = 1;
                    continue;
                }
            }
            elseif($next !== null && $token[$next]['type'] != $record['type']){
                $current = $nr;
            }
        }
        var_dump($token);
        die;

        $is_operator = false;
        $is_variable = false;
        $is_comment = false;
        $is_doc_comment = false;
        $is_number = false;
        $is_float = false;
        $is_hex = false;
        foreach($token as $nr => $record){
            unset($token[$nr]['char']);
            if($record['type'] == Token::TYPE_OPERATOR){
                if(substr($record['value'], 0, 3) == '/**'){
                    $explode = explode('*/', $record['value'], 2);
                    if(isset($explode[1])){
                        if(!empty($explode[1])){
                            $token[$nr]['value'] = $explode[1];
                        }
                    } else {
                        $is_doc_comment = true;
                        $comment_nr = $nr;
                        $token[$nr]['type'] = Token::TYPE_DOC_COMMENT;
                        continue;
                    }
                }
                $substr = substr($record['value'], 0, 2);
                if(
                    $substr == '/*' &&
                    !$is_doc_comment
                ){
                    $explode = explode('*/', $record['value'], 2);
                    if(isset($explode[1])){
                        if(!empty($explode[1])){
                            $token[$nr]['value'] = $explode[1];
                        }
                    } else {
                        $is_comment = true;
                        $comment_nr = $nr;
                        $token[$nr]['type'] = Token::TYPE_COMMENT;
                        continue;
                    }
                }
                elseif(
                    $substr == '//' &&
                    (
                        !$is_comment &&
                        !$is_doc_comment
                    )
                ){
                }
                if(
                    $is_comment ||
                    $is_doc_comment
                    ){
                        $explode = explode('*/', $record['value'], 2);
                        if(isset($explode[1])){
                            $is_comment = false;
                            $is_doc_comment = false;
                            if(empty($explode[1])){
                                $token[$comment_nr]['value'] .= $record['value'];
                                if($comment_nr != $nr){
                                    unset($token[$nr]);
                                    continue;
                                }
                            } else {
                                $token[$comment_nr]['value'] .= $explode[0] .'*/';
                                $token[$nr]['value'] = $explode[1];
                            }
                        }
                }
                switch ($record['value']){
                    case '=' :
                        if($token[$previous_nr]['value'] == '&'){
                            $token[$previous_nr]['value'] .= $record['value'];
                            $token[$previous_nr]['type'] = Token::TYPE_IS_AND_EQUAL;
                            unset($token[$nr]);
                        } else {
                            $token[$nr]['type'] = Token::TYPE_IS;
                        }
                    break;
                    case '+' :
                        $token[$nr]['type'] = Token::TYPE_IS_PLUS;
                    break;
                    case '-' :
                        $token[$nr]['type'] = Token::TYPE_IS_MINUS;
                    break;
                    case '*' :
                        $token[$nr]['type'] = Token::TYPE_IS_MULTIPLY;
                    break;
                    case '/' :
                        $token[$nr]['type'] = Token::TYPE_IS_DIVIDE;
                    break;
                    case '%' :
                        $token[$nr]['type'] = Token::TYPE_IS_MODULO;
                    break;
                    case '>' :
                        $token[$nr]['type'] = Token::TYPE_IS_GREATER;
                    break;
                    case '<' :
                        $token[$nr]['type'] = Token::TYPE_IS_SMALLER;
                    break;
                    case '==' :
                        $token[$nr]['type'] = Token::TYPE_IS_EQUAL;
                    break;
                    case '!=' :
                        $token[$nr]['type'] = Token::TYPE_IS_NOT_EQUAL;
                    break;
                    case '=>' :
                        $token[$nr]['type'] = Token::TYPE_IS_ARRAY_OPERATOR;
                    break;
                    case '->' :
                        $token[$nr]['type'] = Token::TYPE_IS_OBJECT_OPERATOR;
                    break;
                    case '<=' :
                        $token[$nr]['type'] = Token::TYPE_IS_SMALLER_EQUAL;
                    break;
                    case '>=' :
                        $token[$nr]['type'] = Token::TYPE_IS_GREATER_EQUAL;
                    break;
                    case '<>' :
                        $token[$nr]['type'] = Token::TYPE_IS_NOT_EQUAL;
                    break;
                    case '+=' :
                        $token[$nr]['type'] = Token::TYPE_IS_PLUS_EQUAL;
                    break;
                    case '-=' :
                        $token[$nr]['type'] = Token::TYPE_IS_MINUS_EQUAL;
                    break;
                    case '*=' :
                        $token[$nr]['type'] = Token::TYPE_IS_MULTIPLY_EQUAL;
                    break;
                    case '^=' :
                        $token[$nr]['type'] = Token::TYPE_IS_XOR_EQUAL;
                    break;
                    case '<<' :
                        $token[$nr]['type'] = Token::TYPE_IS_SMALLER_SMALLER;
                    break;
                    case '>>' :
                        $token[$nr]['type'] = Token::TYPE_IS_GREATER_GREATER;
                    break;
                    case '<<<' :
                        $token[$nr]['type'] = Token::TYPE_IS_SMALLER_SMALLER_SMALLER;
                    break;
                    case '>>>' :
                        $token[$nr]['type'] = Token::TYPE_IS_GREATER_GREATER_GREATER;
                    break;
                    case '<<=' :
                        $token[$nr]['type'] = Token::TYPE_IS_SMALLER_SMALLER_EQUAL;
                    break;
                    case '>>=' :
                        $token[$nr]['type'] = Token::TYPE_IS_GREATER_GREATER_EQUAL;
                    break;
                    case '++' :
                        $token[$nr]['type'] = Token::TYPE_IS_PLUS_PLUS;
                    break;
                    case '--' :
                        $token[$nr]['type'] = Token::TYPE_IS_MINUS_MINUS;
                    break;
                    case '===' :
                        $token[$nr]['type'] = Token::TYPE_IS_IDENTICAL;
                    break;
                    case '!==' :
                        $token[$nr]['type'] = Token::TYPE_IS_NOT_IDENTICAL;
                    break;
                    case '<=>' :
                        $token[$nr]['type'] = Token::TYPE_IS_SPACESHIP;
                    break;
                }
            }
            elseif(
                $record['type'] == Token::TYPE_AMPERSAND &&
                $record['value'] == '&&'
            ){
                $token[$nr]['type'] == Token::TYPE_BOOLEAN_AND;
            }
            elseif(
                $record['type'] == Token::TYPE_PIPE &&
                $record['value'] == '||'
            ){
                $token[$nr]['type'] == Token::TYPE_BOOLEAN_OR;
            }
            elseif(
                $record['type'] == Token::TYPE_QUESTIONMARK &&
                $record['value'] == '??'
            ){
                $token[$nr]['type'] == Token::TYPE_IS_COALESCE;
            }
            if($record['type'] == Token::TYPE_STRING){
                $lowercase = strtolower($record['value']);
                switch($lowercase){
                    case 'false' :
                        $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                        $token[$nr]['execute'] =  false;
                        $is_number = false;
                        $is_hex = false;
                    break;
                    case 'true' :
                        $token[$nr]['type'] = Token::TYPE_BOOLEAN;
                        $token[$nr]['execute'] =  true;
                        $is_number = false;
                        $is_hex = false;
                    break;
                    /*
                    case 'null' :
                        $token[$nr]['type'] = Token::TYPE_NULL;
                        $token[$nr]['execute'] =  null;
                        $is_number = false;
                        $is_hex = false;
                    case 'class' :
                        $token[$nr]['type'] = Token::TYPE_CLASS;
                        $is_number = false;
                        $is_hex = false;
                    break;
                    case 'function' :
                        $token[$nr]['type'] = Token::TYPE_FUNCTION;
                        $is_number = false;
                        $is_hex = false;
                    break;
                    case 'trait' :
                        $token[$nr]['type'] = Token::TYPE_TRAIT;
                        $is_number = false;
                        $is_hex = false;
                    break;
                    case 'private' :
                        $token[$nr]['type'] = Token::TYPE_PRIVATE;
                        $is_number = false;
                        $is_hex = false;
                        break;
                    case 'protected' :
                        $token[$nr]['type'] = Token::TYPE_PROTECTED;
                        $is_number = false;
                        $is_hex = false;
                        break;
                    case 'public' :
                        $token[$nr]['type'] = Token::TYPE_PUBLIC;
                        $is_number = false;
                        $is_hex = false;
                        break;
                    */
                }
            }
            if(
                $is_variable === false &&
                $record['type'] == Token::TYPE_NUMBER
            ){
                if($is_float){
                    $token[$number_nr]['value'] .= $record['value'];
                    $token[$number_nr]['value'] += 0;
                    $token[$number_nr]['type'] = Token::TYPE_FLOAT;
                    $token[$number_nr['execute']] = $token[$number_nr]['value'];
                    $is_float = false;
                    $is_number = false;
                    $previous_nr = $number_nr;
                    unset($number_nr);
                    unset($number_record);
                    unset($token[$nr]);
                    continue;
                }
                elseif($is_hex){
                    $token[$number_nr]['value'] .= $record['value'];
                    $token[$number_nr['execute']] = $token[$number_nr]['value'];
                    $token[$number_nr]['type'] = Token::TYPE_HEX;
                    unset($token[$nr]);
                    $previous_nr = $number_nr;
                    continue;
                }
                $is_number = true;
                $number_nr = $nr;
                $number_record = $record;
                $previous_nr = $number_nr;
                continue;
            }
            elseif(
                $is_number &&
                $record['type'] == Token::TYPE_STRING
            ){
                if(
                    $is_hex === false &&
                    strtolower(substr($record['value'],0, 1)) == 'x'
                ){
                    $is_hex = true;
                    $is_variable = false;
                    $token[$number_nr]['value'] .= $record['value'];
                    $token[$number_nr['execute']] = $token[$number_nr]['value'];
                    $token[$number_nr]['type'] = Token::TYPE_HEX;
                    unset($token[$nr]);
                    $previous_nr = $number_nr;
                    continue;
                }
                if($is_hex){
                    $token[$number_nr]['value'] .= $record['value'];
                    unset($token[$nr]);
                    $previous_nr = $number_nr;
                    continue;
                }
            }
            elseif($record['type'] == Token::TYPE_VARIABLE){
                if($is_number){
                    if($is_hex){

                    }
                    elseif(
                        substr($token[$number_nr]['value'], 0, 1) == '0' &&
                        strlen($token[$number_nr]['value']) > 1
                    ){
                        $token[$number_nr]['type'] = Token::TYPE_OCT;
                        $token[$number_nr['execute']] = $token[$number_nr]['value'];
                    } else {
                        $token[$number_nr]['value'] += 0;
                        $token[$number_nr]['type'] = Token::TYPE_INT;
                        $token[$number_nr['execute']] = $token[$number_nr]['value'];
                    }
                    unset($number_nr);
                    unset($number_record);
                    $is_number = false;
                    $is_float = false;
                }
                $is_variable = true;
                $variable_nr = $nr;
                $variable_record = $record;
                continue;
            }
            elseif(
                $is_variable &&
                !in_array(
                    $record['type'],
                    [
                        Token::TYPE_STRING,
                        Token::TYPE_NUMBER,
                        Token::TYPE_DOT
                    ]
                )
            ){
                $is_variable = false;
            }
            if($is_comment){
                $token[$comment_nr]['value'] .= $record['value'];
                if($nr != $comment_nr){
                    unset($token[$nr]);
                    $previous_nr = $comment_nr;
                    continue;
                }
            }
            elseif($is_doc_comment){
                $token[$comment_nr]['value'] .= $record['value'];
                if($nr != $comment_nr){
                    unset($token[$nr]);
                    $previous_nr = $comment_nr;
                    continue;
                }
            }
            elseif($is_number){
                if($record['type'] == Token::TYPE_DOT){
                    $token[$number_nr]['value'] .= '.';
                    $is_float = true;
                    $is_hex = false;
                    unset($token[$nr]);
                    $previous_nr = $number_nr;
                    continue;
                } else {
                    if($is_hex){
                        $is_hex = false;
                    }
                    elseif(
                        substr($token[$number_nr]['value'], 0, 1) == '0' &&
                        strlen($token[$number_nr]['value']) > 1
                    ){
                        $token[$number_nr]['type'] = Token::TYPE_OCT;
                        $is_hex = false;
                    } else {
                        $token[$number_nr]['value'] += 0;
                        $token[$number_nr]['type'] = Token::TYPE_INT;
                        $is_hex = false;
                    }
                    $is_number = false;
                }
            }
            elseif($is_variable){
                if($is_hex){
                    $is_hex = false;
                    unset($number_nr);
                    unset($number_record);
                }
                $token[$variable_nr]['value'] .= $record['value'];
                unset($token[$nr]);
                $previous_nr = $variable_nr;
                continue;
            }
            $previous_nr = $nr;
        }
        $counter = 0;
        $depth = 0;
        $quote_single_toggle = null;
        $quote_single_open = null;
        $quote_double_toggle = null;
        $quote_double_open = null;
        $paranthese_open = null;
        $result = [];
        foreach($token as $nr => $record){
            $record['depth'] = $depth;
            if($quote_single_open){
                if($record['type'] == Token::TYPE_QUOTE_SINGLE && $token[$previous_nr]['type']  == Token::TYPE_BACKSLASH){
                    $quote_single_open['value'] .= $record['value'];
                    continue;
                }
                if($record['type'] != Token::TYPE_QUOTE_SINGLE){
                    $quote_single_open['value'] .= $record['value'];
                }
            }
            elseif($quote_double_open){
                if($record['type'] == Token::TYPE_QUOTE_DOUBLE && $token[$previous_nr]['type']  == Token::TYPE_BACKSLASH){
                    $quote_double_open['value'] .= $record['value'];
                    continue;
                }
                if($record['type'] != Token::TYPE_QUOTE_DOUBLE){
                    $quote_double_open['value'] .= $record['value'];
                }

            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_OPEN){
                $count = substr_count($record['value'], '(');
                if($token[$previous_nr]['type'] == Token::TYPE_BACKSLASH){
                    $count -= 1;
                }
                $depth += $count;
                $record['depth'] = $depth;
                if($paranthese_open === null){
                    $paranthese_open = $counter;
                }

            }
            elseif($record['type'] == Token::TYPE_PARENTHESE_CLOSE){
                $count = substr_count($record['value'], ')');
                if($token[$previous_nr]['type'] == Token::TYPE_BACKSLASH){
                    $count -= 1;
                }
                $depth -= $count;
                $record['depth'] = $depth;
                if($paranthese_open !== null && $depth == $result[$paranthese_open]['depth']){
                    $method_name = '';
                    $has_string = false;
                    for($i = $paranthese_open; $i > 0; $i--){
                        if(
                            in_array(
                                $result[$i]['type'],
                                [
                                    Token::TYPE_WHITESPACE,
                                    Token::TYPE_PARENTHESE_OPEN,
                                    Token::TYPE_PARENTHESE_CLOSE,
                                    Token::
                                ]
                            ) &&
                            $has_string
                        ){

                        }
                        if($result[$i]['type'] == Token::TYPE_STRING){
                            $has_string = true;
                        }
                        //>this.self ()
                        $part = $result[$i];
                    }
                    $paranthese_open = null;


                }

            }
            if($record['type'] == Token::TYPE_QUOTE_SINGLE){
                $is_quote_single = true;
                $count = substr_count($record['value'], '\'');
                for($i=0; $i < $count; $i++){
                    $record['value'] = '\'';
                    if($quote_single_open){
                        $quote_single_open['value'] .= '\'';
                        $quote_single_open['type'] = Token::TYPE_STRING;
                        $i = $quote_single_open['nr'] + 1;
                        for($i; $i < $counter; $i++){
                            unset($result[$i]);
                        }
                        $i = $quote_single_open['nr'];
                        unset($quote_single_open['nr']);
                        unset($quote_single_open['toggle']);
                        $result[$i] = $quote_single_open;
                        $quote_single_open = null;
                        $counter = $i + 1;
                        continue;
                    }
                    if(!$quote_single_toggle){
                        $quote_single_toggle = true;
                        $record['toggle'] = 'open';
                        if($token[$previous_nr]['type'] != Token::TYPE_BACKSLASH){
                            $quote_single_open = $record;
                            $quote_single_open['nr'] = $counter;
                        }
                    } else {
                        $quote_single_toggle = null;
                        $record['toggle'] = 'close';
                    }
                    $record['column'] += $i;
                    unset($record['toggle']);
                    $result[$counter] = $record;
                    $counter++;
                }
                $previous_nr = $nr;
                continue;
            }
            elseif($record['type'] == Token::TYPE_QUOTE_DOUBLE){
                $is_quote_double = true;
                $count = substr_count($record['value'], '"');
                for($i=0; $i < $count; $i++){
                    $record['value'] = '"';
                    if($quote_double_open){
                        $quote_double_open['value'] .= '"';
                        $quote_double_open['type'] = Token::TYPE_STRING;
                        $i = $quote_double_open['nr'] + 1;
                        for($i; $i < $counter; $i++){
                            unset($result[$i]);
                        }
                        $i = $quote_double_open['nr'];
                        unset($quote_double_open['nr']);
                        unset($quote_double_open['toggle']);
                        $result[$i] = $quote_double_open;
                        $quote_double_open = null;
                        $counter = $i + 1;
                        continue;
                    }
                    if(!$quote_double_toggle){
                        $quote_double_toggle = true;
                        $record['toggle'] = 'open';
                        if($token[$previous_nr]['type'] != Token::TYPE_BACKSLASH){
                            $quote_double_open = $record;
                            $quote_double_open['nr'] = $counter;
                        }
                    } else {
                        $quote_double_toggle = null;
                        $record['toggle'] = 'close';
                    }
                    $record['column'] += $i;
                    unset($record['toggle']);
                    $result[$counter] = $record;
                    $counter++;
                }
                $previous_nr = $nr;
                continue;
            }
            $result[$counter] = $record;
            $previous_nr = $nr;
            $counter++;
        }
        if($quote_double_open){ //can be true

        }
        return $result;
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
            case '&' :
                return Token::TYPE_AMPERSAND;
            break;
            case '|' :
                return Token::TYPE_PIPE;
            break;
            case '?' :
                return Token::TYPE_QUESTIONMARK;
            break;
            case '\\' :
                return Token::TYPE_BACKSLASH;
                break;
            case ';' :
                return Token::TYPE_SEMI_COLON;
            break;
            case ':' :
                return Token::TYPE_COLON;
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
                return Token::TYPE_OPERATOR;
            break;
            case '!' :
                return Token::TYPE_EXCLAMATION;
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