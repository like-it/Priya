<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use stdClass;
use Priya\Application;
use Priya\Module\Core\Data;

class Pager extends Data {
    const LENGTH = 20;
    const METHOD = 'replace-with';
    const TARGET = 'body';

    private $recordStart;
    private $recordLength;
    private $recordCount;

    private $page;
    private $pageAmount;
    private $route;

    private $content;

    private $method;
    private $target;

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
        $this->recordStart(0);
        $this->recordLength(Pager::LENGTH);
        $this->method(Pager::METHOD);
        $this->target(Pager::TARGET);
    }

    public function run(){
        $page = $this->request('page');
        $this->calculatePageAmount();
        if($page >= $this->pageAmount()){
            $page = $this->pageAmount();
        }
        if($page <= 1){
            $page = 1;
        }
        $this->calculateRecordStart($page);
        return $this->content('create');
    }

    public function data($attribute=null, $value=null){
        $data = parent::data($attribute, $value);
        $counter = 0;
        foreach($data as $nr => $node){
            $counter++;
        }
        $this->recordCount($counter);
        return $data;
    }

    public function page($page=null){
        if($page !== null){
            $this->setPage($page);
        }
        return $this->getPage();
    }

    private function setPage($page=0){
        $this->page = intval($page);
    }

    private function getPage(){
        return $this->page;
    }

    public function method($method=null){
        if($method!== null){
            $this->setMethod($method);
        }
        return $this->getMethod();
    }

    private function setMethod($method=''){
        $this->method = $method;
    }

    private function getMethod(){
        return $this->method;
    }

    public function target($target=null){
        if($target!== null){
            $this->setTarget($target);
        }
        return $this->getTarget();
    }

    private function setTarget($target=''){
        $this->target = $target;
    }

    private function getTarget(){
        return $this->target;
    }

    public function route($route=null, $attribute=null){
        if($route !== null){
            $this->setRoute($route);
        }
        return $this->getRoute();
    }

    private function setRoute($route=''){
        $this->route = $route;
    }

    private function getRoute(){
        return $this->route;
    }

    public function start($start=null){
        return $this->recordStart($start);
    }

    private function recordStart($recordStart=null){
        if($recordStart !== null){
            $this->setRecordStart($recordStart);
        }
        return $this->getRecordStart();
    }

    private function setRecordStart($recordStart=0){
        $this->recordStart  = intval($recordStart);
    }

    private function getRecordStart(){
        return $this->recordStart;
    }

    public function length($length=null){
        return $this->recordLength($length);
    }

    private function recordLength($recordLength=null){
        if($recordLength !== null){
            $this->setRecordLength($recordLength);
        }
        return $this->getRecordLength();
    }

    private function setRecordLength($recordLength=20){
        $this->recordLength = intval($recordLength);
    }

    private function getRecordLength(){
        return $this->recordLength;
    }

    public function recordCount($recordCount=null){
        if($recordCount !== null){
            $this->setRecordCount($recordCount);
        }
        return $this->getRecordCount();
    }

    private function setRecordCount($recordCount=''){
        $this->recordCount = $recordCount;
    }

    private function getRecordCount(){
        return $this->recordCount;
    }

    public function calculateRecordStart($page=''){
        if(!empty($page)){
            $this->setPage($page);
        }
        $page = $this->getPage();
        $recordLength = $this->getRecordLength();
        if(is_numeric($page)===false){
            $page = 1;
            $this->setPage($page);
        }
        if(is_numeric($recordLength)===false){
            $recordLength = Pager::LENGTH;
            $this->setRecordLength($recordLength);
        }
        $recordStart = ($page * $recordLength) - $recordLength;
        $this->setRecordStart($recordStart);
    }

    public function calculatePageAmount($recordCount=null){
        if($recordCount!==null){
            $this->recordCount($recordCount);
        }
        $recordLength = $this->recordLength();
        $recordCount = $this->recordCount();
        return $this->pageAmount(intval(ceil($recordCount/$recordLength)));
    }

    public function amount($amount=null){
        return $this->pageAmount($amount);
    }

    private function pageAmount($pageAmount=null){
        if($pageAmount !== null){
            $this->setPageAmount($pageAmount);
        }
        return $this->getPageAmount();
    }

    private function setPageAmount($pageAmount=0){
        $this->pageAmount = $pageAmount;
    }

    private function getPageAmount(){
        return $this->pageAmount;
    }

    public function content($attribute=null, $value=null){
        if($attribute !== null){
            if($attribute == 'create'){
                return $this->createContent($value);
            }
            elseif($value !== null){
                if($attribute == 'delete'){
                    return $this->deleteContent($value);
                }
                else {
                    $content = $this->content();
                    if(is_null($content)){
                        $content = $this->content(new stdClass());
                    }
                    $this->object_set($attribute, $value, $this->content());
                }
            } else {
                if(is_string($attribute)){
                    return $this->object_get($attribute, $this->content());
                } else {
                    $this->setContent($attribute);
                    return $this->getContent();
                }
            }
        }
        return $this->getContent();
    }

    private function setContent($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->content)){
                foreach($attribute as $key => $value){
                    $this->content->{$key} = $value;
                }
            }
            elseif(is_array($this->content)){
                foreach($attribute as $key => $value){
                    $this->content[$key] = $value;
                }
            } else {
                $this->content = $attribute;
            }
        } else {
            if(is_object($this->content)){
                $this->content->{$attribute} = $value;
            }
            elseif(is_array($this->content)) {
                $this->content[$attribute] = $value;
            }
        }
    }

    private function getContent($attribute=null){
        if($attribute === null){
            return $this->content;
        }
        if(is_object($this->content)){
            if(isset($this->content->{$attribute})){
                return $this->content->{$attribute};
            } else {
                return false;
            }
        }
        elseif(is_array($this->content)){
            if(isset($this->content[$attribute])){
                return $this->content[$attribute];
            } else {
                return false;
            }
        }
    }

    private function deleteContent($attribute=null){
        return $this->object_delete($attribute, $this->content());
    }

    private function createContent($attribute=null){
        $data = $this->data();
        $counter = 0;
        $recordStart = $this->recordStart();
        $recordLength = $this->recordLength();
        $content = array();

        foreach($data as $node){
            if($counter >= $recordStart && $counter < ($recordStart + $recordLength)){
                $content[] = $node;
            }
            if($counter >= ($recordStart + $recordLength)){
                break;
            }
            $counter++;
        }
        $node = new stdClass();
        $node->route = $this->route();
        $node->page = new stdClass();
        $node->page->current = $this->page();
        $node->page->amount = $this->amount();
        $node->page->length = $this->length();
        $node->method = $this->method();
        $node->target = $this->target();
        $node->nodeList = $this->object($content);
        return $this->content($node);
    }
}