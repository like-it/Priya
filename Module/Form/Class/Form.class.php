<?php
/**
 * @author              Remco van der Velde
 * @since               2019-08-25
 * @version             1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;
use stdClass;

use Priya\Module\Core\Result;
use Priya\Module\File\Extension;
use Priya\Module\File\Dir;
use Exception;

class Form {
    const DATA_UUID_NEW = 'New';

    public static function create($object, $attribute=[]){
        $attribute = (array) $attribute;

        if(isset($attribute['model'])){
            $attribute['model'] = (array) $attribute['model'];
        } else {
            throw new Exception('Model not defined in attribute');
        }
        if(isset($attribute['storage'])){
            $attribute['storage'] = (array) $attribute['storage'];
        } else {
            throw new Exception('Storage not defined in attribute');
        }
//         dd($attribute);
        /*
        if(!isset($attribute['model'])){
            throw new Exception('Model not defined in attribute-list');
        }
        */
        if(!isset($attribute['model']['key'])){
            throw new Exception('key not defined in model attribute-list');
        }
        if(!isset($attribute['model']['url'])){
            throw new Exception('Url not defined in model attribute-list');
        }
        if(File::exist($attribute['model']['url']) === false){
            throw new Exception('Model (' . $attribute['model']['url'] . ') not exists');
        }
        $data = new Data();
        $data->read($attribute['model']['url']);

        $list = $data->data($attribute['model']['key']);

        if(empty($list)){
            throw new Exception('Model (' . $attribute['model']['key'] . ') not exists');
        }
        if(!is_object($list)){
            throw new Exception('Model (' . $attribute['model']['key'] . ') is not a valid object');
        }
        if(!isset($attribute['storage'])){
            throw new Exception('Storage not defined in attribute-list');
        }
        if(!isset($attribute['storage']['url'])){
            throw new Exception('Storage url not defined in attribute-list');
        }
        if(!isset($attribute['storage']['key'])){
            throw new Exception('Storage nodeList not defined in attribute-list');
        }
        $node = Form::createNode($object, $attribute);


        $form = new stdClass();
        $form->header = Form::createHeader($object, $attribute);
        $form->body = '';
        $form->nodeList = new stdClass();
        $form->footer = '';
        foreach($list as $name => $record){
            if(property_exists($record, 'type') === false){
                throw new Exception('Need property type in record to create the field');
            }
            if(property_exists($form, $name)){
                throw new Exception('Property duplicate (' . $name .'), please rename a property');
            }
            if(property_exists($record, 'name') === false){
                $record->name = $name;
            }
            switch(strtolower($record->type)){
                case 'uuid' :
                    /**
                     * input hidden unique identifier
                     */
                    $form->nodeList->{$name} = Form::createUuid($object, $node, $record);
                break;
                case 'choice' :
                    /**
                     * select
                     */
                    $form->nodeList->{$name} = Form::createChoice($object, $node, $record);
                break;
                case 'text' :
                    /**
                     * input type = text
                     */
                    $form->nodeList->{$name} = Form::createText($object, $node, $record);
                break;
                default :
                    throw new Exception('Form type (' . strtolower($record->type) . ') not defined yet');
            }
        }
        $form = Form::createBody($object, $form, $attribute);
        $form = Form::createFooter($object, $form, $attribute);
        return $form;
    }

    private static function createNode($object, $attribute=[]){
        //move to node::read with attribute
        //create new node when uuid = new, get from model
        if(!isset($attribute['storage'])){
            throw new Exception('Storage not defined in attribute-list');
        }
        if(!isset($attribute['storage']['url'])){
            throw new Exception('Storage url not defined in attribute-list');
        }
        if(!isset($attribute['storage']['key'])){
            throw new Exception('Storage nodeList not defined in attribute-list');
        }
        if(!isset($attribute['uuid'])){
            throw new Exception('Uuid not defined in attribute-list');
        }
        dd($attribute);
        $storage = new Data();
        $storage->read($attribute['storage']['url']);
        $node = $storage->data($attribute['storage']['key'] . '.' . $attribute['uuid']);
        return $node;
    }

    private static function createFooter($object, $form, $attribute=[]){
        $form->footer = '<button type="submit"><i class="icon icon-tasks category-default"></i> Save</button>' . "\n" .
            '</form>';
        return $form;
    }

    private static function createBody($object, $form, $attribute=[]){
        $result = '';
        if(isset($attribute['break'])){
            $break = $attribute['break']; //disable break with empty break attribute
        } else {
            $break = '<br>';
        }
        foreach($form->nodeList as $node){
            $result .= $node->label .
                $node->field .
                $break . "\n";

        }
        $form->body = $result;
        return $form;
    }

    private static function createHeader($object, $header=[]){
        //file=false for frontend (disable it, not needed, unsafe use get parameter for it (create temporary key with email)
        //file=true, for backend (should be save or disable customer)

        if(empty($header['name'])){
            throw new Exception('Header should have a name for form name=""');
        }
        $result = '';
        $result = '<form name="' . $header['name'] . '" data-request="'. $header['data']['request'] .'" data-target=\'' . $header['data']['target'] . '\' data-method="' . $header['data']['method'] . '">';
        return $result;
    }

    private static function createText($object, $node=null, $record){
        if(property_exists($record, 'name') === false){
            throw new Exception('name property missing in text record');
        }
        if(property_exists($record, 'class') === false){
            $class = '';
        } else {
            $class = $record->class;
        }
        $explode = explode('node.', $record->name, 2);
        $value = '';
        if(isset($explode[1])){
            if(property_exists($node, $explode[1])){
                $value = $node->{$explode[1]};
            }
        }
        $data = Form::createData($object, $node, $record);
        $result =  new stdClass();
        $result->label = Form::createLabel($object, $node, $record);
        $result->type = 'text';
        if(!empty($data)){
            $result->field = '<input type="text" value="'. $value .'" name="' . $record->name . '" class="' . $class .'" ' . $data . '></input>';
        } else {
            $result->field = '<input type="text" value="'. $value .'" name="' . $record->name . '" class="' . $class .'"></input>';
        }
        return $result;
    }

    private static function createChoice($object, $node=null, $record){
        if(property_exists($record, 'choice') === false){
            throw new Exception('choice property missing in choice record');
        }
        if(property_exists($record->choice, 'multiple') === false){
            $multiple = false;
        } else {
            $multiple = $record->choice->multiple;
        }
        if(property_exists($record->choice, 'list') === false){
            throw new Exception('choice list property missing in choice record');
        }
        $result = new stdClass();
        $result->label = Form::createLabel($object, $node, $record);
        $result->type = 'hidden';
        $result->field = Form::createSelect($object, $node, $record);
        return $result;
        /*
        if(property_exists($record->label))
        $result = new stdClass();
        $result->label = $record->label->text
        var_dump($record);
        die;
        */
    }

    private static function createData($object, $node=null, $record){
        $data = '';
        if(property_exists($record, 'rule')){
            foreach($record->rule as $rule => $value){
                $data .= 'data-rule-' . str_replace('.', '-', $rule) . '="' . $value . '" ';
            }
        }
        $data = substr($data, 0, -1);
        if(empty($data)){
            $data = '';
        }
        return $data;
    }

    private static function createSelect($object, $node, $record){
        $class = '';
        if(property_exists($record, 'class')){
            $class = $record->class;
        }
        $data = Form::createData($object, $node, $record);
        if(!empty($data)){
            $result = '<select name="' . $record->name . '" class="' . $class . '" ' . $data . '>';
        } else {
            $result = '<select name="' . $record->name . '" class="' . $class . '">';
        }

        $selected = null;
        if(property_exists($record->choice, 'selected')){
            $selected = $record->choice->selected;
        }
        $explode = explode('node.', $record->name, 2);
        if(isset($explode[1])){
            if(!is_object($node)){
                $debug = debug_backtrace(true);

                dd($debug);
            }
            if(property_exists($node, $explode[1])){
                $selected = $node->{$explode[1]};
            }
        }
        foreach($record->choice->list as $value => $text){
            if($selected == $value){
                $result .= '<option value="' . $value  . '" selected="selected">' . $text . '</option>';
            } else {
                $result .= '<option value="' . $value  . '">' . $text . '</option>';
            }
        }
        $result .= '</select>';
        return $result;
    }

    private static function createLabel($object, $node=null, $record){
        if(property_exists($record, 'label') === false){
            throw new Exception('label property missing in text record');
        }
        if(property_exists($record->label, 'text') === false){
            throw new Exception('text property missing in text label record');
        }
        if(property_exists($record->label, 'class') === false){
            $label_class = '';
        } else {
            $label_class = $record->label->class;
        }
        return '<label class="' . $label_class . '">' . $record->label->text . '</label>';
    }

    private static function createUuid($object, $node=null, $record){
        $uuid = $object->request('uuid');
        if(empty($uuid) || strtolower($uuid) == strtolower(FORM::DATA_UUID_NEW)){
            $uuid = Result::uuid();
        } else {
            if(
                $node !== null &&
                is_object($node) &&
                property_exists($node, 'uuid')
            ){
                $uuid = $node->uuid;
            } else {
                throw new Exception('uuid missing in node');
            }
            //load data, make sure uuid is the first thing to create
        }
        $result = new stdClass();
        $result->label = '';
        $result->type = 'hidden';
        $result->field = '<input type="hidden" name="'.
            $record->name .
            '" value="' . $uuid .'"></input>';
        return $result;
    }
}