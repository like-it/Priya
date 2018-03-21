<?php

namespace Priya\Module\Parse;

use Priya\Module\Core;
use Priya\Module\Parse;
use Exception;

class Parameter extends Core {

    public static function find($string='', $parser=null){
       $parameters = array();
       $explode = explode('})', strrev($string), 2);
       if(!isset($explode[1])){
            return $parameters;
       }       
       $method = token_get_all('<?php $method=method(' . strrev($explode[1]) . ');');
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
                if($parameter == ',' || isset($parameter[1]) && $parameter[1] == ','){
                    $counter++;
                    continue;
                }
                if(isset($parameter[1]) || (isset($parameter[1]) && $parameter[1] == 0)){
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
                    if($test == 'true'){
                        $parameters[$nr] = true;
                    }
                    elseif($test == 'false'){
                        $parameters[$nr] = true;
                    }
                    elseif($test == 'null'){
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
}
