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
		case 'list' :
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
			echo "\tlist        (this will show available routes) \n";
			break;
	}
}
?>