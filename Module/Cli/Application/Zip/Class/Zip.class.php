<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use stdClass;
use ZipArchive;
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
            $this->extract($this->parameter(3));
        }

        return $this->result('cli');
    }


    public function pack($url){
        $parser = new Parser($this->handler(), $this->route(), $this->data());
        $parser->read($url);
        $parser->cwd('cwd', getcwd());

        $source = $parser->data('archive.source');
        $target = $parser->data('archive.target');

        $this->output('[pack] Read directory: ' . $source . PHP_EOL);

        $dir = new Dir();
        $dir->ignore($parser->data('archive.ignore'));
        $read = $dir->read($source, true);

        $this->request('date', date('Y-m-d' . ' ' . 'His'));

        $location = $this->data('priya.dir.data') . 'Pack' . Application::DS . $this->request('date') . Application::DS;

        if(!file_exists($location)){
            mkdir($location, Dir::CHMOD, true);
        }
        if(!is_dir($location)){
            return;
        }
        $count = count($read);
        $counter = 0;
        $mod = $count / 100;
        if($mod < 1){
            $mod = 1;
        }
        $this->output('[pack] Temp writing files: (amount: ' . $count  . ')'  . PHP_EOL);
        foreach($read as $file){
            $counter++;
            $src = $file->url;
            $src = explode($source, $src, 2);
            $file->target = $location . $src[1];

            if($file->type == File::TYPE){
                copy($file->url, $file->target);
            } else {
                mkdir($file->target, Dir::CHMOD, true);
            }
            if($counter % $mod == 0){
                $this->output('[temp] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
            elseif($counter >= $count){
                $this->output('[temp] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
        }
        $this->create($target, $location, $read);
        chmod($target, File::CHMOD);
        $this->output('[pack] Removing Directory: ' . $location . PHP_EOL);
        $dir = new Dir();
        $dir->delete($location);
        $this->output('[pack] Complete: ' . $target . PHP_EOL);
    }

    public function create($target='', $location='', $read=array()){
        $dirname = dirname($target);
        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        if(!is_dir($dirname)){
            return;
        }
        $this->output('[archive] Creating archive: ' . $target  . PHP_EOL);

        $zip = new ZipArchive();
        $res = $zip->open($target, ZipArchive::CREATE);
        $count = count($read);
        if($count == 0){
            return;
        }
        $counter = 0;
        $mod = $count / 100;
        if($mod < 1){
            $mod = 1;
        }
        foreach($read as $node){
            $counter++;
            $skip = false;
            if($node->url == $target){
                $skip = true;
            }
            if($node->target == $target){
                $skip = true;
            }
            if($node->type != File::TYPE){
                $skip = true;
            }
            if(empty($skip)){
                $loc = explode($location, $node->target, 2);
                $loc = implode('', $loc);
                $zip->addFile($node->target, $loc);
            }
            if($counter % $mod == 0){
                $this->output('[archive] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
            elseif($counter >= $count){
                $this->output('[archive] ' . round(($counter / $count) * 100, 2) . '%' . PHP_EOL);
            }
        }
        $this->output('[archive] Closing archive...' . PHP_EOL);
        $zip->close();
    }

    public function extract($url=''){
        $update = false;
        $parser = new Parser($this->handler(), $this->route(), $this->data());
        $parser->read($url);
        $parser->data('cwd', getcwd());

        $source = $parser->data('archive.source');
        $target = $parser->data('archive.target');

        if(!file_exists($target)){
            mkdir($target, Dir::CHMOD, true);
        }
        if(!is_dir($target)){
            return;
        }
        $zip = new ZipArchive();
        $zip->open($source);

        $this->output('[extract] Read archive: ' . $source . PHP_EOL);

        $dirList = array();
        $fileList = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $node = new stdClass();
            $isDir = false;
            $node->name = $zip->getNameIndex($i);
            if(substr($node->name, -1) == '/'){
                $node->type = Dir::TYPE;
            } else {
                $node->type = File::TYPE;
            }
            $node->index = $i;
            $node->url = $target . str_replace(array('/', '\\'), Application::DS, $node->name);
            if($node->type == Dir::TYPE){
                $dirList[] = $node;
            } else {
                $fileList[] = $node;
            }
        }
        $this->output('[extract] Files found: ' . count($fileList) . PHP_EOL);
        $this->output('[extract] Directories found: ' . count($dirList) . PHP_EOL);
        $this->output('[extract] Creating directories...' . PHP_EOL);
        foreach($dirList as $dir){
            if(is_dir($dir->url) === false){
                mkdir($dir->url, Dir::CHMOD, true);
            }
        }
        $skip = 0;
        $count = 0;
        $error = array();
        $this->output('[extract] Extracting files...' . PHP_EOL);
        foreach($fileList as $node){
            $stats = $zip->statIndex($node->index);
            if(!empty($update)){
                if(file_exists($node->url)){
                    $mtime = filemtime($node->url);
                    if($stats['mtime'] <= $mtime){
                        $skip++;
                        continue;
                    }
                }
            }
            $dir = dirname($node->url);
            if(file_exists($dir) && !is_dir($dir)){
                unlink($dir);
                mkdir($dir, Dir::CHMOD, true);
            }
            if(file_exists($dir) === false){
                mkdir($dir, Dir::CHMOD, true);
            }
            if(file_exists($node->url)){
                unlink($node->url);
            }
            $file = new File();
            $write = $file->write($node->url, $zip->getFromIndex($node->index));
            if($write !== false){
                chmod($node->url, File::CHMOD);
                touch($node->url, $stats['mtime']);
            } else {
                $error[] = $file;
            }
            $this->output($node->url . PHP_EOL);
            $count++;
        }
        $this->output('[extract] Total files extracted: ' . $count . PHP_EOL);
        $this->output('[extract] Total files skipped: ' . $skip . PHP_EOL);
        $this->output('[extract] Error(s): ' . count($error) . PHP_EOL);
    }

}