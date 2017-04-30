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

if($this->parameter('create')){
	if($this->error('read') === true){
		echo $this->color('red') . '[error]' . $this->color('end') . ' ' . $this->data('dir.priya.data') . 'Config.json corrupted or missing. Please re-install /restore Priya.' . "\n";	
		$this->data('options', true);
		echo "\t" . 'Options:' . "\n";
		$this->cli('create', 'Priya\Module\Cli\Application\Install');
		$this->cli('create', 'Priya\Module\Cli\Application\Restore');
	}
	elseif($this->error('file_exists') === true){
		echo $this->color('red') . '[error]' . $this->color('end') . ' ' . $this->data('dir.priya.data') . 'Config.json file exists please use --force.' . "\n";
	} else {	
		echo 'config' ."\n";	
	}
}
elseif($this->parameter('get')){
		echo $this->data('attribute') . "\n";	
}
elseif($this->parameter('set')){
	if($this->error('public_html')){
		echo $this->color('red') . '[error]' . $this->color('end') . ' ' . 'File exists: ' . $this->error('public_html') . "\n";
	} else {
		echo $this->object($this->data(), 'json') . "\n";
	}
}
elseif($this->parameter('delete')){
	if($this->error('public_html')){
		echo $this->color('red') . '[error]' . $this->color('end') . ' ' . 'Required: ' . $this->error('public_html') . "\n";
	}
}

?>