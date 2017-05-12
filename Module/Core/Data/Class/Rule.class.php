<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Core\Data;

use stdClass;
use Priya\Module\Core\Data;

class Rule extends Data {
    const DIR = __DIR__;

    private $ruleList;

    public function __construct($node, $data=''){
        $this->data('node', $node);
        $this->data('data', $data);
    }

    private function ruleList($ruleList=null){
        if($ruleList !== null){
            $this->setRuleList($ruleList);
        }
        return $this->getRuleList();
    }

    private function setRuleList($ruleList = array()){
        $this->ruleList = $ruleList;
    }

    private function getRuleList(){
        return $this->ruleList;
    }

    public function add(){
        $args = func_get_args();
        $amount = func_num_args();
        $ruleList = $this->ruleList();
        if(empty($ruleList)){
            $ruleList = array();
        }
        switch(func_num_args()){
            case '6':
                $object = new stdClass();
                $object->attribute = array_shift($args);
                $object->compare = array_shift($args);
                $object->type = strtolower(array_shift($args));
                $object->method = strtolower(array_shift($args));
                $object->rule = strtolower(array_shift($args));
                $object->error = array_shift($args);
                $ruleList[] = $object;
            break;
            case '5':
                $object = new stdClass();
                $object->attribute = array_shift($args);
                $object->compare = array_shift($args);
                $object->type = strtolower(array_shift($args));
                $object->rule = strtolower(array_shift($args));
                $object->error = array_shift($args);
                $ruleList[] = $object;
            break;
            case '4':
                $object = new stdClass();
                $object->attribute = array_shift($args);
                $object->type = strtolower(array_shift($args));
                $object->rule = strtolower(array_shift($args));
                $object->error = array_shift($args);
                $ruleList[] = $object;
            break;
            case '1':
                $ruleList[] = array_shift($args);
        }
        $this->ruleList($ruleList);
    }

    public function validate(){
        $ruleList = $this->ruleList();
        foreach($ruleList as $nr => $rule){
            if(!isset($rule->attribute)){
                trigger_error('attribute not defined in rule');
            }
            if(!isset($rule->type)){
                trigger_error('type not defined in rule');
            }
            if(!isset($rule->rule)){
                trigger_error('rule not defined in rule');
            }
            if(!isset($rule->error)){
                trigger_error('error not defined in rule');
            }
            switch($rule->type){
                case 'string':
                    if(!isset($rule->method)){
                        switch($rule->rule){
                            case 'unique':
                                $count = 0;
                                $data = $this->data($rule->compare);
                                if(is_array($data) || is_object($data)){
                                    $jid = $this->data('node.jid');
                                    foreach($data as $key => $node){
                                        if($key == $jid){
                                            continue;
                                        }
                                        $selector = $rule->compare . '.' . $key . '.' . str_replace('node.', '', $rule->attribute);
                                        $select = $this->data($selector);
                                        $attribute = $this->data($rule->attribute);
                                        if($attribute == $select){
                                            $count++;
                                        }
                                    }
                                }
                                if($count >= 1){
                                    $this->error('add', $rule->attribute, $rule->error);
                                    $this->error('add', $rule->error, true);
                                }
                            break;
                            case 'email':
                                $attribute = $this->data($rule->attribute);
                                if(empty($attribute)){
                                    $attribute = $rule->attribute;
                                }
                                if($this->is_email($attribute) === false){
                                    $this->error('add', $rule->attribute, $rule->error);
                                    $this->error('add', $rule->error, true);
                                }
                            break;
                            case '!=':
                                $attribute = $this->data($rule->attribute);
                                $compare = $this->data($rule->compare);
                                if($attribute  != $compare){
                                    //actions?
                                } else {
                                    $this->error('add', $rule->attribute, $rule->error);
                                    $this->error('add', $rule->error, true);
                                }
                            break;
                            case '==':
                                $attribute = $this->data($rule->attribute);
                                $compare = $this->data($rule->compare);

                                if($attribute  == $compare){
                                    //actions?
                                } else {
                                    $this->error('add', $rule->attribute, $rule->error);
                                    $this->error('add', $rule->error, true);
                                }
                            break;
                            default:
                                trigger_error('rule not defined in rule');
                            break;
                        }
                    } else {
                        switch($rule->method){
                            case 'strlen' :
                                switch($rule->rule){
                                    case '>' :
                                        if(strlen($this->data($rule->attribute)) > $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    case '>=':
                                        if(strlen($this->data($rule->attribute)) >= $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    case '!=':
                                        if(strlen($this->data($rule->attribute)) != $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    case '==':
                                        if(strlen($this->data($rule->attribute)) == $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    case '<=':
                                        if(strlen($this->data($rule->attribute)) <= $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    case '<':
                                        if(strlen($this->data($rule->attribute)) < $rule->compare){
                                        } else {
                                            $this->error('add', $rule->attribute, $rule->error);
                                            $this->error('add', $rule->error, true);
                                        }
                                    break;
                                    default:
                                        trigger_error('rule not defined in strlen');
                                    break;
                                }
                            break;
                        }
                    }
                break;
                case 'array':
                    if(!isset($rule->method)){
                        if($rule->rule == 'in'){
                            if(is_array($rule->compare)){
                                $compare = $rule->compare;
                            } else {
                                $compare = $this->data($rule->compare);
                            }
                            $data  = $this->data($rule->attribute);
                            if(is_array($data) || is_object($data)){
                                foreach ($data as $key => $value){
                                    $found = false;
                                    foreach($compare as $nr => $node){
                                        if($value == $node){
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if(empty($found)){
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                }
                            } else {
                                $found = false;
                                foreach($compare as $nr => $node){
                                    if($data == $node){
                                        $found = true;
                                        break;
                                    }
                                }
                                if(empty($found)){
                                    $this->error('add', $rule->attribute, $rule->error);
                                    $this->error('add', $rule->error, true);
                                }
                            }
                        }
                    } else {
                        if($rule->method == 'count'){
                            switch($rule->rule){
                                case '>' :
                                    if(count($this->data($rule->attribute)) > $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                case '>=' :
                                    if(count($this->data($rule->attribute)) >= $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                case '!=' :
                                    if(count($this->data($rule->attribute)) != $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                case '==' :
                                    if(count($this->data($rule->attribute)) == $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                case '<=' :
                                    if(count($this->data($rule->attribute)) <= $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                case '<' :
                                    if(count($this->data($rule->attribute)) < $rule->compare){
                                    } else {
                                        $this->error('add', $rule->attribute, $rule->error);
                                        $this->error('add', $rule->error, true);
                                    }
                                break;
                                default:
                                    trigger_error($rule);
                                    trigger_error('rule not defined in array count');
                                break;
                            }
                        }
                    }
                break;
                default:
                    trigger_error('unknown rule->type in rule');
                break;
            }
        }
        return $this->data('node');
    }

    public function is_email($email=''){
        $requirement = '[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,63}$';
        $requirement = '/' . str_replace('/', '\/', $requirement) . '/i';
        $amount = preg_match_all($requirement, $email, $matches);
        if($amount == 1){
            return true;
        }
        return false;
    }
}