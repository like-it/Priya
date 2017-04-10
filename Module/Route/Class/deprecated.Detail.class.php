<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */ 
namespace Priya\Module\Route;
// use Priya\Application;
use Priya\Module\File\Dir;


class Detail extends \Priya\Module\Core\NodeList {
	const DIR = __DIR__;
	
	
	public function run(){
		$this->read(__CLASS__);	
		$this->data('resource', $this->resource('create'));
		return $this->result('template');				
	}
	
	public function resource($resource=null){
		if($resource !== null){
			if($resource == 'create'){
				return $this->createResource();
			}
		}
	}
	
	private function createResource(){
		$dir = new Dir();
		$dir->ignore('.git');
		$read = $dir->read($this->data('dir.root'), true);
		$resource = array();
		foreach($read as $file){
			if(stristr($file->name, 'route.json') !== false){
				$resource[] = $file;
			}
		}
		return $resource;			
	}
	
	
	
}
