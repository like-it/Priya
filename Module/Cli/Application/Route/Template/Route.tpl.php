<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

$request = $this->request('data');
array_shift($request);
array_shift($request);

$route = $this->route();

foreach($request as $key => $value){
    $value = trim($value, ' -');

    switch ($value){
        case 'array' :
            $list = array();
            foreach($route->data() as $name => $route){
                if(empty($route->method)){
                    continue;
                }
                if(!is_array($route->method)){
                    $route->method = (array) $route->method;
                }
                foreach ($route->method as $key => $value){
                    $route->method[$key] = strtoupper($value);
                }
                if(!in_array('CLI', $route->method)){
                    continue;
                }
                if(isset($route->path)){
                    $route->name = $name;
                    $list[] = $route;
                }
            }
            echo $this->object($list, 'json');
        break;
        case 'list' :
            foreach($route->data() as $name => $route){
                if(empty($route->method)){
                    continue;
                }
                if(!is_array($route->method)){
                    $route->method = (array) $route->method;
                }
                foreach ($route->method as $key => $value){
                    $route->method[$key] = strtoupper($value);
                }
                if(!in_array('CLI', $route->method)){
                    continue;
                }
                if(isset($route->path)){
                    echo "\tName: \t\t\t\"" . $name . "\":\n";
                    echo "\tPath or command: \t\"" . $route->path . "\"\n";
                    if(!empty($route->alternative)){
                        echo "\tAlternatives: \t\t(" . implode(',',$route->alternative) . ")\n";
                    }
                    if(isset($route->method) && is_array($route->method)){
                        echo "\tMethods \t\t(" .  implode(',', $route->method) .")\n\n";
                    }
                }
            }
        break;
        case 'list-all' :
            foreach($route->data() as $name => $route){
                if(isset($route->path)){
                    echo "\tName: \t\t\t\"" . $name . "\":\n";
                    echo "\tPath or command: \t\"" . $route->path . "\"\n";
                    if(!empty($route->alternative)){
                        echo "\tAlternatives: \t\t(" . implode(',',$route->alternative) . ")\n";
                    }
                    if(isset($route->method) && is_array($route->method)){
                        echo "\tMethods \t\t(" .  implode(',', $route->method) .")\n\n";
                    }
                }
            }
        break;
        default:
            echo "\033[31m[error]\033[0m Unknown argument supplied for route (". $value . ")\n";
            echo "\n";
            echo "\tOptions:\n";
            echo "\tlist            (this will show available cli routes) \n";
            echo "\tlist-all        (this will show all available routes) \n";
            break;
    }
}