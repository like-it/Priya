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
use Priya\Module\File;
use DOMDocument;
use DOMXPath;

class Update extends Cli {
    const DIR = __DIR__;

    public function run(){
        $list = $this->createNode();
        $this->debug($list);
        return $this->result('cli');
    }

    private function createNode(){
        $file = new File();
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
