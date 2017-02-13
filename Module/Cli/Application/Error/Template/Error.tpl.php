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

switch ($this->request('id')){
	case '2' :
		echo "\033[31m[error]\033[0m Priya\Application\Data\Route.json corrupted or missing. Please re-install Priya\n";
		echo "\n";
		echo "\tOptions:\n";
		echo "\t - php " . getcwd() . Application::DS . "Priya.php install             (this will install the newest Priya) \n";
		echo "\t - php " . getcwd() . Application::DS . "Priya.php install --tag 0.0.5 (this will install Priya tagged version 0.0.5) \n";
		echo "\t - php " . getcwd() . Application::DS . "Priya.php install --local     (this will install Priya from the local backup version instead of online) \n";
		echo "\t - php " . getcwd() . Application::DS . "Priya.php install --options   (this will show tagged versions available) \n";
		break;
	default:
		$route = $this->request('route') ? $this->request('route') : $this->request('request');
		echo "\033[31m[error]\033[0m Route not found for request (". $route . ")\n";
		echo "\n";
		echo "\tOptions:\n";
		echo "\t - php " . getcwd() . Application::DS . "Priya.php route --list        (this will show available routes) \n";
		break;
}
?>