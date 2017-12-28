<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use Priya\Application;
use Priya\Module\Core\Cli;
use Priya\Module\Parser;
use Priya\Module\File;
use Priya\Module\File\Dir;

class Zip extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('pack')){
            if($this->parameter(3)){
                $this->pack($this->parameter(3));
            }
        }
        if($this->parameter('extract')){

        }

        return $this->result('cli');
    }


    public function pack($url){
        $parser = new Parser($this->handler(), $this->route(), $this->data());
        $parser->test = true;
        $parser->read($url);

        $parser->data('cwd', getcwd());

        $archive = $parser->data('archive');

        var_dump($archive);
        die;

        $source = $parser->data('archive.source');

        $dir = new Dir();
        $dir->ignore($parser->data('archive.ignore'));
        $read = $dir->read($source, true);

        $this->request('date', date('Y-m-d' . Application::DS . 'H-i-s'));

        $target = $this->data('priya.dir.data') . 'Pack' . Application::DS . $this->request('date') . Application::DS;

        if(!file_exists($target)){
            mkdir($target, Dir::CHMOD, true);
        }
        if(!is_dir($target)){
            return;
        }
        foreach($read as $file){
            $src = $file->url;
            $src = explode($source, $src, 2);
            $file->target = $target . $src[1];

            if($file->type == File::TYPE){
                copy($file->url, $file->target);
            } else {
                mkdir($file->target, Dir::CHMOD, true);
            }
        }
        die('done');
    }
}