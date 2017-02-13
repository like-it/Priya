<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */ 
namespace Priya\Module\Cli\Module;
use Priya\Module\Core\Cli;


class Update extends Cli {
	const DIR = __DIR__;
			
	public function run(){
// 		$this->read(dirname(self::DIR) . Application::DS  . 'Data' . Application::DS . 'Highcharts.json');
// 		$this->read(__CLASS__);
// 		$this->read('Start.json');
// 		$this->read('jumpstart.json');		
		$this->data('delete', 'this.is.it');
		$this->request('for.special.moments','beer');
		$this->request('delete' ,'for');
		return $this->result('template');
		
		/*
		 * @todo
		 * set color, set line, through result (cli)
		 * - create symlink for every module with a Data/Public to Public/Module
		 * - create a route resource for every module with a Data/Route.json 
		 */
		
		
	}
	
	
}
