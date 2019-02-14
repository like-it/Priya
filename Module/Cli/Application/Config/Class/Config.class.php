<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli\Application;

use Priya\Application;
use Priya\Module\Core\Cli;
use Priya\Module\File;

class Config extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('create')){
            $url = $this->parameter('create', 1);
            if(empty($url)){
                $url = $this->data('module.dir.data') . 'Create.json';
            }
            $target = $this->data('dir.data') . Application::CONFIG;
            if(File::exist($target)){
                $this->data('execute', 'exist');
            } else {
                File::write($target, File::read($url));
                $this->data('execute', 'create');
            }
            return Config::execute($this);
        }
        if($this->parameter('mail')){
            Config::execute($this, null, 'Mail');
            return;
        }



        return $this->object($this->data(), 'json');
    }
}
