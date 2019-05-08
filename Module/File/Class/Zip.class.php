<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File;

use stdClass;
use ZipArchive;
use Priya\Module\File;
use Priya\Application;

class Zip {

    public function unpack($archive='', $target='', $update=false){
        if(empty($archive)){
            return false;
        }
        if(empty($target)){
            return false;
        }
        if(file_exists($archive) === false){
            return false;
        }
        $target= rtrim(str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $target), '/\\') . DIRECTORY_SEPARATOR;
        $zip = new ZipArchive();
        $zip->open($archive);
        $dirList = array();
        $fileList = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $node = new stdClass();
            $node->name = $zip->getNameIndex($i);
            if(substr($node->name, -1) == '/'){
                $node->type = 'dir';
            } else {
                $node->type = 'file';
            }
            $node->index = $i;
            $node->url = $target . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $node->name);
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
        $result = array();
        foreach($fileList as $node){
            $stats = $zip->statIndex($node->index);
            if(!empty($update)){
                if(file_exists($node->url)){
                    $mtime = filemtime($node->url);
                    if($stats['mtime'] <= $mtime){
                        $result['skip'][] = $node->url;
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
                $result['error'][] = $node->url;
            }
            $result['unpack'][] = $node->url;
        }
        return $result;
    }

    public function pack($read, $target){
        $this->request('date', date('Y-m-d' . ' ' . 'His'));
        $location = $this->data('priya.dir.data') . 'Pack' . Application::DS . $this->request('date') . Application::DS;

        $url = $location . rand(1000, 9999) . Application::DS;

        while(file_exists($url)){
            $url = $location . rand(1000, 9999) . Application::DS;
        }
        if(!file_exists($url)){
            mkdir($url, Dir::CHMOD, true);
        }
        var_dump($url);
        die;
        if(!is_dir($url)){
            return;
        }
        foreach($read as $file){
            $src = $file->url;
            $src = explode($source, $src, 2);
            $file->target = $location . $src[1];

            if($file->type == File::TYPE){
                copy($file->url, $file->target);
            } else {
                mkdir($file->target, Dir::CHMOD, true);
            }
        }
        $this->create($target, $url, $read);
        chmod($target, File::CHMOD);

        $dir = new Dir();
        $dir->delete($url);
    }

    public function create($target='', $location='', $read=array()){
        $dirname = dirname($target);
        if(!is_dir($dirname)){
            mkdir($dirname, Dir::CHMOD, true);
        }
        if(!is_dir($dirname)){
            return;
        }
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
        }
        $zip->close();
    }
}