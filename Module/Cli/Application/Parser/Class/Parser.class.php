<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
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
                $this->data('priya.dir.module') .
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
            $write[] = 'Priya ' . $this->data('priya.version') . ' (built: ' . $this->data('priya.built') . ')';
            $write[] = 'Copyright (c) 2015-' . date('Y') . ' Remco van der Velde';
            $write[] = 'Generated File (do not modify) (built: ' . date('Y-m-d H:i:s') . ')';
            $write[] = '*/';
            $write[] = '';

            $write = implode("\n", $write);

            $combination = '';

            foreach($read as $node){
                if($node->type != File::TYPE){
                    continue;
                }
                $this->output('Combining: ' . lcfirst(str_ireplace(array('control.', 'function.', 'modifier.', '.php'), '', $node->name)) . PHP_EOL);
                $tmp = $file->read($node->url);
                $tmp = str_replace('<?php', '', $tmp);
                $explode = explode("\n", $tmp);
                $match = 'use';
                foreach($explode as $nr => $record){
                    $class = trim($record, " \t");
                    if(substr($class, 0, 3) == $match){
                        $use[$class] = $nr;
                        unset($explode[$nr]);
                    }
                }
                $tmp = implode("\n", $explode);
                $combination .= $tmp;
            }
            foreach ($use as $class => $nr){
                $write .= $class . "\n";
            }
            $write .= $combination;
            $this->output('In: ' . basename($target) . PHP_EOL);
            $file->write($target, $write);
        }
    }
}
