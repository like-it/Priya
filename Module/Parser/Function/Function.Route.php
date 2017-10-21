<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_route($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(isset($argumentList['name'])){
        $name = $argumentList['name'];
    } else {
        $name = false;
    }
    if(isset($argumentList['attribute'])){
        $attribute = $argumentList['attribute'];
    } else {
        $attribute = false;
    }
    if($name === false){
        $name = array_shift($argumentList);
    }
    if($attribute === false){
        $attribute = array_shift($argumentList);
    }
    if(!is_array($attribute)){
        $attribute = (array) $attribute;
    }
    $route = $parser->route();
    if(empty($route)){
        $function['execute'] = false;
    } else {
        $route->data($parser->data());
        $result = $route->route($name, $attribute);
        $function['execute'] = $result;
    }
    return $function;
}
