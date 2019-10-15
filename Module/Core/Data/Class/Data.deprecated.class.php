<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace Priya\Module\Core;
use Priya\Module\Core;
use Priya\Module\Handler;
class Data extends Core {
    const DIR = __DIR__;
    private $object_data;
    public function __construct($handler=null, $route=null, $data=null){
        if($handler !== null && stristr(get_class($handler), 'autoload') !== false){
            $this->autoload($handler);
            parent::__construct(null, $route);
        } else {
            parent::__construct($handler, $route);
        }
        $this->data(Data::object_merge($this->data(), $data));
    }
    public function object_data($object_data=null){
        if($object_data !== null){
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
    public function read($url=''){
        return $this->data('object')->read($url);
    }
    public function write($url=''){
        return $this->data('object')->write($url);
    }
    public function url($url=null, $attribute=null){
        return $this->data('object')->url($url, $attribute);
    }
}