<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli\Application;

use stdClass;
use DOMXPath;
use ZipArchive;
use DOMDocument;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\Core\Cli;

class Update extends Cli {
    const DIR = __DIR__;

    public function run(){
        $list = $this->createNodeList();
        $wanted= $this->parameter('version');
        $force = $this->parameter('force');
        if(empty($wanted)){
            $highest = $this->data('priya.version');
            $update = false;
            foreach($list as $version => $url){
                $compare = version_compare($version, $highest, '==');
                if($compare === true){
                    $update = $url;
                    break;
                }
            }
            foreach($list as $version => $url){
                $compare = version_compare($version, $highest, '>');
                if($compare === true){
                    $highest = $version;
                    $update = $url;
                }
            }
            if(version_compare($highest, $this->data('priya.version'), '==') && empty($force)){
                $this->output('You are already on this version (' . $this->data('priya.version') . ')' . PHP_EOL);
                $this->output('If you want to force this, use --force' . PHP_EOL);
            } else {
                $this->update($update);
            }
        } else {
            if(version_compare($wanted, $this->data('priya.version'), '==') && empty($force)){
                $this->output('You are already on this version (' . $this->data('priya.version') . ')' . PHP_EOL);
                $this->output('If you want to force this, use --force' . PHP_EOL);
            }
            foreach($list as $version => $url){
                $compare = version_compare($wanted, $version, '==');
                if($compare === true){
                    $this->update($url);
                }
            }
        }
        return $this->result('cli');
    }

    private function update($url=''){
        $file = new File();
        $this->output('Downloading (' . $url .')...' . PHP_EOL);
        $read = $file->read($url);
        $archive = $this->data('dir.priya.update') . basename($url);
        $file->write($archive, $read);
        $this->output('Download complete.' . PHP_EOL);
        $target = $this->data('dir.priya.root');
        $this->extract($archive, $target, 'Priya-' .basename($url, '.zip'));
        $this->output('Extracting archive....' . PHP_EOL);

        $this->output('Extract complete....' . PHP_EOL);

    }

    private function extract($archive='', $target='', $strip='', $update=false){
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
            $node->url = $target . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, str_replace($strip, '', $node->name));
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
            $result['extract'][] = $node->url;
        }
    }


    private function createNodeList(){
        $file = new File();
        $this->output('Reading releases...' . PHP_EOL);
        $read = $file->read('https://github.com/like-it/Priya/releases');
        $doc = new DOMDocument();
        $error = libxml_use_internal_errors(true);
        $doc->loadHTML($read, LIBXML_NOERROR);
        libxml_use_internal_errors($error);

        $xpath = new DOMXpath($doc);
        $elements = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " release-timeline ")]');
        $element = $elements->item(0);
        $html = $doc->saveHTML($element);

        $html = str_replace('href="/like-it', 'href="' . 'https://github.com/like-it', $html);
        $html = str_replace('https://github.com/like-it/Priya/releases/tag/', '#', $html);

        $list = array();

        $explode = explode('href="', $html);
        if(count($explode) == 1){
            return $list;
        }
        foreach($explode as $nr => $part){
            $part = explode('"', $part);
            if(count($part) == 1){
                continue;
            }
            $url = $part[0];
            if(substr($url, -4, 4) == '.zip'){
                $tag = basename($url, '.zip');
                $list[$tag] = $url;
            }
        }
        return $list;
    }

}
