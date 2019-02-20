<?php

namespace Priya\Module\Parser;

use stdClass;
use Exception;

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
    const TYPE_SET = 'set';
    const TYPE_OPEN = 'open';
    const TYPE_CLOSE = 'close';
    const TYPE_METHOD = 'method';
    const TYPE_EXCLAMATION = 'exclamation';
    const TYPE_CONTROL = 'control';
    const TYPE_WHILE = 'while';

    const LITERAL_OPEN = '{literal}';
    const LITERAL_CLOSE = '{/literal}';


    public static function all($token=''){
        $tokens = @token_get_all('<?php $variable=' . $token . ';');
        array_shift($tokens); //remove php tag
        array_shift($tokens); //remove $variable
        array_shift($tokens); //remove =
        if(end($tokens) == ';'){
            array_pop($tokens); //remove ;
        } else {
            // on // this should do...
            $temp = end($tokens);
            $key = key($tokens);
            $temp[1] = substr($temp[1],0, -1);
            $tokens[$key] = $temp;
        }

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
                        $tokens[$key][2] = 'T_CURLY_OPEN';
                    break;
                    case '}' :
                        $tokens[$key][2] = 'T_CURLY_CLOSE';
                    break;
                    case ',' :
                        $tokens[$key][2] = 'T_COMMA';
                    break;
                    case '.' :
                        $tokens[$key][2] = 'T_DOT';
                    break;
                    case '=' :
                        $tokens[$key][2] = 'T_IS';
                    break;
                    case '!' :
                        $tokens[$key][2] = 'T_EXCLAMATION';
                    break;
                    case ';' :
                        $tokens[$key][2] = 'T_SEMI_COLON';
                    break;
                    case '"' :
                        $tokens[$key][2] = 'T_DOUBLE_QUOTE';
                    break;
                    case ':' :
                        $tokens[$key][2] = 'T_COLON';
                    break;
                    case '?' :
                        $tokens[$key][2] = 'T_QUESTION_MARK';
                    break;
                    case '$' :
                        $tokens[$key][2] = 'T_DOLLAR_SIGN';
                    break;
                    case '@' :
                        $tokens[$key][2] = 'T_AT';
                    break;
                    case '&&' :
                        $tokens[$key][2] = 'T_BOOLEAN_AND';
                    break;
                    case '`' :
                        $tokens[$key][2] = 'T_BACKTICK';
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
        if(empty($tokens)){
            return $tokens;
        }
        $result = array();
        $record = array();
        $set_depth = 0;
        $parse = '';
//         var_dump($tokens);
        $tokens = Token::fix($tokens);
        foreach($tokens as $nr => $token){
            if(isset($token[1])){
                if(is_array($token[1])){
                    var_dump($token);
                    die;
                    var_dump(debug_backtrace(true));
                    die;
                }
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
            if(
                $record['type'] == Token::TYPE_INT ||
                $record['type'] == Token::TYPE_FLOAT
            ){
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
            }
            elseif(Token::is_bracket($token, Token::TYPE_OPEN)){
                $set_depth++;
                $record['set']['depth'] = $set_depth;
            }
            elseif(Token::is_bracket($token, Token::TYPE_CLOSE)){
                $record['set']['depth'] = $set_depth;
                $set_depth--;
            }
            else {
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

    public static function format_json($tokens=array()){
        $requirement = false;
        if(Token::is_bracket($record, Token::TYPE_OPEN) && Token::is_bracket(end($record) ,  Token::TYPE_CLOSE)){
            $requirement = true;
        }
        if(empty($requirement)){
            return false;
        }
    }

    public static function create_object($value= '', $attribute='', $parser=null){
        $parse = Token::parse($value);
        $object_start = false;
        $object_end = false;
        foreach($parse as $nr => $record){
            if($record['value'] == '{'){
                $object_start = true;
            }
            if($record['value'] == '}'){
                $object_end = true;
            }
            if($record['type'] == Token::TYPE_OPERATOR){
                return array();
            }
            if(
                $object_start === true &&
                $object_end === true
            ){
                $value = Token::string($value, $parser);
                $result = array();
                $result['type'] = Token::TYPE_OBJECT;
                if($record['is_cast'] === true){
                    $explode = explode($record['cast'], $value, 2);
                    $$result['value'] = ltrim($explode[1], ') ');
                } else {
                    $result['value'] = $value;
                }
                $result['attribute'] = $attribute;
                $result['original'] = $result['value'];
                $result['value'] = Token::object($result['value']);
                if(is_object($result['value'])){
                    foreach($result['value'] as $key => $assign){
                        $parser->data($attribute . '.' . $key, $assign);
                    }
                }
                $result['value'] = $parser->compile($result['value'], $parser->data(), true);
                if(!is_object($result['value'])){
                    return array();
                }
                return $result;
                break;
            }
        }
        return array();
    }

    public static function create_array($value= '', $parser=null){
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
                if(is_array($value)){
                    $value = Token::string($value, $parser);
                }
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

    public static function exclamation($record = array()){
        if(
            isset($record['has_exclamation']) &&
            $record['has_exclamation'] === true
        ){
            if($record['invert'] === true){
                if(empty($record['value'])){
                    $record['value'] = true;
                } else {
                    $record['value'] = false;
                }
            } else {
                $record['value'] = (bool) $record['value'];
            }
        }
        return $record;
    }

    public static function fix($tokens=array()){
        $result = array();
        //find " in tokens and then { and }
        $in_string = false;
        $in_bracket = false;
//         var_dump($tokens);
        if(empty($tokens)){
            var_dump(debug_backtrace(true));
            die;
        }
        foreach($tokens as $nr => $record){
            if(
                isset($record[2]) &&
                $record[2] == 'T_DOUBLE_QUOTE'
            ){
                $in_string = true;
            }
            if(
                !$in_string
            ){
                $result[] = $record;
                continue;
            }
            if(
                isset($record[2]) &&
                $record[2] == 'T_ENCAPSED_AND_WHITESPACE'
            ){
                $explode = explode('{', $record[1], 2);
                if(isset($explode[1])){
                    $in_bracket = true;
                    $before = Token::all($explode[0]);
                    foreach($before as $part){
                        $result[] = $part;
                    }

                    $curly = array();
                    $curly[0] = -1;
                    $curly[1] = '{';
                    $curly[2] = 'T_CURLY_OPEN';
                    $result[] = $curly;

                    $after = Token::all($explode[1]);
                    foreach($after as $part){
                        $result[] = $part;
                    }
                } else {
                    $explode = explode('}', $record[1], 2);
                    if(isset($explode[1])){
                        $before = Token::all($explode[0]);
                        foreach($before as $part){
                            $result[] = $part;
                        }

                        $curly = array();
                        $curly[0] = -1;
                        $curly[1] = '}';
                        $curly[2] = 'T_CURLY_CLOSE';
                        $result[] = $curly;

                        $after = Token::all($explode[1]);
                        foreach($after as $part){
                            $result[] = $part;
                        }
                    } else {
                        $result[] = $record;
                    }
                }
            } else {
                $result[] = $record;
            }

        }
        return $result;
    }

    public static function variable($parse=array(), $attribute=null, $parser=null, $keep=false){
        $parse = Variable::fix($parse);
        $result = array();
        $is_variable = false;
        $unset = false;
        foreach($parse as $nr => $record){
            if(!isset($record['value'])){
                continue;
            }
            if(!isset($record['type'])){
                continue;
            }
            $result[$nr] = $record;
            if($record['value'] == '{'){
                $unset = $nr;
            }
            if($record['value'] == '}' && $is_variable){
                //end of variable
                $is_variable = false;
                if(!empty($unset)){
                    foreach($unset as $part){
                        unset($result[$part]);
                    }
                    $unset = false;
                }
                unset($result[$nr]);
            }
            if($is_variable && is_array($unset)){
                $unset[] = $nr;
            }
            if($record['type'] == Token::TYPE_VARIABLE){
                $is_variable = true;
                if($unset){
                    if(is_array($unset)){
                        foreach($unset as $part){
                            unset($result[$part]);
                        }
                    } else {
                        unset($result[$unset]);
                    }
                    $unset = array();
                }
                if($attribute !== null){
                    $record['value'] = str_replace('$this.', '$' . $attribute . '.', $record['value']);
                }
                $modifier = Token::modifier($parse, $record);

                $original = $record['value'];
                $record['value'] = Variable::replace($record['value'], $modifier, $keep, $parser);
                if($original == $record['value'] && $keep){
                    $record['is_keep'] = true;
                    if($record['type'] == Token::TYPE_VARIABLE){
                        $record['is_variable'] = true;
                        $record['variable'] = $original;
                    }
                    $result[$nr] = $record;
                    $parse[$nr] = $record;
                    continue;
                }
                if($record['exclamation_count'] % 2 == 1){
                    $record['invert'] = true;
                } else {
                    $record['invert'] = false;
                }
                $record = Token::Exclamation($record);
                if($record['type'] == Token::TYPE_VARIABLE){
                    $record['is_variable'] = true;
                    $record['variable'] = $original;
                }
                $record['type'] = Variable::type($record['value']);
                $record = Token::cast($record);
                $result[$nr] = $record;
                $parse[$nr] = $record;
            }
        }
        return $result;
    }

    public static function modifier($parse=array(), $record=array()){
        $modifier = array();
        $is_variable = false;
        $is_modifier = false;
        foreach($parse as $nr => $item){
            if($item == $record){
                $is_variable= $nr;
                continue;
            }
            if($is_variable){
                if($item['value'] == '}'){ //might need set depth
                    $is_variable = false;
                    $is_modifier = false;
                    continue;
                }
                if($item['value'] == '|'){ // might need set depth
                    $is_modifier = $nr;
                    continue;
                }
            }
            if($is_modifier){
                $modifier[$is_modifier][] = $item;
            }

        }
        return $modifier;
    }

    /**
     * @todo
     * - add cast
     */
    public static function create_equation($parse=null, $parser=null){
        $set_counter = 0;
        if(Operator::has($parse) === false){
            if(count($parse) == 1){
                $record = reset($parse);
                if(is_bool($record['value'])){
                    return $record['value'];
                }
                elseif(is_numeric($record['value'])){
                    return $record['value'] + 0;
                }
            }
            return;
        }
        while(Set::has($parse)){
            $set_counter++;
            $set = Set::get($parse);
            $statement = Set::statement($set);
//             debug($set, 'set is wrong, need has_exclamation & invert on first?');
            $operator_counter = 0;
            if(empty($statement)){
                break;
            }
            while (Operator::has($statement)){
                $operator_counter++;
                $statement = Operator::statement($statement, $parser);

                if($operator_counter > Operator::MAX){
                    break;
                }
            }
            $record = reset($statement);
            $record['set']['depth']--;
            $parse = Set::replace($parse, $set, $record);
            $parse = Set::exclamation($parse);
            //test variable..
            if($set_counter > Set::MAX){
                break;
            }
        }
        $operator_counter = 0;
        while (Operator::has($parse)){
            $operator_counter++;
            $parse = Operator::statement($parse, $parser);
            if($operator_counter >= Operator::MAX){
                break;
            }
        }
        foreach($parse as $nr => $record){
            if($record['type'] == Token::TYPE_WHITESPACE){
                unset($parse[$nr]);
            }
        }
        if(count($parse) == 1){
            $record = array_shift($parse);
            $record = Token::cast($record);
            if(is_numeric($record['value'])){
                return $record['value'] + 0;
            }
            elseif(is_bool($record['value'])){
                return $record['value'];
            }
            if(
                isset($record['modified_is_executed']) &&
                $record['modified_is_executed'] === true
            ){
                return $record['value'];
            } else {
                return;
            }
        } else {
            debug($parse, 'output in create_equation');
            return;
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
                    'T_CURLY_OPEN',
                    'T_CURLY_CLOSE',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_OPEN){
            if(isset($token[2]) && in_array($token[2], array(
                    'T_CURLY_OPEN',
            ))){
                return true;
            }
        }
        elseif($type == Token::TYPE_CLOSE){
            if(isset($token[2]) && in_array($token[2], array(
                    'T_CURLY_CLOSE',
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

    public static function cast($record=array(), $attribute='value'){
        if(empty($record['is_cast'])){
            if($record[$attribute] === 'true'){
                $record[$attribute] = true;
            }
            elseif($record[$attribute] === 'false'){
                $record[$attribute] = false;
            }
            elseif($record[$attribute] === 'null'){
                $record[$attribute] = null;
            }
            return $record;
        }
        switch ($record['cast']){
            case Token::TYPE_STRING :
                if(empty($record[$attribute])){
                    $record[$attribute] = '';
                }
                elseif(is_array($record[$attribute]) || is_object($record[$attribute])){
                    $record[$attribute] = '';
                } else {
                    $record[$attribute] = (string) $record[$attribute];
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_STRING;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_INT:
                if(!isset($record[$attribute])){
                    $record[$attribute] = 0;
                }
                elseif(is_array($record[$attribute]) || is_object($record[$attribute])){
                    $record[$attribute] = 0;
                } else {
                    if($record[$attribute] === 'false'){
                        $record[$attribute] = 0;
                    }
                    if($record[$attribute] === 'true'){
                        $record[$attribute] = 1;
                    }
                    if(!is_numeric($record[$attribute])){
                        $record[$attribute] = 0;
                    } else {
                        $record[$attribute] = (int) round($record[$attribute] + 0);
                    }
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_INT;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_FLOAT:
                if(!isset($record[$attribute])){
                    $record[$attribute] = 0.0;
                }elseif(is_array($record[$attribute]) || is_object($record[$attribute])){
                    $record[$attribute] = 0.0;
                } else {
                    if($record[$attribute] == 'false'){
                        $record[$attribute] = 0;
                    }
                    if($record[$attribute] == 'true'){
                        $record[$attribute] = 1;
                    }
                    $record[$attribute] = floatval($record[$attribute]);
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_FLOAT;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_BOOLEAN:
                if($record[$attribute] == 'false'){
                    $record[$attribute] = 0;
                }
                if($record[$attribute] == 'true'){
                    $record[$attribute] = 1;
                }
                if(!empty($record[$attribute])){
                    $record[$attribute] =  true;
                } else {
                    $record[$attribute] =  false;
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_BOOLEAN;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_ARRAY:
                if(isset($record[$attribute])){
                    $record[$attribute] =  Token::object($record[$attribute], 'array');
                } else {
                    $record['value'] =  array();
                }
                $record['is_cast'] = false;
                $record['type'] = Token::TYPE_ARRAY;
                unset($record['cast']);
                return $record;
                break;
            case Token::TYPE_OBJECT:
                if(isset($record[$attribute])){
                    $record[$attribute] =  Token::object($record[$attribute]);
                } else {
                    $record[$attribute] =  new stdClass();
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

    public static function string($tokens=array(), $parser=null){
        $string = '';
        $is_string = false;
        $is_variable = false;
        foreach($tokens as $nr => $token){
            if(isset($token[1])){
                if(substr($token[1], 0, 1) == '\'' && substr($token[1], -1, 1) == '\''){
                    $token[1] = substr($token[1], 1, -1);
                    $token[1] = str_replace('\\\'', '\'', $token[1]);
                    $token[1] = str_replace('"', '\"', $token[1]);
                    $token[1] = '"' . $token[1] . '"';
                    $string .= $token[1];
                    continue;
                }
                if($token[1] == '"'){
                    $is_string = true;
                }
                if($token[2] == 'T_VARIABLE'){
                    $is_variable = true;
                }
                if($is_string === true){
                    if(in_array($token[2], array(
                        'T_VARIABLE'
                    ))){
                        $token[2] = Token::TYPE_STRING;
                    }
                }
                if(is_null($token[1])){
                    $string .= 'null';
                    continue;
                }
                elseif(is_bool($token[1])){
                    if(!empty($token[1])){
                        $string .= 'true';
                    } else {
                        $string .= 'false';
                    }
                    continue;
                }
                elseif(
                    substr($token[1], 0, 1) == '"' &&
                    substr($token[1], -1, 1) == '"' ||
                    in_array($token[2], array(
                        Token::TYPE_STRING
                    ))
                ){
                    //do nothing
                } else {
                    if(in_array($token[2], array(
                        'T_STRING',
                        'T_ARRAY',
                        'T_OBJECT',
                    ))){
                        if($is_variable === false){
                            $token[1] = '"' . $token[1] . '"';
                        }
                    }
                    elseif(
                        in_array(
                            $token[2], array(
                                'T_VARIABLE'
                            )
                        )
                    ){
                        $token[1] = Variable::replace($token[1], '', false, $parser);
                    }
                }
                if($token[2] == 'T_COMMA'){
                    $is_variable = false;
                }
                if(is_object($token[1]) || is_array($token[1])){
                    $string .= json_encode($token[1]);
                    //pre-check if method then dont come here...
                    continue;
                }
                $string .= $token[1];
            }
            elseif($token[1] === null){
                $string .= 'null';
            }
        }
        if(is_string($string)){
            $string = str_replace('""', '', $string);
        }
        return $string;
    }

    public static function get($token=array()){
        if(isset($token[2])){
            return $token[2];
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
            case 'T_ARRAY' :        //might needs its own type
            case 'T_CLASS' :
            case 'T_NAMESPACE' :
            case 'T_IS' :             //might needs its own type
            case 'T_DEFAULT' :         //might needs its own type
            case 'T_PUBLIC' :
            case 'T_PROTECTED' :
            case 'T_PRIVATE' :
            case 'T_REQUIRE' :
            case 'T_INCLUDE' :
            case 'T_QUESTION_MARK' :
            case 'T_ELLIPSIS' :
            case 'T_COMMENT' :
            case 'T_FUNCTION' :
            case 'T_AT' :
            case 'T_FOREACH' :
            case 'T_FOR' :
            case 'T_WHILE' :
            case 'T_ELSE' :
            case 'T_PLUS_EQUAL' :
            case 'T_BACKTICK' :
            case 'T_BREAK' :
            case 'T_DEC' :
            case 'T_INC' :
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
            case 'T_OPERATOR_BITWISE' :
            case 'T_IS_GREATER_OR_EQUAL' :
            case 'T_IS_SMALLER_OR_EQUAL' :
            case 'T_IS_EQUAL' :
            case 'T_IS_NOT_EQUAL' :
            case 'T_IS_IDENTICAL' :
            case 'T_IS_NOT_IDENTICAL' :
            case 'T_SL' :
            case 'T_SR' :
            case 'T_BOOLEAN_AND';    //might need a different one
            case 'T_BOOLEAN_OR';     //might need a different one
                return  Token::TYPE_OPERATOR;
            break;
            case 'T_VAR' :
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
            case 'T_SQUARE_BRACKET_OPEN' :
            case 'T_SQUARE_BRACKET_CLOSE':
            case 'T_CURLY_OPEN' :
            case 'T_CURLY_CLOSE' :
                return Token::TYPE_BRACKET;
            break;
            case 'T_WHITESPACE' :
            case 'T_RETURN' :
                return Token::TYPE_WHITESPACE;
            break;
            case 'T_DOT' :
                return Token::TYPE_DOT;
            break;
            case 'T_COMMA' :
                return Token::TYPE_COMMA;
            break;
            case 'T_COLON' :
                return Token::TYPE_COLON;
            break;
            case 'T_DOUBLE_COLON' :
                return Token::TYPE_DOUBLE_COLON;
            break;
            case 'T_SEMI_COLON' :
                return Token::TYPE_SEMI_COLON;
            case 'T_EXCLAMATION' :
                return Token::TYPE_EXCLAMATION;
            break;
            case 'T_EMPTY' :
            case 'T_ISSET' :
                return Token::TYPE_METHOD;
            break;
            case 'T_IF' :
                return Token::TYPE_CONTROL;
            break;
            case Token::TYPE_STRING:
            case Token::TYPE_MIXED:
            case Token::TYPE_NULL:
            case Token::TYPE_BOOLEAN:
                return $type;
            break;
            case 'T_AS' :
                return Token::TYPE_AS;
            break;
            case 'T_DOUBLE_ARROW' :
                return Token::TYPE_DOUBLE_ARROW;
            break;
            default :
                //if production defaults to TYPE_STRING
                throw new Exception('undefined type in token found: ' . $type);
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

    public static function comment_remove($string='', $test=false){
        $tokens = Token::all($string);
        foreach($tokens as $nr => $token){
            if(
                in_array(
                    $token[2],
                    array(
                        'T_COMMENT',
                        'T_DOC_COMMENT'
                    )
                )
            ){
                if(substr($token[1], 0, 2) == '//'){
                    continue;
                }
                elseif(
                    substr($token[1], 0, 2) == '/*' &&
                    substr($token[1], -2, 2) == '*/'
                ){
                    $string = str_replace($token[1], '', $string);
                }
            }
        }
        return $string;
    }

    public static function method($record=array(), $parser=null, $depth=0){
        $counter = 0;
        $has_method = false;
        $list = array();
        if(!isset($record['string'])){
            //create_object
            //create_array
        }
        foreach($record['parse'] as $parse){
            if(
                isset($parse['is_executed']) &&
                isset($parse['type']) &&
                $parse['type'] == Token::TYPE_METHOD &&
                $parse['is_executed'] === true
            ){
                return $record;
            }
        }
        $method = Method::get($record['parse'], $parser);
        if($method === false && $parser->data('priya.parser.method')){
            $parser->data('priya.parser.loop', 'init');
        }
        while($method !== false){
            $parser->data('priya.parser.method', $method);
            $attribute = false;
            if(!empty($method['parse_method']) && is_array($method['parse_method'])){
                foreach($method['parse_method'] as $key => $parse){
                    if($attribute === false){
                        $attribute = $key;
                    }
                    unset($record['parse'][$key]);
                }
            }
            if($attribute !== false){
                if(!isset($record['string'])){
                    $method['string'] = '';
                } else {
                    $method['string'] = $record['string'];
                }
//                 var_dump($method['string']);
                $method['key'] = $record['key'];
                $method['original'] = $record['original'];

                $debug = false;
                if($method['method'] == 'string'){
                    $debug = true;
                }

                if(!empty($debug)){
//                     echo $method['string'];
//                     var_dump($method['key']);
//                     var_dump('test');
//                     die;
                }


//                 var_dump($record);
                $method = Method::execute($method, $parser, $debug);
                $method = Method::exclamation($record, $method, $parser);
                $record = Method::remove_exclamation($record);
                $method = Token::cast($method);
                $method['type'] = Token::TYPE_METHOD;
                $record['string'] = $method['string'];
                $record['parse'][$attribute] = $method;
                $record['status'] = Method::STATUS;
                ksort($record['parse']);

            }
            //loses string /status
            $method = Method::get($record['parse'], $parser);
            $counter++;
            if($counter >= Method::MAX){
                break;
            }
        }
        return $record;
    }

    public static function newline_replace($input='',  $random=''){
        if(is_object($input)){
            foreach($input as $key => $value){
                $input->{$key} = Token::newline_replace($value, $random);
            }
            return $input;
        }
        elseif(is_array($input)){
            foreach($input as $key => $value){
                $input[$key] = Token::newline_replace($value, $random);
            }
            return $input;
        } else {
            $input = str_replace("\r", '[' . $random . '][return]', $input);
            $input = str_replace("\n", '[' . $random . '][newline]', $input);
            return $input;
        }

    }

    public static function newline_restore($input='',  $random=''){
        if(is_object($input)){
            foreach($input as $key => $value){
                $input->{$key} = Token::newline_restore($value, $random);
            }
            return $input;
        }
        elseif(is_array($input)){
            foreach($input as $key => $value){
                $input[$key] = Token::newline_restore($value, $random);
            }
            return $input;
        } else {
            $search = array();
            $search[] = '[' . $random . '][return]';
            $search[] = '[' . $random . '][newline]';
            $replace = array();
            $replace[] = "\r";
            $replace[] = "\n";
            $input = str_replace($search, $replace, $input);
            return $input;
        }
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function literal_get($value=''){
        if(!is_string($value)){
            return '';
        }
        $explode = explode(Token::LITERAL_OPEN, $value, 2);
        if(count($explode) == 2){
            $temp = explode(TOKEN::LITERAL_CLOSE, $explode[1], 2);
            if(count($temp) ==2){
                return $temp[0];
            }
        }
        return '';
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public static function literal_remove($value=''){
        if(is_array($value)){
            foreach($value as $key => $value_value){
                $value[$key] = Token::literal_remove($value_value);
            }
            return $value;
        }
        elseif(is_object($value)){
            foreach($value as $key => $value_value){
                $value->{$key} = Token::literal_remove($value_value);
            }
            return $value;
        }
        if(!is_string($value)){
            return $value;
        }
        return str_replace(
            array(
                Token::LITERAL_OPEN,
                Token::LITERAL_CLOSE
            ),
            '',
            $value
            );
    }

    /**
     *
     * @param string $value
     * @param string $random
     * @return string
     */
    public static function literal_restore($value='', $random=''){
        $search = array(
            '[' . $random . '][literal]',
            '[' . $random . '][/literal]',
            '[' . $random . '][curly_open]',
            '[' . $random . '][curly_close]',
        );
        $replace = array(
            TOKEN::LITERAL_OPEN,
            TOKEN::LITERAL_CLOSE,
            '{',
            '}',
        );
        return str_replace($search, $replace, $value);
    }

    /**
     *
     * @param string $value
     * @param string $random
     * @return string
     */
    public static function literal_replace($value='', $random=''){
        $literal = Token::literal_get($value);
        while($literal != ''){
            $literal = Token::literal_get($value);
            $search = Token::LITERAL_OPEN . $literal . Token::LITERAL_CLOSE;
            $literal = str_replace(
                array(
                    '{',
                    '}',
                ),
                array(
                    '[' . $random . '][curly_open]',
                    '[' . $random . '][curly_close]',
                ),
                $literal
                );
            $replace = '[' . $random . '][literal]' . $literal . '[' . $random .'][/literal]';
            $value = str_replace($search, $replace, $value);
        }
        return $value;
    }

    /**
     * adds extra literal tags around {}
     * @param string $value
     */
    public static function literal_extra($value=''){
        if(is_object($value)){
            foreach ($value as $key => $val){
                $value->{$key} = Token::literal_extra($val);
            }
            return $value;
        }
        elseif(is_array($value)){
            foreach ($value as $key => $val){
                $value[$key] = Token::literal_extra($val);
            }
            return $value;
        } else {
            $search = array(
                '{' . "\n",
                '{' . "\r",
                '{' . "\r\n",
                '{' . ' ',
                '{}',
                "\n" . '}',
                "\r" . '}',
                "\r\n" . '}',
                ' ' . '}',
            );
            $replace = array(
                Token::LITERAL_OPEN . '{' . Token::LITERAL_CLOSE . "\n",
                Token::LITERAL_OPEN . '{' . Token::LITERAL_CLOSE . "\r",
                Token::LITERAL_OPEN . '{' . Token::LITERAL_CLOSE . "\r\n",
                Token::LITERAL_OPEN . '{' . Token::LITERAL_CLOSE . ' ',
                Token::LITERAL_OPEN .'{}' . Token::LITERAL_CLOSE,
                "\n" . Token::LITERAL_OPEN . '}'. Token::LITERAL_CLOSE,
                "\r" . Token::LITERAL_OPEN . '}'. Token::LITERAL_CLOSE,
                "\r\n" . Token::LITERAL_OPEN . '}'. Token::LITERAL_CLOSE,
                ' ' . Token::LITERAL_OPEN .'}'. Token::LITERAL_CLOSE,
            );
            return str_replace($search, $replace, $value);
        }
    }
}