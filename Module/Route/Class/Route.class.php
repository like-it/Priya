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

class Route extends Core\Parser{
    const DIR = __DIR__;

    private $item;

    public function __construct(Handler $handler, $data='', $read=true){
        $this->handler($handler);
        $this->data($data);

        if($read){
            $data = new Data();
            $read = $data->read($this->data('dir.data') . Application::ROUTE);
            if(empty($read)){
                $this->error('read', true);
            } else {
                $this->data($read);
            }
            $this->parseRoute();
        }
    }

    public function run($path=''){
        $route = $this->parseRequest($path);
        if(empty($route)){
            $route = $this->parseRequest($path, false);
        }
        if(empty($route)){
            $this->error('route', true);
        }
        return $route;
    }

    public function parseRequest($path='', $isHost=true){
        $handler = $this->handler();
        $data = $this->data();
        if(empty($path)){
            $path = trim($handler->request('request'), '/') . '/';
        }
        foreach($data as $name => $route){
            if(isset($route->resource) && !isset($route->read)){
                $route->resource = $this->parser('object')->compile($route->resource, $this->data());
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
            if($isHost === false){
                if(isset($route->host)){
                    continue;
                }
            }
            if(isset($route->host)){
                $host = explode('.', $route->host);
                array_pop($host);
                $host[] = 'local';
                $host = implode('.', $host);
                $real_host = $this->handler()->host(false);
                if($route->host != $real_host){
                    if($host != $real_host){
                        continue;
                    }
                }
            }
            if($isHost && !isset($route->host)){
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
            if(
                isset($route->default) &&
                isset($route->default->read)
            ){
                $read =
                    $this->data('dir.host') .
                    $this->handler()->host(false) .
                    Application::DS .
                    $this->data('public_html') .
                    Application::DS .
                    $route->default->read
                ;
                $object = new stdClass();
                $object->url = $read;
                if(isset($route->format)){
                    $object->format = $route->format;
                }
                elseif(isset($route->default->format)){
                    $object->format = $route->default->format;
                } else {
                    $object->format = 'raw';
                }
                if(isset($route->language)){
                    $object->language = $route->language;
                }
                elseif(isset($route->default->language)){
                    $object->language = $route->default->language;
                }
                if(isset($route->translate)){
                    $object->translate = $route->translate;
                }
                return $this->item($object);
            }
            if(isset($route->controller)){
                $controller = '\\' . trim(str_replace(array(':', '.'), array('\\','\\'), $route->controller), ':\\');
                $tmp = explode('\\', $controller);
                $node->route = $node->controller;
                $node->function = array_pop($tmp);
                $node->controller = implode('\\', $tmp);
                $node->name = $name;
                if(isset($node->request) && is_object($node->request)){
                    foreach($node->request as $attribute => $value){
                        $this->request($attribute, $value);
                    }
                }
                $node->request = $this->request();
                return $this->item($node);
            }
            elseif(isset($route->default) && isset($route->default->controller)){
                $controller = '\\' . trim(str_replace(array(':', '.'), array('\\','\\'), $route->default->controller), ':\\');
                $tmp = explode('\\', $controller);
                $object = new stdClass();
                $object->function = array_pop($tmp);
                $object->controller = implode('\\', $tmp);
                $object->name = $name;
                $object->request = $this->request();
                return $this->item($object);
            } else {
                $route->name = $name;
                if(isset($node->request) && is_object($node->request)){
                    foreach($node->request as $attribute => $value){
                        $this->request($attribute, $value);
                    }
                }
                $route->request = $this->request();
                return $this->item($route);
            }
        }
    }

    private function parseRoute(){
        $data = $this->data();
        if(is_array($data) || is_object($data)){
            foreach($data as $name => $route){
                if(isset($route->resource) && !isset($route->read)){
                    $route->resource = $this->parser('object')->compile($route->resource, $this->data());
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
            if($part != strtolower($path[$part_nr])){
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
        $object->default->controller = 'Priya.Module:' . $module . '.'. implode('.', $name) . '.' .  $method;
        $object->method = array('CLI');
        $object->translate = false;
        $this->data(strtolower(implode('-',$name)), $object);
        if(count($name) > 1){
            $object = $this->copy($object);
            array_shift($name);
            $object->path = implode('/', $name) . '/';
            $this->data(strtolower(implode('-',$name) . '-shorthand'), $object);
        }
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
        if(empty($name)){
            return;
        }
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
//             throw new Exception('Route not found for (' . $name .')');
        } else {
            $route_path = explode('/', trim($route->path, '/'));
            foreach($route_path as $part_nr => $part){
                if(substr($part,0,1) == '{' && substr($part,-1) == '}'){
                    $route_path[$part_nr] = array_shift($attribute);
                }
                if(empty($route_path[$part_nr])){
                    unset($route_path[$part_nr]);
                }
            }
            $path = implode('/', $route_path);
            if(strpos($path, Handler::SCHEME_HTTP) !== 0){
                $path = $this->data('web.root') . $path;
            }
            return $path;
        }
    }
}
?>