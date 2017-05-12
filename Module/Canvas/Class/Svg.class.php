<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Canvas;

use Priya\Module\File;

class Svg extends \Priya\Module\Core\NodeList {
    const DIR = __DIR__;

    public function run($result=''){
        $this->read(__CLASS__);
        $result = $this->convert($result);
        return $this->result($result);
    }

    public function convert($convert=''){
        $convert = $this->convertCss($convert);
        $convert = $this->convertHtml($convert);
        return $convert;
    }

    private function convertCss($convert=''){
        $nodeList = array();
        $tmp = explode('url(', $convert);
        array_shift($tmp);
        foreach ($tmp as $nr =>  $part){
            $array = $this->explode_multi(array("\r", "\n", "\r\n"), $part);
            $key = 'url(' . reset($array);
            $value = $key;
            $value = str_replace(array('url(', ');', '"'), '', $value);
            $nodeList[$key] = $value;
        }
        foreach($nodeList as $key => $value){
            $url = $this->data('dir.vendor') . $this->handler()->removeHost($value);
            if(file_exists($url)){
                $file = new File();
                $read = base64_encode($file->read($url));
                $nodeList[$key] = 'data:image/jpg;base64,' . $read;
            }
        }
        foreach($nodeList as $key => $value){
            $replace = 'url(' . $value . ');';
            $convert = str_replace($key, $replace, $convert);
        }
        return $convert;
    }

    private function convertHtml($convert=''){
        $nodeList = array();
        $tmp = explode('<img', $convert);
        array_shift($tmp);
        foreach ($tmp as $nr =>  $part){
            $array = $this->explode_multi(array(">"), $part);
            $array = explode('src=', reset($array));
            array_shift($array);
            $array = explode('"', trim(reset($array),'"'));
            $value = reset($array);
            $key = $value; //'"' . $value . '"';
            $nodeList[$key] = $value;
        }
        foreach($nodeList as $key => $value){
            $url = $this->data('dir.vendor') . $this->handler()->removeHost($value);
            if(file_exists($url)){
                $file = new File();
                $read = base64_encode($file->read($url));
                $nodeList[$key] = 'data:image/jpg;base64,' . $read;
            }
        }
        foreach($nodeList as $key => $value){
            $replace = $value;
            $convert = str_replace($key, $replace, $convert);
        }
        return $convert;
    }
}
