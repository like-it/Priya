<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File;

use Priya\Module\File;
use stdClass;
use Exception;

class Csv {

    public static function read($url, stdClass $attribute=null){
        /*
        if(!File::exist($url)){
            throw new Exception('File (' . $url .') not exists');
        }
        if(!File::is($url)){
            throw new Exception('File (' . $url .') is no file');
        }
        */

        if(empty($attribute)){
            $attribute = new stdClass();
            $attribute->delimiter = null;
            $attribute->enclosure = null;
            $attribute->escape = null;
        }
        if(!property_exists($attribute, 'delimiter')){
            $attribute->delimiter = null;
        }
        if(!property_exists($attribute, 'enclosure')){
            $attribute->enclosure = null;
        }
        if(!property_exists($attribute, 'escape')){
            $attribute->escape = null;
        }
        $input = File::read($url);

        $list = [];
        $explode = explode("\n", $input);

        foreach($explode as $nr => $line){
            $line = trim($line, '\'",');
            $record = str_getcsv($line, $attribute->delimiter, $attribute->enclosure, $attribute->escape);
            $list[$nr] = $record;
        }
        return $list;
    }

    public static function createRecord($csv=[], $header=[]){
        $list = [];
        $skip = false;
        foreach($csv as $nr => $record){
            $item = [];
            foreach($header as $key => $name){
                if(!array_key_exists($key, $record)){
                    $skip = true;
                    break;
                }
                $item[$name] = $record[$key];
            }
            if(!empty($skip)){
                $skip = false;
                continue;
            }
            $list[] = $item;
        }
        return $list;
    }


}