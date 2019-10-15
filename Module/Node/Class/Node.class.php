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
use Priya\Module\File\Dir;
use Priya\Module\File\Extension;

class Node extends stdClass{
    const MODEL = 'Model';
    const ATTRIBUTE_INPUT = 'input';
    const ATTRIBUTE_OUTPUT = 'output';

    public static function create($object, $attribute=[]){
        $attribute = (array) $attribute;
        if(isset($attribute['model'])){
            $attribute['model'] = (array) $attribute['model'];
        }
        if(empty($attribute['model']['url'])){
            throw new Exception('Attribute url missing');
        }
        if(empty($attribute['model']['key'])){
            throw new Exception('Attribute key missing');
        }
        if(!file::exist($attribute['model']['url'])){
            throw new Exception('File for model not found (' . $attribute['model']['url'] . ')');
        }
        $model = new Data();
        $model->read($attribute['model']['url']);
        $model = Node::filter(
            $object,
            $model,
            $attribute
        );
        //need rule
        $node = Node::select(
            $model,
            $attribute
        );
        return $node;
    }

    public static function backup($node, $attribute=[]){
        $attribute = (array) $attribute;
        if(isset($attribute['storage'])){
            $attribute['storage'] = (array) $attribute['storage'];
            if(isset($attribute['storage']['backup'])){
                $attribute['storage']['backup'] = (array) $attribute['storage']['backup'];
            } else {
                throw new Exception('Storage should have a backup url');
            }
            if(!isset($attribute['storage']['url'])){
                throw new Exception('Storage should have an url');
            }
            if(!isset($attribute['storage']['backup']['url'])){
                throw new Exception('Storage should have a backup url');
            }
        } else {
            throw new Exception('Storage should have an url and backup url');
        }
        if(File::exist($attribute['storage']['backup']['url']) === false){
            File::copy($attribute['storage']['url'], $attribute['storage']['backup']['url']);
        }
    }

    public static function write($object, $node, $url='', $list='nodeList'){
        if(property_exists($node, 'uuid') === false){
            throw new Exception('Node needs an uuid.');
        }
        $write = new Data();
        $write->read($url);
        $write->data($list . '.' . $node->uuid, $node);
        $bytes = $write->write($url);
        if($bytes > 0){
            return true;
        }
        throw new Exception('Failed to write to (' . $url .')');
        return false;
    }

    public static function select($model, $attribute=[]){
        if(!isset($attribute['model']['key'])){
            throw new Exception('attribute key is required, the name of the model');
        }
        $list = $model->data($attribute['model']['key']);
        if(empty($list)){
            throw new Exception('Model is empty and should be an object');
        }
        $result = new stdClass();
        foreach($list as $name => $node){
            if(property_exists($node, 'input') === false){
                d($node);
                throw new Exception('node input is empty and should be a string');
            }
            $result->{$name} = $node->input; //change to output after rule
        }
        return $result;
    }

    public static function filter($object, $model, $attribute=[]){
        if(!isset($attribute['model']['key'])){
            throw new Exception('attribute key is required, the name of the model');
        }
        if(empty($attribute['model']['prefix'])){
            throw new Exception('Prefix for request is empty, it should exists for grouping a node, default is node');
        }
        $list = $model->data($attribute['model']['key']);
        if(empty($list)){
            throw new Exception('Model is empty and should be an object');
        }
        $allowed = [];
        foreach($list as $name => $node){
            $allowed[] = $name;
        }
        foreach($allowed as $name){
            $model->data($attribute['model']['key'] . '.' . $name . '.' . Node::ATTRIBUTE_INPUT, $object->request($attribute['model']['prefix'] . '.' . $name));
        }
        return $model;
        //sort not allowed, it should be generated from top to bottom, move it by hand that's faster.
    }

}