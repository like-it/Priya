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
use ZipArchive;
use Priya\Application;
use stdClass;

class Restore extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->parameter('create')){
            $this->create();
        }
        if($this->parameter('list')){
            $this->nodeList('create');
            $this->cli('create', 'List');
        }
        if($this->parameter('point')){
            $this->point();
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

    public function create($filename=''){
        if(empty($filename)){
            $version = $this->data('version');
            if(empty($version)){
                return false;
            }
            if($version == '0.0.0'){
                return false;
            }
            $filename = $version . '.zip';
        }
        if(empty($this->data('dir.priya.restore'))){
            return false;
        }

        $file = new File();
        $read = $file->read($this->data('dir.priya.root') . '.gitignore');
        $url = $this->data('dir.root');
        $dir = new Dir();
        if(!empty($read)){
            $read = $this->explode_multi(array("\n", "\r\n"), $read);
        }
        $ignore = array();
        $ignore[] = '.git';	//move to $this->data('dir.data') . '.ignore' and read .ignore as well
        foreach($read as $location){
            $location = trim($location);
            $ignore[] = str_replace(array('/', '\\'), Application::DS, $location);
        }
        $dir->ignore('list', $ignore);
        $read = $dir->read($url, true);

        if(is_dir($this->data('dir.priya.restore')) === false){
            mkdir($this->data('dir.priya.restore'), Dir::CHMOD, true);
        }
        if(is_dir($this->data('dir.priya.restore') . 'Temp' . Application::DS) === false){
            mkdir($this->data('dir.priya.restore') . 'Temp' . Application::DS, Dir::CHMOD, true);
        } else {
            $dir->ignore('list', array());
            $temp = $dir->read($this->data('dir.priya.restore') . 'Temp' . Application::DS, true);
            foreach($temp as $node){
                if($node->type != 'file'){
                    continue;
                }
                unlink($node->url);
            }
            krsort($temp);
            foreach($temp as $node){
                if($node->type != 'dir'){
                    continue;
                }
                rmdir($node->url);
            }
        }
        foreach($read as $node){
            $node->target = explode($url, $node->url, 2);
            $node->target = $this->data('dir.priya.restore') . 'Temp' . Application::DS . implode('', $node->target);
            if($node->type == 'dir'){
                mkdir($node->target, DIR::CHMOD, true);
            }
            elseif($node->type == 'file'){
                copy($node->url, $node->target);
            } else {
                continue;
            }
            touch($node->target, filemtime($node->url));
        }
        $target = $this->data('dir.priya.restore') . $filename;
        if(file_exists($target)){
            unlink($target);
        }
        $zip = new ZipArchive();
        $res = $zip->open($target, ZipArchive::CREATE);
        foreach($read as $node){
            if($node->url == $target){
                continue;
            }
            if($node->target == $target){
                continue;
            }
            if($node->type != 'file'){
                continue;
            }
            $location = explode($this->data('dir.root'), $node->url, 2);
            $location = implode('', $location);
            $zip->addFile($node->target, $location);
//             $zip->addFromString($location, file_get_contents($node->url));
        }
        $zip->close();
        foreach($read as $node){
            if($node->type != 'file'){
                continue;
            }
            unlink($node->target);
        }
        krsort($read);
        foreach($read as $node){
            if($node->type != 'dir'){
                continue;
            }
            rmdir($node->target);
        }
        rmdir($this->data('dir.priya.restore') . 'Temp' . Application::DS);
        return $target;
    }

    public function nodeList($nodeList=null){
        if($nodeList !== null){
            if($nodeList == 'create'){
                return $this->createNodeList();
            }
        }
    }

    private function createNodeList(){
        $url = $this->data('dir.priya.restore');
        $dir = new Dir();
        $read = $dir->read($url);
        if(is_array($read) || is_object($read)){
            foreach ($read as $node){
                $node->mtime = filemtime($node->url);
            }
            $this->data('nodeList', $read);
            $this->sort('nodeList', 'mtime', 'DESC');
        }
        return $this->data('nodeList');
    }

    private function point(){
        $collect = false;
        $parameters = array();
        $data = $this->request('data');
        $skip = false;
        foreach($data as $key => $parameter){
            if(!empty($skip)){
                $skip = false;
                continue;
            }
            if($parameter == 'point'){
                $collect = true;
                continue;
            }
            if($parameter == 'dir.data'){
                $skip = true;
                continue;
            }
            if($parameter == 'update'){
                continue;
            }
            if(!empty($collect)){
                $parameters[] = $parameter;
            }
        }
        $nodeList = $this->nodeList('create');
        $count = count($parameters);
        $restore_path = '';
        $restore_point = '';
        $url = '';
        switch ($count){
            case 0 :
                $search = $this->data('nodeList');
                $restore_path = $this->data('dir.root');
                $restore_point = $this->object(reset($search));
                if(isset($restore_point->url)){
                    $url = $restore_point->url;
                }
                break;
            case 1 :
                $search = $this->search($this->data('nodeList'), reset($parameters), 'name');
                if(empty($search)){
                    $restore_path = reset($parameters);
                    $search = $this->data('nodeList');
                } else {
                    $restore_path = $this->data('dir.root');
                }
                $restore_point = $this->object(reset($search));
                if(isset($restore_point->url)){
                    $url = $restore_point->url;
                }
                break;
            case 2 :
                $search = $this->search($nodeList, reset($parameters), 'name');
                $restore_path = end($parameters);
                if(empty($search)){
                    $search = $this->search($nodeList, end($parameters), 'name');
                    $restore_path = reset($parameters);
                }
                if(empty($search)){
                    trigger_error('Bad parameter', E_USER_ERROR);
                }
                $restore_point = $this->object(reset($search));
                if(isset($restore_point->url)){
                    $url = $restore_point->url;
                }
                break;
        }
        $this->extract($url, $restore_path, $this->parameter('update'));
    }

    public function extract($url='', $path='', $update=false){
        if(empty($url)){
            return false;
        }
        if(empty($path)){
            return false;
        }
        if(file_exists($url) === false){
            return false;
        }
        $path = rtrim(str_replace(array('\\', '/'), '/', $path), '/') . '/';
        $zip = new ZipArchive();
        $zip->open($url);
        $dirList = array();
        $fileList = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $node = new stdClass();
            $isDir = false;
            $node->name = $zip->getNameIndex($i);
            if(substr($node->name, -1) == '/'){
                $node->type = 'dir';
            } else {
                $node->type = 'file';
            }
            $node->index = $i;
            $node->url = $path . str_replace(array('/', '\\'), Application::DS, $node->name);
            if($node->type == 'dir'){
                $dirList[] = $node;
            } else {
                $fileList[] = $node;
            }
        }
        foreach($dirList as $dir){
            if(is_dir($dir->url) === false){
                mkdir($dir->url, Dir::CHMOD, true);
            }
        }
        $skip = 0;
        $error = array();
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

            if(file_exists($node->url)){
                unlink($node->url);
            }
            $file = new File();
            $write = $file->write($node->url, $zip->getFromIndex($node->index));
            if($write !== false){
                chmod($node->url, File::CHMOD);
                touch($node->url, $stats['mtime']);
            }
        }
        echo 'skipped: ' . $skip . PHP_EOL;
    }
}
