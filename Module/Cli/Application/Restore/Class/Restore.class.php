<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Cli\Application;
use Priya\Module\Core\Cli;
use Priya\Module\File\Dir;
use Priya\Module\File;
use Priya\Module\Core\Data;

class Restore extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('create')){
            $this->createPoint();
        }
        if($this->parameter('list')){
            $this->createList();
            $this->cli('create', 'List');
            var_dump('list');
            die;
        }
        if($this->parameter('point')){
            var_dump('point');
            die;
        }
        $this->data('step', 'download');
        $this->cli('create', 'Restore');
        $this->data('step', 'download-complete');
        $this->data('tag', '0.0.4');
        $this->cli('create', 'Restore');
        $this->data('step', 'download-failure');
        $this->cli('create', 'Restore');
        $this->data('step', 'tag');
        $this->cli('create', 'Restore');
        $this->data('step', 'install');
        $this->cli('create', 'Restore');
        $this->data('step', 'install-complete');
        return $this->result('cli');
    }

    private function createPoint(){
        $file = new File();
        $read = $file->read($this->data('dir.priya.root') . '.gitignore');
        $url = $this->data('dir.root');
        $dir = new Dir();
        if(!empty($read)){
            $read = $this->explode_multi(array("\n", "\r\n"), $read);
        }
        $ignore = array();
        foreach($read as $location){
            $location = trim($location);
            $ignore[] = $location;
        }
        $dir->ignore('list', $ignore);
        $read = $dir->read($url, true);
        var_dump(count($read));
    }

    private function createList(){
        $url = $this->data('dir.restore');
        $dir = new Dir();
        $read = $dir->read($url);
        var_dump($read);
        die;
    }
}
