<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Core;

use Priya\Module\Core;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\Handler;
use stdClass;

class Data extends Core {
    const DIR = __DIR__;

    private $url;
    private $data;

    private $object_data;

    public function __construct($handler=null, $route=null, $data=null){
        if(stristr(get_class($handler), 'autoload') !== false){
            $this->autoload($handler);
            parent::__construct(null, $route);
        } else {
            parent::__construct($handler, $route);
        }
        $this->data($this->object_merge($this->data(), $data));
    }

    public function object_data($object_data=null){
        if($object_data!== null){
            $this->set_object_data($object_data);
        }
        $object_data = $this->get_object_data();
        if($object_data === null){
            $this->set_object_data(new \Priya\Module\Data());
        }
        return $this->get_object_data();
    }

    private function set_object_data($object_data=''){
        $this->object_data= $object_data;
    }

    private function get_object_data(){
        return $this->object_data;
    }

    public function data($attribute=null, $value=null){
        if($attribute == 'object'){
            return $this->object_data();
        } else {
            return $this->object_data()->data($attribute, $value);
        }
    }

    public function url($url=null, $attribute=null){
        return $this->object_data()->url($url, $attribute);
    }

    public function read($url=''){
        return $this->object_data()->read($url);
    }

    public function write($url=''){
        return $this->object_data()->write($url);
    }

    public function node($attribute='', $node='', $merge=false){
        return $this->object_data()->node($attribute, $node, $merge);
    }

    public function jid($list=''){
        return $this->object_data()->jid($list);
    }

    public function copy($copy=null){
        return $this->object_data()->copy($copy);
    }

    /*
    public function search($list, $find, $attribute=null, $case=false, $not=false){
        $useData = true;
        $output = 'array';
        if(is_string($list)){
            $data = $this->data($list);
        } else {
            $useData = false;
            $data = $list;
        }
        if(!is_array($data)){
            $output = 'object';
        }
        $result = array();
        if(!is_array($attribute) && !is_null($attribute)){
            $attribute = explode(',', $attribute);
        }
        if(is_array($data) || is_object($data)){
            foreach($data as $key => $node){
                $search = '';
                if(is_null($attribute)){
                    if(is_array($node)){
                        $node = $this->object($node);
                    }
                    foreach($node as $attr => $value){
                        if(is_array($value)){
                            continue;
                        }
                        elseif(is_object($value)){
                            continue;
                        }
                        $search .= $value . ' ';
                    }
                }
                elseif(is_array($attribute)){
                    if(is_array($node)){
                        $node = $this->object($node);
                    }
                    foreach($attribute as $value){
                        $selector = trim($value);
                        $select = $this->object_get($selector, $node);
                        $search .= $select . ' ';
                    }
                }
                if(empty($case)){
                    $search = strtolower($search);
                    $find = strtolower($find);
                }
                $search = trim($search);
                $find = trim($find);
                $levenshtein = levenshtein(substr($search, 0, 255), substr($find, 0, 255), 5, 2, 5);
                if(!empty($not)){
                    if(strstr($search, $find) === false){
                        $result[$levenshtein][$key] = $node;
                    }
                } else {
                    if(strstr($search, $find) !== false){
                        $result[$levenshtein][$key] = $node;
                    }
                }
            }
        }
        $data = array();
        $sort = SORT_NATURAL;
        if(!empty($not) == 'desc'){
            krsort($result, $sort);
        } else {
            ksort($result, $sort);
        }
        foreach($result as $levenshtein => $subList){
            foreach($subList as $key => $node){
                $data[$key] = $node;
            }
        }
        if(empty($useData)){
            return $this->object($data, $output);
        } else {
            $this->data('delete', $list);
            return $this->data($list, $this->object($data, $output));
        }
    }

    public function sort($list, $attribute='sort', $order='ASC', $sort=null, $case=false){
        $useData = true;
        $output = 'array';
        if(is_string($list)){
            $data = $this->data($list);
        } else {
            $useData = false;
            $data = $list;
        }
        if(!is_array($data)){
            $output = 'object';
        }
        $result = array();
        if(!is_array($attribute)){
            $attribute = explode(',', $attribute);
        }
        if(is_array($data) || is_object($data)){
            foreach($data as $key => $node){
                $sorter = '';
                if(is_array($attribute)){
                    if(is_array($node)){
                        $node = $this->object($node);
                    }
                    foreach($attribute as $value){
                        $selector = trim($value);
                        $select = $this->object_get($selector, $node);
                        $sorter .= $select;
                    }
                }
                if(empty($case)){
                    $sorter = strtolower($sorter);
                }
                $result[$sorter][$key] = $node;
            }
        }
        if($sort === null){
            $sort = SORT_NATURAL;
        }
        if(strtolower($order) == 'desc'){
            krsort($result, $sort);
        } else {
            ksort($result, $sort);
        }
        $data = array();
        foreach($result as $sorter => $subList){
            foreach($subList as $key => $node){
                $data[$key] = $node;
            }
        }
        if(empty($useData)){
            $res = $this->object($data, $output);
            return $res;
        } else {
            $this->data('delete', $list);
            return $this->data($list, $this->object($data, $output));
        }
    }

    public function filter($list='', $attribute=array(), $values=array(), $action='keep'){
        $useData = true;
        $output = 'array';
        if(is_string($list)){
            $data = $this->data($list);
        } else {
            $useData = false;
            $data = $list;
        }
        if(!is_array($data)){
            $output = 'object';
        }
        $result = array();
        $remove = array();
        if(!is_array($attribute)){
            $attribute = explode(',', $attribute);
        }
        if(is_object($values)){
            $values = $this->object($values, 'array');
            var_dump($values);
            die;
        }
        elseif(!is_array($values)){
            $values = explode(',', $values);
        }
        if(is_array($data) || is_object($data)){
            foreach($data as $key => $node){
                if(is_array($node)){
                    $node = $this->object($node);
                }
                if(is_array($attribute)){
                    foreach($attribute as $value){
                        $selector = trim($value);
                        $select = $this->object_get($selector, $node);
                        if($action == 'remove'){
                            if(!empty($select)){
                                $remove[$key] = true;
                            }
                            $result[$key] = $node;
                        }
                        elseif($action == 'keep' && !empty($select)){
                            if(empty($values)){
                                $result[$key] = $node;
                            } else {
                                if(is_array($select)){
                                    foreach($values as $val){
                                        if(in_array($val, $select)){
                                            $result[$key] = $node;
                                            break;
                                        }
                                    }
                                } else {
                                    foreach($values as $val){
                                        if($val == $select){
                                            $result[$key] = $node;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(!empty($remove)){
            foreach ($remove as $key => $true){
                unset($result[$key]);
            }
        }
        if(empty($useData)){
            return $this->object($result, $output);
        } else {
            $this->data('delete', $list);
            return $this->data($list, $this->object($result, $output));
        }
    }

    public function count($nodeList='', $attribute='', $value='', $value_attribute='', $count='count'){
        if(empty($nodeList)){
            return;
        }
        if(empty($attribute)){
            return;
        }
        foreach($nodeList as $node){
            if(!isset($node->{$attribute})){
                continue;
            }
            if(is_array($node->{$attribute})){
                foreach ($node->{$attribute} as $attribute_value){
                    if(is_object($value)){
                        foreach($value as $object){
                            if($object->{$value_attribute} == $attribute_value){
                                if(!isset($object->{$count})){
                                    $object->{$count} =1;
                                } else {
                                    $object->{$count}++;
                                }
                            }
                        }
                    }
                }
            } else {
                if(is_object($value)){
                    foreach($value as $object){
                        if($object->{$value_attribute} == $node->{$attribute}){
                            if(!isset($object->{$count})){
                                $object->{$count} =1;
                            } else {
                                $object->{$count}++;
                            }
                        }
                    }
                }

            }
        }
    }

    public function parent($list='', $attribute='', $value='', $children='nodeList', $parent=null){
        if(is_object($list)){
            foreach($list as $key => $node){
                if(isset($node->{$attribute}) && $node->{$attribute} == $value){
                    if(empty($parent)){
                        return null;
                    } else {
                        return $parent;
                    }
                }
                if(!empty($node->{$children})){
                    $node_parent = $this->parent($node->{$children}, $attribute, $value, $children, $node);
                    if(!empty($node_parent)){
                        return $node_parent;
                    }
                }
            }
        }
        return false;
    }

    public function recursive($list='', $attribute='', $value='', $children='nodeList'){
        if(is_object($list)){
            foreach($list as $key => $node){
                if(isset($node->{$attribute}) && $node->{$attribute} == $value){
                    return $node;
                }
                if(!empty($node->{$children})){
                    $recursive = $this->recursive($node->{$children}, $attribute, $value, $children);
                    if(!empty($recursive)){
                        return $recursive;
                    }
                }
            }
        }
        return false;
    }

    public function recursive_sort($list='', $attribute='sort', $order='ASC', $children='nodeList', $sort=null, $case=false){
        $nodeList = $this->sort($list, $attribute, $order, $sort, $case);
        foreach($nodeList as $key => $node){
            if(is_object($node) && !empty($node->{$children})){
                $node->{$children} = $this->recursive_sort($node->{$children}, $attribute, $order, $children, $sort, $case);
            }
        }
        return $nodeList;
    }

    public function recursive_delete($list='', $attribute='', $value='', $children='nodeList'){
        if(is_object($list)){
            foreach($list as $key => $node){
                if($node->{$attribute} == $value){
                    unset($list->{$key});
                }
                if(isset($node->{$children})){
                    $this->recursive_delete($node->{$children}, $attribute, $value, $children);
                }
            }
        }
    }

    public function flatten($list='', $children='nodeList'){
        if(is_object($list)){
            foreach($list as $key => $node){
                if(isset($node->{$children})){
                    $nodeList = $this->flatten($node->{$children}, $children);
                    if(is_object($nodeList)){
                        foreach($nodeList as $child_key => $child){
                            $list->$child_key = $child;
                        }
                    }
                }
            }
        }
        return $list;
    }
    */
    /*
    public function node($attribute='', $node='', $merge=false){
        $url = $this->url();
        if(empty($url)){
            return false;
        }
        if(empty($attribute)){
            return false;
        }
        if(empty($node)){
            return false;
        }
        $nodeList = $this->data($attribute);
        if(empty($node->jid)){
            $node->jid = $this->jid($attribute);
            if(empty($nodeList)){
                $nodeList = new stdClass();
            }
            $nodeList->{$node->jid} = $node;
            $this->data($attribute, $nodeList);
            $this->write($url);
            return $node->jid;
        } else {
            $update = false;
            if(is_array($nodeList) || is_object($nodeList)){
                foreach($nodeList as $jid => $item){
                    if($jid == $node->jid){
                        $update = true;
                        if(empty($merge)){
                            $nodeList->{$jid} = $node;
                        } else {
                            $nodeList->{$jid} = $this->object_merge($item, $node);
                        }
                        break;
                    }
                }
            }
            if(empty($update)){
                return false;
            }
        }
        $this->data($attribute, $nodeList);
        $this->write($url);
        return $node->jid;
    }

    public function jid($list=''){
        if(is_array($list) || is_object($list)){
            $data = $list;
        } else {
            $data = $this->data($list);
        }
        $number = 1;
        if(empty($data)){
            return '1';
        } else {
            $tmpList = array_keys($this->object($data, 'array'));
            rsort($tmpList);
            foreach($tmpList as $nr => $jid){
                if(is_numeric($jid) && intval($jid) >= $number){
                    $number = intval($jid)+1;
                    break;
                }
            }
            return strval($number);
        }
    }

    public function copy($copy=null){
        return unserialize(serialize($copy));
    }

    public function request($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler($this->data()));
        }
        return parent::request($attribute, $value);
    }

    public function session($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler($this->data()));
        }
        return parent::session($attribute, $value);
    }
    */
}