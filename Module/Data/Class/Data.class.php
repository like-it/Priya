<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module;

use stdClass;
use Exception;
use Priya\Application;

class Data extends Core {
    const DIR = __DIR__;

    public $url;
    public $data;

    public function __construct($handler=null, $route=null, $data=null){
        $this->data(Data::object_merge($this->data(), $handler));
    }

    public static function is_empty($data = array()){
        $is_empty = true;
        foreach($data as $key => $item){
            $is_empty = false;
            break;
        }
        return $is_empty;
    }

    public function data($attribute=null, $value=null, $type=null){
        if($attribute !== null){
            if($attribute == 'set'){
                $this->object_delete($value, $this->data()); //for sorting an object
                $this->object_set($value, $type, $this->data());
                return $this->object_get($value, $this->data());
            }
            elseif($attribute == 'get'){
                return $this->object_get($value, $this->data());
            }
            if($value !== null){
                if($attribute=='delete'){
                    return $this->deleteData($value);
                } else {
                    $this->object_delete($attribute, $this->data()); //for sorting an object
                    $this->object_set($attribute, $value, $this->data());
                    return $this->object_get($attribute, $this->data());
                }
            } else {
                if(is_string($attribute)){
                    return $this->object_get($attribute, $this->data());
                } else {
                    $this->setData($attribute);
                    return $this->getData();
                }
            }
        }
        return $this->getData();
    }

    private function setData($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->data)){
                foreach($attribute as $key => $value){
                    $this->data->{$key} = $value;
                }
            }
            elseif(is_array($this->data)){
                foreach($attribute as $key => $value){
                    $this->data[$key] = $value;
                }
            } else {
                $this->data = $attribute;
            }
        } else {
            if(is_object($this->data)){
                $this->data->{$attribute} = $value;
            }
            elseif(is_array($this->data)) {
                $this->data[$attribute] = $value;
            }
        }
    }

    private function getData($attribute=null){
        if($attribute === null){
            if(is_null($this->data)){
                $this->data = new stdClass();
            }
            return $this->data;
        }
        if(isset($this->data[$attribute])){
            return $this->data[$attribute];
        } else {
            return false;
        }
    }

    private function deleteData($attribute=null){
        return $this->object_delete($attribute, $this->data());
    }

    public function url($url=null, $attribute=null){
        if($url !== null){
            if($attribute !== null){
                switch($url){
                    case 'encode':
                        return $this->encodeUrl($attribute);
                    break;
                    case 'decode':
                        return $this->decodeUrl($attribute);
                    break;
                    default:
                        throw new Exception('unknown attribute (' . $url . ') in url');
                }
            } else {
                $this->setUrl($url);
            }
        }
        return $this->getUrl();
    }

    private function setUrl($url=''){
        $this->url = $url;
    }

    private function getUrl(){
        return $this->url;
    }

    private function encodeUrl($url=''){
        $temp = explode('/', $url);
        foreach($temp as $nr => $part){
            if($part == Handler::SCHEME_HTTP . ':'){
                continue;
            }
            if($part == Handler::SCHEME_HTTPS . ':'){
                continue;
            }
            $temp[$nr] = rawurlencode($part);
        }
        $url = implode('/', $temp);
        return $url;
    }

    private function decodeUrl($url=''){
        $temp = explode('/', $url);
        foreach($temp as $nr => $part){
            if($part == Handler::SCHEME_HTTP . ':'){
                continue;
            }
            if($part == Handler::SCHEME_HTTPS . ':'){
                continue;
            }
            $temp[$nr] = rawurldecode($part);
        }
        $url = implode('/', $temp);
        return $url;
    }

    public function read($url=''){
        $namespace = '';
        if(empty($url)){
            $url = get_called_class();
        }
        if(file_exists($url)){
            $file = new File();
            $read = $file->read($this->url($url));
            $read = $this->object($read);
            $data = $this->data();
            if(empty($data)){
                $data = new stdClass();
            }
            if(!empty($read)){
                if(is_array($read) || is_object($read)){
                    foreach($read as $attribute => $value){
                        $this->object_set($attribute, $value, $data);
                    }
                }
            }
            return $this->data($data);
        } else {
            $module = $url;
        }
        $autoload = $this->autoload();
        if(empty($autoload) || !$autoload instanceof \Priya\Module\Autoload\Data){
            $tmp = explode('\\', trim(str_replace(Application::DS, '\\',$url),'\\'));
            $class = array_pop($tmp);
            $namespace = implode('\\', $tmp);
            $directory = explode(Application::DS, Application::DIR);
            array_pop($directory);
            array_pop($directory);
            $priya = array_pop($directory);
            $directory = implode(Application::DS, $directory) . Application::DS;
            if(empty($namespace)){
                $namespace = $priya . '\\' . Application::MODULE;
            }
            $directory .= str_replace('\\', Application::DS, $namespace) . Application::DS;
            $data = new \Priya\Module\Autoload\Data();
            $class = get_called_class();
            if($class::DIR){
                $dir = dirname($class::DIR) . Application::DS;
                $data->addPrefix('none', $dir);
            }
            $data->addPrefix($namespace, $directory);
            $autoload = $this->data('priya.autoload');
            if(empty($autoload)){
                $autoload = $this->data('autoload');
            }
            if(is_object($autoload)){
                foreach($autoload as $prefix => $directory){
                    $data->addPrefix($prefix, $directory);
                }
            }
            $autoload = $this->autoload($data);
        }
        $url = $autoload->data_load($url);
        if($url !== false){
            $this->url($url);
        }

        $file = new File();
        $read = $file->read($url);
        if($read !== false){
            $read = $this->object($read);
        }
        $data = $this->data();
        if(empty($data)){
            $data = new stdClass();
        }

        if(!empty($read)){
            foreach($read as $attribute => $value){
                $this->object_set($attribute, $value, $data);
            }
        } else {
            return false;
        }
        return $this->data($data);
    }

    public function write($url=''){
        if(!empty($url)){
            $this->url($url);
        }
        $url = $this->url();
        if(empty($url)){
            return false;
        }
        $file = new File();
        $write = $file->write($url, $this->object($this->data(), 'json'));
        return $write;
    }

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
            return $data;
        } else {
            $this->data('delete', $list);
            return $this->data($list, $data);
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
                            $nodeList->{$jid} = Data::object_merge($item, $node);
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

}