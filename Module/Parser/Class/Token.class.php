<?php

namespace Priya\Module\Parser;

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
    const TYPE_DOT = 'dot';
    const TYPE_COLON = 'colon';
    const TYPE_DOUBLE_COLON = 'double-colon';
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

    public static function create_object($value= '', $attribute='', Variable $variable, $parser=null){
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
                $parse = Token::variable($parse, $variable, $attribute);
                //if is_object($parse... assign the attributes
                $method = array();
                $method['parse'] = $parse;
                $method = Token::method($method, $variable, $parser);
                $parse = $method['parse'];
                $value = Token::string($value, $variable);
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
                if(
                    is_array($result['value']) ||
                    is_object($result['value'])
                ){
                    foreach($result['value'] as $key => $assign){
                        $parser->data($attribute . '.' . $key, $assign);
                    }
                }
                $result['value'] = $parser->compile($result['value'], $parser->data(), true);
                return $result;
                break;
            }
        }
        return array();

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
//             if(Token::is_operator)
//             debug($token, 'token');
            if(
                Token::is_bracket($token, Token::TYPE_OPEN) &&
                Token::is_bracket(end($tokens) ,  Token::TYPE_CLOSE)
            ){
                $record['type'] = Token::TYPE_OBJECT;
                if(is_array($value)){
                    $parse = Token::parse($value);
                    debug($parse, 'parse');
                    $parse = Token::variable($parse, $variable, $attribute);
                    //if is_object($parse... assign the attributes
                    $method = array();
                    $method['parse'] = $parse;
                    $method = Token::method($method, $variable, $parser);
                    $parse = $method['parse'];
                    $value = Token::string($value, $variable);
                }
                if($record['is_cast'] === true){
                    $explode = explode($record['cast'], $value, 2);
                    $record['value'] = ltrim($explode[1], ') ');
                } else {
                    $record['value'] = $value;
                }
                $record['attribute'] = $attribute;
                $record['original'] = $record['value'];
                $record['token'] = $tokens;
                $record['value'] = Token::object($record['value']);
                if(
                    is_array($record['value']) ||
                    is_object($record['value'])
                ){
                    foreach($record['value'] as $key => $assign){
                        $parser->data($attribute . '.' . $key, $assign);
                    }
                }
                debug($record, 'rec');

                $record['value'] = $parser->compile($record['value'], $parser->data(), true);
                return $record;
            }
        }
        return array();
    }

    public static function create_array($value= '', Variable $variable){
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
                    $value = Token::string($value, $variable);
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
        if($record['has_exclamation'] === true){
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

    public static function variable($parse=array(), Variable $variable, $attribute=null){
        $parse = Variable::fix($parse);
        $item = array();
        $key = null;
        $unset = array();
        $has_exclamation = false;
        $exclamation_count = 0;
        $exclamation_check = false;
        $is_variable = false;

        /*
         * first find the variable
         * from there upwards till (
         */
        foreach ($parse as $nr => $record){
            $possible_dot = next($parse);
            if(!isset($record['value'])){
                continue;
            }
            if(!isset($record['type'])){
                continue;
            }
            $exclamation_parse[$nr] = $record;
            if($record['type'] != Token::TYPE_VARIABLE && empty($item)){
                continue;
            }
            $is_variable = true;

            if($exclamation_check === false){
                krsort($exclamation_parse);
                foreach($exclamation_parse as $exclamation_nr => $exclamation_value){
                    if($exclamation_value['type'] == Token::TYPE_EXCLAMATION){
                        $has_exclamation = true;
                        $exclamation_count++;
                        $unset[] = $exclamation_nr;
                    }
                    if(
                        $exclamation_value['type'] == Token::TYPE_PARENTHESE &&
                        $exclamation_value['value'] == '('
                    ){
                        break;
                    }
                }
            }

            if(isset($possible_dot) && $possible_dot['type'] == Token::TYPE_DOT && empty($item)){
                $item = $record;
                $key = $nr;
                continue;
            }
            elseif(empty($item)) {
                $record['has_exclamation'] = $has_exclamation;
                $record['exclamation_count'] = $exclamation_count;
                if($attribute !== null){
                    $record['value'] = str_replace('$this.', '$' . $attribute . '.', $record['value']);
                }
                $modifier = Token::modifier($parse);
                $record['value'] = $variable->replace($record['value'], $modifier);
                if($record['exclamation_count'] % 2 == 1){
                    $record['invert'] = true;
                } else {
                    $record['invert'] = false;
                }
                $record = Token::Exclamation($record);
                if($record['type'] == Token::TYPE_VARIABLE){
                    $record['is_variable'] = true;
                }
                $record['type'] = Variable::type($record['value']); //change to value::type...
                $record = Token::cast($record);

                $parse[$nr] = $record;
            }
            if($record['value'] == ')' && $record['set']['depth'] == 1){
                continue;
            }
            if(!empty($item)){
                if(
                    (
                        $record['type'] == Token::TYPE_WHITESPACE  &&
                        $item['type'] == Token::TYPE_VARIABLE
                    ) ||
                    (
                        $record['type'] = Token::TYPE_BRACKET &&
                        $record['value'] == '}'
                    )
                ){
                    $item['has_exclamation'] = $has_exclamation;
                    $item['exclamation_count'] = $exclamation_count;
                    if($attribute !== null){
                        $item['value'] = str_replace('$this.', '$' . $attribute . '.', $item['value']);
                    }
                    if($item['exclamation_count'] % 2 == 1){
                        $item['invert'] = true;
                    } else {
                        $item['invert'] = false;
                    }
                    $modifier = Token::modifier($parse);
                    $item['value'] = $variable->replace($item['value'], $modifier);
                    $item = Token::Exclamation($item);
                    if($item['type'] == Token::TYPE_VARIABLE){
                        $item['is_variable'] = true;
                    }
                    $item['type'] = Variable::type($item['value']); //change to value::type...
                    $item = Token::cast($item);
                    foreach($unset as $unset_key){
                        unset($parse[$unset_key]);
                    }
                    $parse[$key] = $item;
                    $item = array();
                    $unset = array();
                    $key = null;
                } else {
                    $item['value'] .= $record['value'];
                    $unset[] = $nr;
                }
            }
        }
        if(!empty($item)){
            $item['has_exclamation'] = $has_exclamation;
            $item['exclamation_count'] = $exclamation_count;
            if($attribute !== null){
                $item['value'] = str_replace('$this.', '$' . $attribute . '.', $item['value']);
            }
            if($item['exclamation_count'] % 2 == 1){
                $item['invert'] = true;
            } else {
                $item['invert'] = false;
            }
            $item['value'] = $variable->replace($item['value']);
            $item= Token::Exclamation($item);
            if($item['type'] == Token::TYPE_VARIABLE){
                $item['is_variable'] = true;
            }
            $item['type'] = Variable::type($item['value']); //change to value::type...
            $item = Token::cast($item);
            foreach($unset as $unset_key){
                unset($parse[$unset_key]);
            }
            $unset = array();
            $parse[$key] = $item;
        }
        if($is_variable === true){
            foreach($unset as $unset_key){
                unset($parse[$unset_key]);
            }
        }
        return $parse;
    }

    public static function modifier($parse=array()){
        $modifier = '';
        $collect = false;
        foreach($parse as $nr => $record){
            if($record['value'] == '|'){
                $collect = true;
            }
            if($collect === true){
                $modifier .= $record['value'];
            }
        }
        return $modifier;
    }

    /**
     * @todo
     * - add cast
     */
    public static function create_equation($parse=null, Variable $variable, $parser=null){
        $set_counter = 0;
        if(Operator::has($parse) === false){
            if(count($parse) == 1){
                $record = reset($parse);
                if(is_bool($record['value'])){
                    return $record['value'];
                } else {
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
                debug(set::has($parse));
                debug($set, 'set');
                debug($parse, 'parse empty statement');
                die;
                break;
            }
            while (Operator::has($statement)){
                $operator_counter++;
                $statement = Operator::statement($statement, $variable, $parser);

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
            $parse = Operator::statement($parse, $variable, $parser);
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

    public static function string($tokens=array(), Variable $variable=null){
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
                        ) &&
                        is_object($variable)
                    ){
                        $token[1] = $variable->replace($token[1]);
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
            if(
                strpos($string, '{"') !== false &&
                strpos($string, '"(') !== false
            ){
                $explode = explode('{"', $string, 2);
                $string = implode('{', $explode);
                $explode = explode('"."', $string);
                $string = implode('.', $explode);
                $explode = explode('"(', $string, 2);
                $string = implode('(', $explode);
                debug($string, 'string');
            }
        }
        return $string;
    }

    public static function get($token=array()){
        if(isset($token[2])){
            return $token[2];
        } else {
            debug($token);
            debug(debug_backtrace(true));
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
            case 'T_IS' :             //might needs its own type
            case 'T_DEFAULT' :         //might needs its own type
            case 'T_PUBLIC' :
            case 'T_PROTECTED' :
            case 'T_PRIVATE' :
            case 'T_REQUIRE' :
            case 'T_INCLUDE' :
            case 'T_QUESTION_MARK' :
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

    public static function remove_comment($string='', $test=false){
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

    public static function method($record=array(), Variable $variable, $parser=null, $depth=0){
        $counter = 0;
        $has_method = false;
        $list = array();
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
        $method = Method::get($record['parse'], $variable, $parser);
        while($method !== false){
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
                $method = Method::execute($method, $variable, $parser);
                $method = Method::exclamation($record, $method, $parser);
                $record = Method::remove_exclamation($record);
                $method = Token::cast($method);
                $method['type'] = Token::TYPE_METHOD;
                $record['string'] = $method['string'];
                $record['parse'][$attribute] = $method;
                ksort($record['parse']);
            }
            $method = Method::get($record['parse'], $variable, $parser);
            $counter++;
            if($counter >= Method::MAX){
                break;
            }
        }
        return $record;
    }
}



