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

class Restore extends Cli {
    const DIR = __DIR__;

    public function run(){
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
}
