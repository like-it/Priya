<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */ 
namespace Priya\Module\Route;
use Priya\Module\Core\Data;
use stdClass;

class Process extends \Priya\Module\Core\NodeList {
	const DIR = __DIR__;
			
	public function run(){
		$this->read(__CLASS__);	
		$this->process();
		return $this->result('template');				
	}
	
	public function process(){
		$request = $this->request('data');		
		$resource = $this->request('data.resource');
		
		$data = new Data();
		$data->read($resource);
		$route = $this->route('create', $request);
		$name = trim(str_replace('/', '-', strtolower($route->path)), '/');
		$data->data($name, $route);
		return $this->data('process', $data->write($resource));
	}
	
	public function route ($route=null, $request=null){
		if($route !== null){
			if($route == 'create'){
				if($request !== null){
					return $this->createRoute($request);
				}
			}
		}
	}
	
	private function createRoute($request = ''){
		$route = new stdClass();
		$route = clone $request;
		$route->method = explode(',', $route->method);
		$route->default->format = explode(',', $route->default->format);
		unset($route->resource);
		return $route;
	}
	
}
