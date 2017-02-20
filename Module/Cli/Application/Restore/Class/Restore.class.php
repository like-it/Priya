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
            $this->createPoint();
        }
        if($this->parameter('list')){
            $this->createList();
            $this->cli('create', 'List');
        }
        if($this->parameter('point')){
            $this->restorePoint();
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
        $ignore[] = '.git';
        foreach($read as $location){
            $location = trim($location);
            $ignore[] = $location;
        }
        $dir->ignore('list', $ignore);
        $read = $dir->read($url, true);

        if(is_dir($this->data('dir.restore'))===false){
            mkdir($this->data('dir.restore'), Dir::CHMOD, true);
        }
        $target = $this->data('dir.restore') . $this->data('version') . '.zip';
        if(file_exists($target)){
            unlink($target);
        }
        $zip = new ZipArchive();
        $res = $zip->open($target, ZipArchive::CREATE);
        foreach($read as $node){
            if($node->url == $target){
                continue;
            }
            $filename= str_replace(Application::DS, '/', $node->url);
            $location = explode($this->data('dir.root'), $filename, 2);
            $location = implode('', $location);
            $zip->addFile($filename, $location);
        }
        $zip->close();
    }

    private function createList(){
        $url = $this->data('dir.restore');
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

    private function restorePoint(){
        $collect = false;
        $parameters = array();
        $data = $this->request('data');
        foreach($data as $key => $parameter){
            if($parameter == 'point'){
                $collect = true;
                continue;
            }
            if($parameter == 'update'){
                continue;
            }
            if(!empty($collect)){
                $parameters[] = $parameter;
            }
        }
        $nodeList = $this->createList();
        $count = count($parameters);
        $restore_path = '';
        $restore_point = '';
        switch ($count){
            case 0 :
                $search = $this->data('nodeList');
                $restore_path = $this->data('dir.root');
                $restore_point = $this->object(reset($search));
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
                break;
        }
        $this->restore($restore_path, $restore_point);
    }

    private function restore($path='', $archive=array()){
        if(empty($archive->url)){
            return false;
        }
        if(empty($path)){
            return false;
        }
        if(file_exists($archive->url) === false){
            return false;
        }
        $path = rtrim(str_replace(array('\\', '/'), '/', $path), '/') . '/';
        $zip = new ZipArchive();
        $zip->open($archive->url);
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
            $node->url = $path . $node->name;
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
        $update = $this->parameter('update');
        foreach($fileList as $node){
            $stats = $zip->statIndex($node->index);
            if(!empty($update)){
                if(file_exists($node->url)){
                    $mtime = filemtime($node->url);
                    if($stats['mtime'] <= $mtime){
                        continue;
                    }
                    var_dump($node->url);
                }
            }
            if(file_exists($node->url)){
                unlink($node->url);
            }
            $file = new File();
            $file->write($node->url, $zip->getFromIndex($node->index));
            chmod($node->url, File::CHMOD);
            touch($node->url, $stats['mtime']);
        }
    }
}
