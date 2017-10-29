<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */
namespace Priya\Module\File;

use stdClass;
use zipArchive;
use Priya\Module\File;

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

    public function pack($archive='', $source=''){
        //source can be a filelist created by dir (flat)
        //use a tmp directory to copy see restore
    }
}