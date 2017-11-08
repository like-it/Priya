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

class Parser extends Data {

    private $object_parser;

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
    }

    public function object_parser($object_parser=null){
        if($object_parser !== null){
            $this->set_object_parser($object_parser);
        }
        $object_parser= $this->get_object_parser();
        if($object_parser === null){
            $this->set_object_parser(
                new \Priya\Module\Parser(
                    $this->handler(),
                    $this->route()
                )
            );
            $this->get_object_parser()->data($this->data());
            $this->get_object_parser()->route($this->route());
            $this->get_object_parser()->handler($this->handler());
        }
        return $this->get_object_parser();
    }

    private function set_object_parser($object_parser=''){
        $this->object_parser = $object_parser;
    }

    private function get_object_parser(){
        return $this->object_parser;
    }

    public function parser($data=null, $value=null){
        if($data== 'object'){
            return $this->object_parser();
        } else {
            return $this->object_parser()->compile($data, $value);
        }
    }

    public function data($attribute=null, $value=null){
        if($attribute == 'object'){
            return $this->object_parser();
        } else {
            return $this->object_parser()->data($attribute, $value);
        }
    }

    public function read($url=''){
        return $this->parser('object')->read($url);
    }

    public function literal($node=''){
        return $this->parser('object')->literal($node);
    }
}