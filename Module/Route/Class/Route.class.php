<?php

namespace Priya\Module;

use Priya\Application;
use Priya\Module\Core\Parser;
use Priya\Module\Core\Data;
use stdClass;

class Route extends Parser{
    const DIR = __DIR__;
    private $item;

    public function __construct(Handler $handler, $data=''){
        $this->handler($handler);
        $this->data($data);

        $data = new Data();
        $read = $data->read($this->data('dir.data') . Application::ROUTE);
        if(empty($read)){
            $this->error('read', true);
        } else {
            $this->data($read);
        }
        $this->parseRoute();
    }

    public function run($path=''){
        return $this->parseRequest($path);
    }

    public function parseRequest($path=''){
        $handler = $this->handler();
        $data = $this->data();
        if(empty($path)){
            $path = trim($handler->request('request'), '/') . '/';
        }
        foreach($data as $name => $route){
            if(isset($route->resource) && !isset($route->read)){
                $route->resource = $this->compile($route->resource, $this->data());
                if(file_exists($route->resource)){
                    $object = new Data();
                    $this->data($object->read($route->resource));
                    $route->read = true;
                    return $this->parseRequest($path);
                } else {
                    $route->read = false;
                }
            }
        }
        $path = explode('/', trim($path, '/'));

        foreach($data as $name => $route){
            if(!isset($route->path)){
                continue;
            }
            $node = $this->parsePath($path, $route);
            if(empty($node)){
                continue;
            }
            if(isset($route->method)){
                if(!is_array($route->method)){
                    $route->method = (array) $route->method;
                }
                foreach($route->method as $key => $method){
                    $route->method[$key] = strtoupper($method);
                }
                $contentType = $this->handler()->contentType();
                if($contentType == handler::CONTENT_TYPE_CLI && !in_array('CLI', $route->method)){
                    continue; //skip based on wrong content
                }
                if($contentType !== handler::CONTENT_TYPE_CLI){
                    $method = $this->handler()->method();
                    if(!in_array($method, $route->method)){
                        continue; //skip based on wrong method
                    }
                }
            }
            if(isset($route->default) && isset($route->default->controller)){
                $controller = '\\' . trim(str_replace(array(':', '.'), array('\\','\\'), $route->default->controller), ':\\');
                $tmp = explode('\\', $controller);
                $object = new stdClass();
                $object->function = array_pop($tmp);
                $object->controller = implode('\\', $tmp);
                return $this->item($object);
            }
        }
        $this->error('route', true);
    }

    private function parseRoute(){
        $data = $this->data();
        if(is_array($data) || is_object($data)){
            foreach($data as $name => $route){
                if(isset($route->resource) && !isset($route->read)){
                    $route->resource = $this->compile($route->resource, $this->data());
                    if(file_exists($route->resource)){
                        $object = new Data();
                        $this->data($object->read($route->resource));
                        $route->read = true;
                        $this->parseRoute();
                    } else {
                        $route->read = false;
                    }
                }
            }
        }
    }

    private function parsePath($path='', $route=''){
        $found = true;
        $route_path = explode('/', trim(strtolower($route->path), '/'));
        $attributeList = array();
        $valueList = $path;
        foreach($route_path as $part_nr => $part){
            if(substr($part,0,1) == '{' && substr($part,-1) == '}'){
                $attributeList[$part_nr] = $part;
                continue;
            }
            if(!isset($path[$part_nr])){
                $found = false;
                break;
            }
            if($part != $path[$part_nr]){
                $found = false;
                break;
            }
            unset($route_path[$part_nr]);
            unset($valueList[$part_nr]);
        }
        if(empty($found)){
            return false;
        }
        if(!empty($valueList) && empty($attributeList)){
            return false;
        }
        if(!empty($attributeList)){
            $itemList = array();
            $counter = 0;
            $count = count($attributeList);
            foreach($attributeList as $attribute_nr => $attribute){
                if(isset($valueList[$attribute_nr])){
                    if($counter == $count -1){
                        $value = implode('/', $valueList);
                    } else {
                        $value = $valueList[$attribute_nr];
                    }
                    $record = $this->parseAttributeList($attribute, $value);
                    unset($valueList[$attribute_nr]);
                    foreach($record as $record_nr => $item){
                        $itemList[] = $item;
                    }
                }
                $counter++;

            }
            /*
            foreach($attributeList as $attribute_nr => $attribute){
                if(isset($valueList[$attribute_nr])){
                    $record = $this->parseAttributeList($attribute, $valueList[$attribute_nr]);
                    foreach($record as $record_nr => $item){
                        $itemList[] = $item;
                    }
                }
            }
            */
            foreach($itemList as $request){
                if(isset($request->name) && isset($request->value)){
                    $this->request($request->name, $request->value);
                }
            }
        }
        return $route;
    }

    public function item($item=null){
        if($item !== null){
            $this->setItem($item);
        }
        return $this->getItem();
    }

    private function setItem($item=''){
        $this->item = $item;
    }

    private function getItem(){
        return $this->item;
    }

    public function create($name=''){
        $this->createRoute($name);
    }

    private function createRoute($name, $module='Cli', $method='run'){
        $name = $this->explode_multi(array(':', '.', '/', '\\'), trim($name, '.:/\\'));
        $object = new stdClass();
        $object->path = implode('/', $name) . '/';
        $object->default = new stdClass();
        $object->default->controller = 'Priya:Module:' . $module . ':'. implode(':', $name) . ':' .  $method;
        $object->method = array('CLI');
        $object->translate = false;
        $this->data(strtolower(implode('-',$name)) . '/', $object);
        $object = $this->copy($object);
        $object->path = end($name);
        $this->data(strtolower(implode('-',$name) . '-shorthand') . '/', $object);
    }

    public function parseAttributeList($attribute='', $value=''){
        if(empty($attribute)){
            return array();
        }
        $attributeList = array();
        $list = explode('{', $attribute);
        foreach($list as $list_nr => $record){
            $tmp = explode('}', $record);
            $tmpAttribute = ltrim(array_shift($tmp), '$');
            if(empty($tmpAttribute)){
                continue;
            }
            $rest = implode('}', $tmp);
            if(empty($rest)){
                $record = new stdClass();
                $record->name = $tmpAttribute;
                $record->value = $value;
                $attributeList[] = $record;
                continue;
            }
            $valueList = explode($rest,$value);
            $record = new stdClass();
            $record->name = $tmpAttribute;
            $record->value = array_shift($valueList);
            $attributeList[] = $record;
            $value = implode($rest, $valueList);
        }
        return $attributeList;
    }

    public function route($name='', $attribute=array()){
        if(!is_array($attribute)){
            $attribute = (array) $attribute;
        }
        $found = false;
        $data = $this->data();

        foreach($data as $routeName => $route){
            if(!isset($route->path)){
                continue;
            }
            if(strtolower($name) == strtolower($routeName)){
                $found = $route;
                break;
            }
        }
        if(empty($found)){
//             trigger_error('Route not found for ('. $name.')');
        } else {
            $route_path = explode('/', trim(strtolower($route->path), '/'));
            foreach($route_path as $part_nr => $part){
                if(substr($part,0,1) == '{' && substr($part,-1) == '}'){
                    $route_path[$part_nr] = array_shift($attribute);
                }
                if(empty($route_path[$part_nr])){
                    unset($route_path[$part_nr]);
                }
            }
            $path = implode('/', $route_path);
            if(!empty($path)){
                $path .= '/';
            }
            if(stristr($path, 'http') === false){
                $path = $this->data('web.root') . $path;
            }
            return $path;
        }
    }
}
?>