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

class Dir {
    const CHMOD = 0740;

    private $node;

    public function ignore($ignore=null, $attribute=null){
        $node = $this->node();
        if(!isset($node)){
            $node = new stdClass();
        }
        if(!isset($node->ignore)){
            $node->ignore = array();
        }
        if($ignore !== null){
            if(is_array($ignore) && $attribute === null){
                $node->ignore = $ignore;
            }
            elseif($ignore == 'delete' && $attribute === null){
                $node->ignore = array();
            }
            elseif($ignore=='list' && $attribute !== null){
                $node->ignore = $attribute;
            }
            elseif($ignore=='find'){
                if(substr($attribute,-1) !== DIRECTORY_SEPARATOR){
                    $attribute .= DIRECTORY_SEPARATOR;
                }
                foreach ($node->ignore as $nr => $item){
                    if(stristr($attribute, $item) !== false){
                        return true;
                    }
                }
                return false;
            } else {
                if(substr($ignore,-1) !== DIRECTORY_SEPARATOR){
                    $ignore .= DIRECTORY_SEPARATOR;
                }
                $node->ignore[] = $ignore;
            }
        }
        $node = $this->node($node);
        return $node->ignore;
    }

    public function read($url='', $recursive=false, $format='flat'){
        if(substr($url,-1) !== DIRECTORY_SEPARATOR){
            $url .= DIRECTORY_SEPARATOR;
        }
        if($this->ignore('find', $url)){
            return array();
        }
        $list = array();
        $cwd = getcwd();
        if(is_dir($url) === false){
            return false;
        }
        chdir($url);
        if ($handle = opendir($url)) {
            while (false !== ($entry = readdir($handle))) {
                $recursiveList = array();
                if($entry == '.' || $entry == '..'){
                    continue;
                }
                $file = new stdClass();
                $file->url = $url . $entry;
                if(is_dir($file->url)){
                    $file->url .= DIRECTORY_SEPARATOR;
                    $file->type = 'dir';
                }
                if($this->ignore('find', $file->url)){
                    continue;
                }
                $file->name = $entry;
                if(isset($file->type)){
                    if(!empty($recursive)){
                        $directory = new dir();
                        $directory->ignore('list', $this->ignore());
                        $recursiveList = $directory->read($file->url, $recursive, $format);

                        if($format !== 'flat'){
                            $file->list = $recursiveList;
                            unset($recursiveList);
                        }
                    }
                } else {
                    $file->type = 'file';
                }
                if(is_link($entry)){
                    $file->link = true;
                }
                $list[] = $file;
                if(!empty($recursiveList)){
                    foreach ($recursiveList as $recursive_nr => $recursive_file){
                        $list[] = $recursive_file;
                    }
                }
            }
        }
        closedir($handle);
        return $list;
    }

    public function delete($dir=''){
        if(is_dir($dir) === false){
            return true;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $nr => $file) {
            if($this->ignore('find', "$dir/$file")){
                continue;
            }
            if(is_dir("$dir/$file")){
                $this->delete("$dir/$file");
            } else {
                unlink("$dir/$file");
                unset($files[$nr]);
            }
        }
        if($this->ignore('find', "$dir")){
            return true;
        }
        return rmdir($dir);
    }

    public function node($node=null){
        if($node !== null){
            $this->setNode($node);
        }
        return $this->getNode();
    }

    private function setNode($node=null){
        $this->node = $node;
    }

    private function getNode(){
        return $this->node;
    }
}