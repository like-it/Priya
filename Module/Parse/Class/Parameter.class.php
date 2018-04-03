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

    public static function list($string='', $parser=null){
        $list = array();
        if(
            empty($string)
        ){
            return $list;
        }
        if(strpos($string, ',') === false){
            $list[] = trim($string);
            return $list;
        } else {
            $split = str_split($string, 1);
            $set_depth = 0;
            $counter = 0;
            $list[$counter] = '';
            foreach($split as $char){
                if($char == '('){
                    $set_depth++;
                }
                elseif($char == ')'){
                    $set_depth--;
                }
                if($char == ',' && $set_depth == 0){
                    $counter++;
                    $list[$counter] = '';
                    continue;
                }
                $list[$counter] .= $char;
            }
            foreach ($list as $nr => $value){
                $list[$nr] = trim($value);
            }
            return $list;
        }
    }

    /**
     * Methods are not allowed as parameter: K.I.S.S.
     * keep it simple, assigning it as variable first is faster too.
     * Parameter parsing on methods would be complex and cost high on performance
     * even performance lost when paramater has no method.
     *
     * @param string $string
     * @param unknown $parser
     * @throws Exception
     * @return string|array|NULL[]|string[]|unknown[]|unknown|NULL[]
     */
    public static function find($string='', $parser=null){
        $list = Parameter::list($string, $parser);

        foreach($list as $nr => $parameter){
            $set_counter = 0;
            if(strpos($parameter, '(') !== false){
                //set...
                $statement = Set::statement($parameter, $parser);
//                 var_dump($statement);
                $search = '(' . implode(Parameter::EMPTY, $statement) . ')';
                while($statement){
                    $set_counter++;
//                     $statement = Set::statement($set);
//                     var_dump($statement);
                    $operator_counter = 0;
                    while (Operator::has($statement, $parser)){
                        $operator_counter++;
                        $statement = Operator::statement($statement, $parser);
//                         var_dump($statement);
                        if($operator_counter > Operator::MAX){
                            throw new Exception('Operator::MAX exceeded');
                            break;
                        }
                    }
                    if($set_counter> Set::MAX){
                        throw new Exception('Set::MAX exceeded');
                        break;
                    }
                    $replace = $statement[0];
//                     var_dump($search);
//                     var_dump($replace);
                    if($search == $parameter){
                        $parameter = $replace;
                    } else {
                        $parameter = str_ireplace($search, $replace, $parameter);
                    }
                    $statement = Set::statement($parameter, $parser);
                    if($statement !== false){
                        $search = '(' . implode(Parameter::EMPTY, $statement) . ')';
                    }
//                     var_dump($parameter);
//                     var_dump($statement);
                }
            }
//             var_dump($set_counter);
//             var_dump($operator_counter);
//             var_dump($parameter);
            $statement = Set::statement('(' . $parameter . ')', $parser);
//             var_dump($parameter);
//             var_dump($statement);
            if($statement !== false){;
                $search = implode(Parameter::EMPTY, $statement);
            }
//             var_dump($statement);
            $operator_counter = 0;
            while (Operator::has($statement, $parser)){
                $operator_counter++;
                $statement = Operator::statement($statement, $parser);
                if($operator_counter > Operator::MAX){
                    throw new Exception('Operator::MAX exceeded');
                    break;
                }
            }
//             var_dump($operator_counter);
            $replace = $statement[0];
            if($search == $parameter){
                $parameter = $replace;
            } else {
                $parameter = str_ireplace($search, $replace, $parameter);
            }
            $start = substr($parameter, 0, 1);
            $end = substr($parameter, -1, 1);
            if(
                $start == '"' &&
                $end == '"'
            ){
                $parameter = substr($parameter, 1, -1);
                $parameter= Parse::token($parameter, $parser->data(), false, $parser);
                $list[$nr] = $parameter;
            }
            elseif(
                $start == '\'' &&
                $end == '\''
            ){
                $parameter = substr($parameter, 1, -1);
                $list[$nr] = $parameter;
            } else {
                $list[$nr] = $parameter;
            }
        }
        return $list;
    }

    /*
    public static function get($statement=array(), $parser=null){
        if(!isset($statement[1])){
            return $statement[0];
        }
        /**
         * make floats and strings

        var_dump($statement);
        foreach($statement as $nr => $part){
            if($part == '.'){
                $previous = $nr - 1;
                $next = $nr + 1;
                if(
                    !isset($statement[$previous]) &&
                    isset($statement[$previous -1])
                ){
                    $left = $statement[$previous -1];
                } else {
                    $left = $statement[$previous];
                }
                $right = '';
                if(isset($statement[$next])){
                    $right = $statement[$next];
                }
                if(
                    is_numeric($left) &&
                    is_numeric($right)
                ){
                    $float = floatval($left . '.' . $right);
                    $statement[$nr] = $float;
                    unset($statement[$nr + 1]);
                    unset($statement[$nr - 1]);
                } else {
                    $string = $left . $right;
                    $statement[$nr] = $string;
                    unset($statement[$nr + 1]);
                    unset($statement[$nr - 1]);
                }
            }
        }
        $counter = 0;
        while(Operator::has($statement, $parser)){
            $statement = Operator::statement($statement, $parser);
            $counter++;
            if(!isset($statement[1])){
                break;
            }
            if($counter > Operator::MAX){
                throw new Exception('Operator::MAX reached in Parameter::find');
                break;
            }
        }
        return $statement[0];
    }
    */

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
