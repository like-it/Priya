<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Parameter extends Core {

    public static function find($string='', $parser=null){
       $parameters = array();
       $method = token_get_all('<?php $method=method(' . $string . ');');
       $collect = false;
       $counter = 0;
       $set_depth = 0;

       foreach ($method as $nr => $parameter){
            if($parameter == '(' || isset($parameter[1]) && $parameter[1] == '('){
                $set_depth++;
                if($set_depth == 1){
                    $collect = true;
                    continue;
                }
            }
            if($parameter == ')' || isset($parameter[1]) && $parameter[1] == ')'){
                if($set_depth == 1){
                    $collect = false;
                }
                $set_depth--;
            }
            if($collect){
                if(
                    $set_depth == 1 &&
                    (
                        $parameter == ',' ||
                        isset($parameter[1]) &&
                        $parameter[1] == ','
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
       foreach($parameters as $nr => $set){
            $parameters[$nr] = trim(implode('', $set), ' ');
       }
       foreach ($parameters as $nr => $parameter){
            if(
                substr($parameter, 0, 1) == '"' &&
                substr($parameter, -1, 1) == '"'
            ){
                $parameters[$nr] = $parser::token(substr($parameter, 1, -1), $parser->data(), false, $parser);
            }
            elseif(
                substr($parameter, 0, 1) == '\'' &&
                substr($parameter, -1, 1) == '\''
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
                    elseif(substr($parameter, 0, 1) == '$'){
                        $parameter = substr($parameter, 1);
                        $parameters[$nr] = $parser->data($parameter);
                    }
                }
            }
       }
       return $parameters;
    }

    public static function execute($tag=array(), $attribute='', $parser=null){
        foreach($tag[Tag::PARAMETER] as $nr => $parameter){
            if(Method::is($parameter, $parser)){
                $parse = '{' . trim($parameter, '{}') . '}';
                $parse  = Parse::token($parse, $parser->data(), false, $parser);
                $tag[Tag::PARAMETER][$nr] = $parse;
            }
            elseif(Variable::is($parameter, $parser)){
                var_dump($tag);
                throw new Exception('Please implement variable in parameter...');
            }
        }
        return $tag;
    }
}
