<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli\Application\Cache;

use Priya\Application;
use Priya\Module\Core\Cli;
use Priya\Module\File\Dir;

class Clear extends Cli {
    const DIR = __DIR__;

    public function run(){
        $this->clear('smarty');
        return $this->result('cli');
    }

    public function clear($clear=''){
        switch ($clear){
            case 'smarty':
                return $this->clearSmarty();
            break;
        }
    }

    private function clearSmarty(){
        $url =
            $this->data('dir.priya.module') .
              'Smarty' .
              Application::DS .
              'Data' .
              Application::DS
          ;
        $dir = new Dir();
        return $dir->delete($url);
    }
}