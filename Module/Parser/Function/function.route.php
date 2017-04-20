<?php
use Priya\Module\Handler;

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_route($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(isset($argumentList['name'])){
        $name = $argumentList['name'];
    }
    if(isset($argumentList['attribute'])){
        $attribute = $argumentList['attribute'];
    }
    if(!is_array($attribute)){
        $attribute = (array) $attribute;
    }
    $found = false;
    $route = $parser->route();
    if(empty($route)){
        //maybe empty string trigger error ?
        return $value;
    }
    foreach($route->data() as $routeName => $route){
        if(!is_object($route)){
            continue;
        }
        if(!isset($route->path)){
            continue;
        }
        if(strtolower(str_replace(array('/', '\\'),'', $name)) == strtolower(str_replace(array('/', '\\'),'', $routeName))){
            $found = $route;
            break;
        }
    }
    if(empty($found)){
        die('Route not found');
        trigger_error('Route not found for ('. $name.')', E_USER_ERROR);
    } else {
        $route_path = explode('/', trim(strtolower($route->path), '/'));
        foreach($route_path as $part_nr => $part){
            if(substr($part,0, 2) == '{$' && substr($part, -1) == '}'){
                $route_path[$part_nr] = array_shift($attribute);
            }
            if(empty($route_path[$part_nr])){
                unset($route_path[$part_nr]);
            }
        }
        $path = implode('/', $route_path);
        if(!empty($path)){
            $path .= '/';
        }
        if(stristr($path, Handler::SCHEME_HTTP) === false){
            $path = $parser->data('web.root') . $path;
        }
        return $path;
    }
    return $value;
}
