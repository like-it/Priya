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
            }
            elseif(
                $start == '$'
            ){
                $attribute = substr($parameter, 1);
                $list[$nr] = $parser->data($attribute);
            }


            else {
                $list[$nr] = $parameter;
            }
        }
        return $list;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
        return $tag;
    }
}
