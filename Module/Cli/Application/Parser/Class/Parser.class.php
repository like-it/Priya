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
use Priya\Application;
use Priya\Module\File;

class Parser extends Cli {
    const DIR = __DIR__;

    public function run(){
        $this->request('action', $this->parameter('2') ? $this->parameter('2') : 'create');
        $this->request('attribute', $this->parameter('3') ? $this->parameter('3') : 'functionList');

        switch ($this->request('action')){
            case 'create' :
                $this->create($this->request('attribute'));
            break;
            default:
                $this->debug('unknown action (' . $this->request('action') . ')');
            break;
        }
        return $this->result('cli');
    }

    public function create($attribute=''){
        if(strtolower($attribute) == 'functionlist'){
            $url =
                $this->data('dir.priya.module') .
                'Parser' . Application::DS .
                'Function' . Application::DS
            ;
            $target = \Priya\Module\Parser::FUNCTION_LIST;
            $ignore = array();
            $ignore[] = $target;

            $file = new File();
            $dir = new Dir();
            $dir->ignore($ignore);

            $read = $dir->read($url);
            $target = $url . $target;

            $write = array();
            $write[] = '<?php';
            $write[] = '/*';
            $write[] = 'Priya ' . $this->data('version') . ' (built: ' . $this->data('built') . ')';
            $write[] = 'Copyright (c) 2015-' . date('Y') . ' Remco van der Velde';
            $write[] = 'Generated File (do not modify) (built: ' . date('Y-m-d H:i:s') . ')';
            $write[] = '*/';

            $write = implode(PHP_EOL, $write);

            foreach($read as $node){
                if($node->type != 'file'){
                    continue;
                }
                $this->output(lcfirst(str_ireplace(array('control.', 'function.', 'modifier.', '.php'), '', $node->name)) . PHP_EOL);
                $tmp = $file->read($node->url);
                $tmp = str_replace('<?php', '', $tmp);
                $write .= $tmp;
            }
            $file->write($target, $write);
        }
    }
}
