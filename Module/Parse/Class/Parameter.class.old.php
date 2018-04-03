<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Parameter extends Core {
    const SEPARATOR = ',';
    const SPACE =' ';
    const EMPTY = '';
    const QUOTE_SINGLE = '\'';
    const QUOTE_DOUBLE = '"';

    public static function find($string='', $parser=null){
        $parameters = array();
        $parameter = array();
        $no_parse = array();
        $parse = array();
        $collection = array();
        $statement = array();
        $operator = '';
        $split = str_split($string, 1);

        $counter = 0;
        $skip = 0;
        $is_parse = false;
        $is_no_parse = false;
        $is_collection = true;
        $is_operator = false;

        foreach($split as $position => $char){
            if($skip > 0){
                $skip -= 1;
                continue;
            }
//             var_Dump($char);
            //check on is_variable before
            //add skip (skipping operator chars)
            if(
                $is_collection === true &&
                $is_operator === false &&
                $is_no_parse === false &&
                $is_parse === false &&
                in_array(
                    $char,
                    array( //Parameter::OPERATOR_START_LIST
                        '+',
                        '-',
                        '/',
                        '*',
                        '&',
                        '|',
                        '%',
                        'a',
                        'x',
                        'o',
                        '<',
                        '>',
                        '.',
                        '!'
                    )
                )
            ){
                $is_operator = true;
                $is_collection = false;
                $operator = $char;
                $next = '';
                $next_next = '';
                if(isset($split[$position + 1])){
                    $next = $split[$position + 1];
                }
                if(isset($split[$position + 2])){
                    $next_next = $split[$position + 2];
                }
                if(
                    (
                        $operator == '&' &&
                        $next =='&'
                    ) ||
                    (
                        $operator == '|' &&
                        $next =='|'
                    ) ||
                    (
                        $operator == '=' &&
                        $next =='='
                    ) ||
                    (
                        $operator == '!' &&
                        $next =='='
                    )  ||
                    (
                        $operator == '>' &&
                        $next =='='
                    )  ||
                    (
                        $operator == '<' &&
                        $next =='='
                    )  ||
                    (
                        $operator == '*' &&
                        $next =='*'
                    )  ||
                    (
                        $operator == '<' &&
                        $next =='<'
                    )  ||
                    (
                        $operator == '>' &&
                        $next =='>'
                    )  ||
                    (
                        $operator == 'o' &&
                        $next =='r'
                    )
                ){
                    $operator .= $next;
                    $skip = 1;
                }
                elseif(
                    (
                        $operator == '=' &&
                        $next == '=' &&
                        $next_next == '='
                    ) ||
                    (
                        $operator == '!' &&
                        $next == '=' &&
                        $next_next == '='
                    ) ||
                    (
                        $operator == 'x' &&
                        $next == 'o' &&
                        $next_next == 'r'
                    ) ||
                    (
                        $operator == 'a' &&
                        $next == 'n' &&
                        $next_next == 'd'
                    )
                ){
                    $operator .= $next . $next_next;
                    $skip = 2;
                }
                var_Dump($char);
                var_dump($no_parse);
                var_dump($parse);
                var_dump($collection);
                $statement[] = $operator;
                $is_collection === true;
                $is_operator === false;
                continue;
            }
            if(
                $is_collection === true &&
                $is_no_parse === false &&
                $is_parse === false &&
                $char == '"'
            ){
                $is_parse = true;
                $is_collection = false;
                continue;
            }
            elseif(
                $is_no_parse === false &&
                $is_parse === true &&
                $char == '"' &&
                $previous_char != '\\'
            ){
                $is_parse = false;
                $is_collection = true;
                continue;
            }
            elseif(
                $is_collection === true &&
                $is_no_parse === false &&
                $is_parse === false &&
                $char == '\''
            ){
                $is_no_parse = true;
                $is_collection = false;

                /*
                $collection = implode(Parameter::EMPTY, $collection);
                if(!empty($collection)){
                    $statement[] = $collection;
                }
                $collection = array();
                */
                continue;
            }
            elseif(
                $is_no_parse === true &&
                $is_parse === false &&
                $char == '\'' &&
                $previous_char != '\\'
            ){
                $is_no_parse = false;
                $is_collection = true;
                continue;
            }
            if($is_no_parse && !$is_parse){
                $no_parse[] = $char;
            }
            elseif(!$is_no_parse && $is_parse){
                $parse[] = $char;
            } else {
                if(
                    $is_no_parse === false &&
                    $is_parse === false &&
                    in_array(
                        $char,
                        Operator::LIST
                    )
                ){
                    /**
                     * statement can either be a $no_parse, $parse or $collection (2X)
                     */
                    if(!empty($no_parse)){
                        $no_parse = implode(Parameter::EMPTY, $no_parse);
                        $statement[] = $no_parse;
                        var_dump($collection);
                    }
                    elseif(!empty($parse)){
                        //parse $parse
                        $parse = implode(Parameter::EMPTY, $parse);
                        $parse = Parse::token($parse, $parser->data(), false, $parser);
                        $statement[] = $parse;
                    } else {
                        $collection = implode(Parameter::EMPTY, $collection);
                        if(substr($collection, 0, 1) == '$'){
                            var_dump('make variable');
                            die;
                        }
                        $statement[] = $collection;
                    }
                    $no_parse = array();
                    $parse = array();
                    $collection = array();
                    $statement[] = $char;
                }
                elseif(
                    $is_no_parse === false &&
                    $is_parse === false &&
                    $char == Parameter::SEPARATOR
                ){
                    /**
                     * parameter is stament
                     */
                    $counter++;
                    var_dump('have multiple parameters');
                    var_dump($statement);
                    die;
                    $parameter = array();
                }
                else {
                    //fill collection
                    $collection[] = $char;
                }
            }
            $previous_char = $char;
        }
        /**
         * statement can either be a $no_parse, $parse or $collection (2X)
         */
        if(!empty($no_parse)){
            $no_parse = implode(Parameter::EMPTY, $no_parse);
            $statement[] = $no_parse;
        }
        elseif(!empty($parse)){
            //parse $parse
            $parse = implode(Parameter::EMPTY, $parse);
            $parse = Parse::token($parse, $parser->data(), false, $parser);
            $statement[] = $parse;
        } else {
            $collection = implode(Parameter::EMPTY, $collection);
            if(substr($collection, 0, 1) == '$'){
                var_dump('make variable');
                die;
            }
            $statement[] = $collection;
        }
        /**
         * if(!empty($statement)){
                $counter = 0;
                while(Operator::has($statement, $parser)){
                    $statement = Operator::statement($statement, $parser);
//                     var_dump($statement);
                    $parser->data('priya.debug2', true);
                    $counter++;
                    if(!isset($statement[1])){
                        break;
                    }
                    if($counter > Operator::MAX){
                        throw new Exception('Operator::MAX reached in Parameter::find');
                        break;
                    }
                }
                $result[$nr] = $statement[0];
                $statement = array();
            }
         */


        if(!empty($statement)){
//             $statement_count = count($statement);
            if(!isset($statement[1])){
                $parameters[] = $statement[0];
            } else {
                //solve statements
                var_dump('multiple statements');
                var_dump($statement);
                die;
            }
        }
        return $parameters;
    }

    /*
    public static function find($string='', $parser=null){
       $parameters = array();
       $method = token_get_all('<?php $method=method(' . $string . ');');
       $collect = false;
       $counter = 0;
       $set_depth = 0;

       foreach ($method as $nr => $parameter){
               if($parameter == Method::OPEN || isset($parameter[1]) && $parameter[1] == Method::OPEN){
                $set_depth++;
                if($set_depth == 1){
                    $collect = true;
                    continue;
                }
            }
            if($parameter == Method::CLOSE || isset($parameter[1]) && $parameter[1] == Method::CLOSE){
                if($set_depth == 1){
                    $collect = false;
                }
                $set_depth--;
            }
            if($collect){
                if(
                    $set_depth == 1 &&
                    (
                        $parameter == Parameter::SEPARATOR ||
                        isset($parameter[1]) &&
                        $parameter[1] == Parameter::SEPARATOR
                    )
                ){
                    $counter++;
                    continue;
                }
                if(isset($parameter[1])){
                    $parameters[$counter][] = $parameter[1];
                } else {
                    $parameters[$counter][] = $parameter;
                }
            }
       }
//        var_dump($parameters);
       //fix statements 1 by 1
       /**
        * change variables into values
        * change methods into values
        * fix statements from left to right 1 by 1.


        $list = Operator::LIST;
//         unset($list[0]); //no .
        $result = array();

        foreach($parameters as $nr => $set){
            $parse = false;
            $no_parse = false;
            $is_statement = false;
            $compile = array();
            $no_compile = array();
            $statement = array();
            $argument = array();
            $is_variable = true;
            var_dump($set);
            foreach($set as $position => $value){
                //add substr
                $start = substr($value, 0, 1);
                $end = substr($value, -1);
                $length = strlen($value);
                if($start == '\'' &&  $end == '\'' && $length > 1){
                    $statement[] = substr($value, 1, -1);
                    $is_variable = false;
                    continue;
                }
                elseif($start == '"' && $end == '"' && $length > 1){
                    // add parse->compile $statement[]
                    $statement[] = substr($value, 1, -1);
                    $is_variable = false;
                    continue;
                }
                if($value == Parameter::QUOTE_DOUBLE && $parse === false && $is_statement === false){
                    $parse = true;
                    $compile[] = $value;
                    continue;
                }
                elseif($value == Parameter::QUOTE_DOUBLE && $parse === true){
                    //for parse we need the whole set imploded
                    $parse = false;
                    $compile[] = $value;
                    $statement[] = implode(Parameter::EMPTY, $compile);
                    $is_variable = false;
                    var_dump($statement);
                    //parse $statement[$nr];
                    continue;
                }
                if($parse === true){
                    $compile[] = $value;
                    continue;
                }
                if($value == Parameter::QUOTE_SINGLE && $no_parse === false && $is_statement === false){
                    $no_parse = true;
                    $no_compile[] = $value;
                    continue;
                }
                elseif($value == Parameter::QUOTE_SINGLE && $no_parse === true){
                    //just implode the set with an empty delimiter
                    $no_parse = false;
                    $no_compile[] = $value;
                    $statement[] = implode(Parameter::EMPTY, $no_compile);
                    $is_variable = false;
                    continue;
                }
                if($no_parse === true){
                    $no_compile[] = $value;
                    continue;
                }
                $value = trim($value, Parameter::SPACE);
                if(empty($value)){
                    continue;
                }
                if($is_statement === false && $parse === false && $no_parse === false){
                    $is_statement = true;
                }
                if(in_array($value, $list)){
                    if($value == '.' && $is_variable === true){
                        //calculate right ?
                        var_Dump($argument);
                        var_Dump($statement);
                        die;
                    }
                    elseif($value == '.' && $is_variable === false){
                        $statement[] = $value;
                        continue;
                        var_Dump($argument);
                        var_Dump($statement);
                        die;
                    }
                    $parameter = implode(Parameter::EMPTY, $argument);
                    var_dump($parameter);
                    $parameter= Variable::get($parameter, $parser);
                    //add Method::get (2X)
                    $parameter= Cast::translate($parameter);
                    $statement[] = $parameter;
                    $statement[] = $value;
                    $argument = array();
                    $is_statement = false;
                    continue;
                    //argument complete
                }
                elseif($value == '.'){
                    var_dump($argument);
                    var_dump($value);
                    var_dump($statement);
                    die;
                }
                //also on == & === for if statement
                $argument[] = $value;
            }
            if(!empty($argument)){
                $parameter = implode(Parameter::EMPTY, $argument);
                $parameter= Variable::get($parameter, $parser);
                //add Method::get (2X)
                $parameter= Cast::translate($parameter);
                $statement[] = $parameter;
            }

//             var_dump($statement);

            /**
             * statement can have + /- * / etc...

            if(!empty($statement)){
                $counter = 0;
                while(Operator::has($statement, $parser)){
                    $statement = Operator::statement($statement, $parser);
//                     var_dump($statement);
                    $parser->data('priya.debug2', true);
                    $counter++;
                    if(!isset($statement[1])){
                        break;
                    }
                    if($counter > Operator::MAX){
                        throw new Exception('Operator::MAX reached in Parameter::find');
                        break;
                    }
                }
                $result[$nr] = $statement[0];
                $statement = array();
            }
        }

        $parameters = $result;

//        var_Dump($parameters);
       /**
        * old way of parameters, should end up the same...
        */
        /*
       foreach($parameters as $nr => $set){
           $parameters[$nr] = trim(implode(Parameter::EMPTY, $set), Parameter::SPACE);
       }


       foreach ($parameters as $nr => $parameter){
            if(
                substr($parameter, 0, 1) == Parameter::QUOTE_DOUBLE &&
                substr($parameter, -1, 1) == Parameter::QUOTE_DOUBLE
            ){
                $parameters[$nr] = $parser::token(substr($parameter, 1, -1), $parser->data(), false, $parser);
            }
            elseif(
                substr($parameter, 0, 1) == Parameter::QUOTE_SINGLE &&
                substr($parameter, -1, 1) == Parameter::QUOTE_SINGLE
            ){
                $parameters[$nr] = substr($parameter, 1, -1);
            } else {
                $type = gettype($parameter);
                if($type == Parse::TYPE_STRING && is_numeric($parameter)){
                    $parameters[$nr] = $parameter + 0;
                }
                elseif($type == Parse::TYPE_STRING){
                    $test = strtolower($parameter);
                    if($test == Cast::TRANSLATE_TRUE){
                        $parameters[$nr] = true;
                    }
                    elseif($test == Cast::TRANSLATE_FALSE){
                        $parameters[$nr] = false;
                    }
                    elseif($test == Cast::TRANSLATE_NULL){
                        $parameters[$nr] = null;
                    }
                    elseif(substr($parameter, 0, 1) == Variable::SIGN){
                        $parameter = substr($parameter, 1);
                        $parameters[$nr] = $parser->data($parameter);
                    }
                }
            }
       }
       if($parser->data('priya.debug') === true){
//            var_dump($string);
//            var_dump($parameters);
//            die;
       }
       return $parameters;
    }
    */

    public static function execute($tag=array(), $attribute='', $parser=null){
        $mask = Tag::OPEN . TAG::CLOSE;
        foreach($tag[Tag::PARAMETER] as $nr => $parameter){
            if(Method::is($parameter, $parser)){
                $parse = Tag::OPEN . trim($parameter, $mask) . Tag::CLOSE;
                $parse  = Parse::token($parse, $parser->data(), false, $parser);
                $tag[Tag::PARAMETER][$nr] = $parse;
            }
            /*
            elseif(Variable::is($parameter, $parser)){
                var_dump($tag);
                throw new Exception('Please implement variable in parameter...');
            }
            */
        }
        return $tag;
    }
}
