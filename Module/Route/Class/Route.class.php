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
    const LOCAL = 'local';

    const EXCEPTION_ROUTE_CURRUPT = 'Route file corrupted? {$name}';
    const EXCEPTION_ROUTE_DUPLICATE = 'Duplicate route found: {$name}';

    private $storage;
    private $item;

    public function __construct(Handler $handler, $data='', $read=true){
        $this->handler($handler);
        $this->storage(new Data($data));
        if($read === true){
            $this->storage()->data('priya.cache.file', []);
            $data = new Data();
            $url = $this->storage()->data('priya.route.url');            
            if(empty($url)){
                $url == $this->storage()->data('priya.dir.data') . Application::ROUTE;
            }
            $read = $data->read($url);
            if(empty($read)){
                $this->error('read', true);
            } else {
                $this->data('route', $read);
            }
            $this->parseRoute();
        }
        $list = $this->storage()->data('priya.route.default');
        if(is_array($list)){
            foreach($list as $default){
                $this->create($default);
            }
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
        $data = $this->data('route');
        if(empty($path)){
            $path = trim($handler->request('request'), '/') . '/';
        }
        if(empty($data)){
            throw new Exception($this->parser('object')->compile(Route::EXCEPTION_ROUTE_CURRUPT, $this->storage()->data()));
        }        
        $parser = false;
        foreach($data as $name => $route){
            if(isset($route->resource) && !isset($route->read)){
                if(!$parser){
                    $parser = new Parser();
                }
                $route->resource = $parser->compile($route->resource, $this->storage()->data());
                if(file_exists($route->resource)){
                    $object = new Data();
                    $this->data('route', Data::object_merge($this->data('route'), $object->read($route->resource)));
                    $route->read = true;
                    $route->mtime = filemtime($route->resource);
                    $this->cache(clone $route);
                    return $this->parseRequest($path);
                } else {
                    $route->read = false;
                    $this->cache(clone $route);
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
                $route->host = (array) $route->host;

                $match = false;
                $real_host = Handler::host(false);
                $skip = Route::skip($route->host);
                foreach($route->host as $host){
                    if(substr($host,0, 1) == '!'){
                        continue;
                    }
                    if(in_array($real_host, $skip)){
                        continue;
                    }
                    $subdomain = Handler::subdomain($host);
                    if(isset($subdomain) && $subdomain == '*'){
                        $real_host = Handler::domain($real_host) . '.' . Handler::extension($real_host);
                        $host = Handler::domain($host) . '.' . Handler::extension($host);
                    }
                    if($host == $real_host){
                        $match = true;
                        break;
                    }
                    $explode = explode('.', $host);
                    array_pop($explode);
                    $explode[] = Route::LOCAL;
                    $local = implode('.', $explode);
                    if($local == $real_host){
                        $match = true;
                        break;
                    }
                }
                if(empty($match)){
                    continue;
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
                    $route->method = Core::object($route->method, 'array');
                }
                foreach($route->method as $key => $method){
                    $route->method[$key] = strtoupper($method);
                }
                $contentType = $this->handler()->contentType();
                if($contentType == Handler::CONTENT_TYPE_CLI && !in_array('CLI', $route->method)){
                    continue; //skip based on wrong content
                }
                if($contentType !== Handler::CONTENT_TYPE_CLI){
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
                var_dump('yep');
                die;
                $read =
                    $this->data('priya.route.dir.host') .
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
                /**
                 * @deprecated since 2018-05-11
                 */
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

    private static function skip($host=array()){
        $skip = array();
        foreach($host as $name){
            if(substr($name, 0, 1) == '!'){
                $name = substr($name, 1);
                $skip[] = $name;
                $explode = explode('.', $name);
                array_pop($explode);
                $explode[] = Route::LOCAL;
                $skip[] = implode('.', $explode);
            }
        }
        return $skip;
    }

    private function parseRoute(){
        $data = $this->data('route');
        $parser = false;
        if(is_array($data) || is_object($data)){
            foreach($data as $name => $route){
                if(isset($route->resource) && !isset($route->read)){
                    if(!$parser){
                        $parser = new Parser();
                    }
                    $route->resource = $parser->compile($route->resource, $this->storage()->data());
                    if(file_exists($route->resource)){
                        $object = new Data();
                        $object->read($route->resource);
                        foreach($this->data('route') as $test_name => $test_route){
                            if(!empty($object->data($test_name))){
                                $this->storage()->data('name', $test_name);
                                throw new Exception($this->parser('object')->compile(Route::EXCEPTION_ROUTE_DUPLICATE, $this->storage()->data()));
                            }
                        }
                        $merge = Data::object_merge($this->data('route'), $object->data());
                        $this->data('route', $merge);
                        $route->read = true;
                        $route->mtime = filemtime($route->resource);

                        $this->cache(clone $route);
                        $this->data('delete', 'route.' . $name);
                        $this->parseRoute();
                    } else {
                        $route->read = false;
                        $this->cache(clone $route, $name);
                        $this->data('delete', 'route.' . $name);
                    }
                }
            }
        }
    }

    private function cache($cache=''){
        $file = $this->data('priya.cache.file');
        if(empty($file)){
            $file = array();
        }
        $cache->url = $cache->resource;
        unset($cache->resource);

        $file[] = $cache;
        $this->data('priya.cache.file', $file);
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

    public function create($name='', $module='Cli', $method='run'){
        $this->createRoute($name, $module, $method);
    }

    private function createRoute($name, $module='Cli', $method='run'){
        $name = $this->explode_multi(array(':', '.', '/', '\\'), trim($name, '.:/\\'));
        $object = new stdClass();
        $object->path = implode('/', $name) . '/';
        if(empty($module)){
            $object->controller = 'Priya.Module.' . implode('.', $name) . '.' .  $method;
        } else {
            $object->controller = 'Priya.Module.' . $module . '.'. implode('.', $name) . '.' .  $method;
        }

        $object->method = array('CLI');
        $object->translate = false;
        $this->data('route.' . strtolower(implode('-',$name)), $object);
        if(count($name) > 1){
            $object = $this->copy($object);
            array_shift($name);
            $object->path = implode('/', $name) . '/';
            $this->data('route.' . strtolower(implode('-',$name) . '-shorthand'), $object);
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
        $data = $this->data('route');
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
            $path = implode('/', $route_path) . '/';       //added '/' for Doxygen url

            if(strpos($path, Handler::SCHEME_HTTP) !== 0){
                $path = $this->data('priya.root.web.root') . $path;
            }
            return $path;
        }
    }

    public function storage($storage=null){
        if($storage !== null){
            $this->setStorage($storage);
        }
        return $this->getStorage();
    }

    private function setStorage($storage=''){
        $this->storage = $storage;
    }

    private function getStorage(){
        return $this->storage;
    }
}
?>