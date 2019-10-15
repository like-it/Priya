<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;
use stdClass;
use Exception;

class Sort {
    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_ASCENDING = 'ASC';
    const ATTRIBUTE_DESCENDING = 'DESC';

    public static function regular(){
        $order = strtoupper(substr($order, 0, 3));
        $prepare = Sort::prepare($list, $attribute, $ignore_case);
        $flag = SORT_REGULAR;
        if($order == 'ASC'){
            ksort($prepare, $flag);
        } else {
            krsort($prepare, $flag);
        }
        $list = [];
        foreach($prepare as $subList){
            foreach($subList as $record){
                $list[] = $record;
            }
        }
        return $list;
    }

    public static function create_key($record=[], $attribute=[], $ignore_case=true, $is_array=false){
        $key = '';
        foreach($attribute as $nr => $property){
            if($is_array === true){
                if(array_key_exists($property, $record)){
                    $key .= $record[$property];
                }
                //do array thing
            } else {
                if(property_exists($record, $property)){
                    $key .= $record->{$property};
                }
            }
        }
        if($ignore_case === true){
            $key = strtolower($key);
        }
        return $key;
    }

    private static function prepare($list=[], $attribute='name', $ignore_case=true){
        if(is_string($attribute)){
            $sort = [];
            $sort[] = $attribute;
        } else {
            $sort = $attribute;
        }


        if(!is_array($list)){
            throw new Exception('$list needs to be an array, maybe add object of stdClass');
        }
        $is_array = false;
        $result = [];
        foreach($list as $key => $record){
            if(
                $is_array === false &&
                is_array($record)){
                    $is_array = true;
            }
            if($is_array === true){
                if(array_key_exists($attribute, $record)){
                    $sort_key = Sort::create_key($record, $sort, $ignore_case, true);
                    $result[$sort_key][] = $record;
                }
            } else {
                if(property_exists($record, $attribute)){
                    $sort_key = Sort::create_key($record, $sort, $ignore_case);
                    $result[$sort_key][] = $record;
                }
            }
        }
        return $result;
    }

    public static function natural($list=[], $attribute='name', $order='ASC', $ignore_case=true){
        if(!is_array($list)){
            return [];
        }
        $order = strtoupper(substr($order, 0, 3));
        $prepare = Sort::prepare($list, $attribute, $ignore_case);
        $flag = SORT_NATURAL;
        if($order == 'ASC'){
            ksort($prepare, $flag);
        } else {
            krsort($prepare, $flag);
        }
        $list = [];
        foreach($prepare as $subList){
            foreach($subList as $record){
                $list[] = $record;
            }
        }
        return $list;
    }

    public static function string($list=[], $attribute='name', $order='ASC', $ignore_case=true){
        $order = strtoupper(substr($order, 0, 3));
        $prepare = Sort::prepare($list, $attribute, $ignore_case);
        $flag = SORT_STRING;
        if($order == 'ASC'){
            ksort($prepare, $flag);
        } else {
            krsort($prepare, $flag);
        }
        $list = [];
        foreach($prepare as $subList){
            foreach($subList as $record){
                $list[] = $record;
            }
        }
        return $list;
    }

    public static function numeric($list=[], $attribute='name', $order='ASC', $ignore_case=true){
        if(!is_array($list)){
            return [];
        }
        $order = strtoupper(substr($order, 0, 3));
        $prepare = Sort::prepare($list, $attribute, $ignore_case);
        $flag = SORT_NUMERIC;
        if($order == 'ASC'){
            ksort($prepare, $flag);
        } else {
            krsort($prepare, $flag);
        }
        $list = [];
        foreach($prepare as $subList){
            foreach($subList as $record){
                $list[] = $record;
            }
        }
        return $list;
    }

    public static function reverse($list=[]){
        rsort($list);
        return $list;
    }

}