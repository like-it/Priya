<?php

namespace Priya\Module;

use Priya\Application;
use Priya\Module\Core\Data;
use stdClass;

class Handler extends Data{
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_CLI = 'text/cli';

    private $request;
    private $contentType;

    public function __construct($handler=null, $route=null, $data=null){
        $this->data($handler);
        $this->input('create');
        $this->contentType('create');
        $this->lastModified('create');
        $this->referer('create');
    }

    public function request($attribute=null, $value=null){
        if($attribute !== null){
            if($value !== null){
                if($attribute == 'create'){
                    return $this->createRequest($value);
                }
                elseif($attribute=='delete'){
                    return $this->deleteRequest($value);
                }
                elseif($attribute=='request'){
                    $value = $this->removeHost($value);
                    $this->object_set($attribute, $value, $this->request());
                } else {
                    $this->object_set($attribute, $value, $this->request());
                }
            } else {
                if(is_string($attribute)){
                    return $this->object_get($attribute, $this->request());
                } else {
                    $this->setRequest($attribute);
                    return $this->getRequest();
                }
            }
        }
        return $this->getRequest();
    }

    private function setRequest($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            $this->request = $attribute;
        } else {
            if(is_object($this->request)){
                $this->request->{$attribute} = $value;
            } else {
                $this->request[$attribute] = $value;
            }

        }
    }

    private function getRequest($attribute=null){
        if($attribute === null){
            if(is_null($this->request)){
                $this->request = new stdClass();
            }
            return $this->request;
        }
        if(isset($this->request[$attribute])){
            return $this->request[$attribute];
        } else {
            return false;
        }
    }

    private function createRequest($data=''){
        foreach($data as $attribute =>$post){
            if(isset($post->name) && isset($post->value)){
                $this->request($post->name, $post->value);
            } elseif($attribute !== 'nodeList') {
                $this->request($attribute, $post);
            }
        }
        if(isset($data->nodeList)){
            foreach($data->nodeList as $nr => $object){
                if(is_array($object) || is_object($object))
                foreach($object as $attribute => $value){
                    $this->request($attribute, $value);
                } else {
                    $nodeList = $this->request('nodeList');
                    if(empty($nodeList)){
                        $nodeList = array();
                    }
                    $nodeList[] = $object;
                    $this->request('nodeList', $nodeList);
                }
            }
        }
        return $this->getRequest();
    }

    private function deleteRequest($attribute=''){
        return $this->object_delete($attribute, $this->request());
    }

    public function lastModified(){
        $this->request('Last-Modified: '. gmdate('D, d M Y H:i:s T'));
    }

    public function contentType($contentType=null){
        if($contentType !== null){
            if($contentType == 'create'){
                return $this->createContentType();
            } else {
                $this->setContentType($contentType);
            }
        }
        return $this->getContentType();
    }

    private function setContentType($contentType=''){
        $this->contentType = $contentType;
    }

    private function getContentType(){
        return $this->contentType;
    }

    private function createContentType(){
        $contentType = 'text/html';
        if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == Handler::CONTENT_TYPE_JSON){
            $contentType = Handler::CONTENT_TYPE_JSON;
        }
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            $contentType = Handler::CONTENT_TYPE_JSON;
        }
        if(isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], 'text/css')){
            $contentType = Handler::CONTENT_TYPE_CSS;
        }
        $host = $this->host();
        if(empty($host)){
            $contentType = Handler::CONTENT_TYPE_CLI;
        }
        $request = $this->request('request');
        $tmp = explode('.', $request);
        $ext = strtolower(end($tmp));

        $allowed_contentType = $this->data('contentType');
        if(empty($allowed_contentType)){
            $allowed_contentType = $this->data('Content-Type');
        }
        if(isset($allowed_contentType->{$ext})){
            $contentType = $allowed_contentType->{$ext};
        }
        $this->request('contentType',$contentType);
        $this->request('Content-Type',$contentType);
        return $this->contentType($contentType);
    }

    public function input(){
        global $argc, $argv;

        $node = array();
        $input = htmlspecialchars(htmlspecialchars_decode(implode('', file('php://input')), ENT_NOQUOTES), ENT_NOQUOTES, "UTF-8");
        if(empty($input) && !empty($_REQUEST)){
            $input = htmlspecialchars(json_encode(array('nodeList' => array(0 => $_REQUEST))), ENT_NOQUOTES, "UTF-8");
        }
        elseif(!empty($input) && !empty($_REQUEST)){
            $old = json_decode($input);
            if(!isset($old->nodeList)){
                $input = new stdClass();
                $input->nodeList = array();
                if(is_array($old) || is_object($old)){
                    foreach($old as $key => $node){
                        $object = new stdClass();
                        if(isset($node->name) && isset($node->value)){
                            $object->{$node->name} = $node->value; //old behaviour
                            if(!is_numeric($key)){
                                $object->{$key} = new stdClass();
                                $object->{$key}->name = $node->name;
                                $object->{$key}->value = $node->value;
                            }
                        } else {
                            $object->{$key} = $node;
                        }

                        $input->nodeList[] = $object;
                    }

                }
                $input->nodeList[] = $_REQUEST;
                $input = json_encode($input);
            } else {
                $input = $old;
                $input->nodeList = $this->object($old->nodeList, 'array');
                if(!is_array($input->nodeList)){
                    $input->nodeList = (array) $input->nodeList;
                }
                $input->nodeList[] = $_REQUEST;		//strange but works...
                $input = json_encode($input);
            }
        }
        elseif(!empty($input) && empty($_REQUEST)){
            $old = json_decode($input);
            if(!isset($old->nodeList)){
                $input = new stdClass();
                $input->nodeList = array();
                foreach($old as $node){
                    $input->nodeList[] = $node;
                }
                $input = json_encode($input);
            }
        }
        $data = json_decode($input);
        if(empty($data) && !empty($argv)){
            $data = new stdClass();
            $data->nodeList = array();
            $object = new stdClass();
            $object->data =  $argv;
            $data->nodeList[] = $object;
            $object = new stdClass();
            $object->file =  Application::DIR . Application::DS . basename(array_shift($argv));
            $data->nodeList[] = $object;
            if(count($argv) >= 1){
                $object = new stdClass();
                $object->request =  str_replace('\\','/',array_shift($argv));
                $data->nodeList[] = $object;
            }
        }
        $this->request('create',$data);
    }

    public function webRoot(){
        if(empty($_SERVER['DOCUMENT_ROOT'])){
            return false;
        }
        return str_replace('/', Application::DS, $_SERVER['DOCUMENT_ROOT'] .
        Application::DS);
    }

    public function web(){
        if(empty($_SERVER['REQUEST_SCHEME'])){
            return false;
        }
        if(empty($_SERVER['HTTP_HOST'])){
            return false;
        }
        return
        $_SERVER['REQUEST_SCHEME'] .
        '://' .
        $_SERVER['HTTP_HOST'] .
        '/';
    }

    public function url($url=null){
        $url = parent::url($url);
        if($url === null){
            if(empty($_SERVER['REQUEST_SCHEME'])){
                return false;
            }
            if(empty($_SERVER['HTTP_HOST'])){
                return false;
            }
            if(!isset($_SERVER['REQUEST_URI'])){
                return false;
            }
            $url =
            $_SERVER['REQUEST_SCHEME'] .
            '://' .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']
            ;
        }
        return parent::url($url);
    }

    public function session($attribute=null, $value=null){
        if(!isset($_SESSION)){
            if($attribute !== null){
                session_start();
                $_SESSION['id'] = session_id();
                $post = $this->session('post');
                $this->session('delete', 'post');
                if(is_array($post)){
                    foreach($post as $key => $value){
                        if(is_array($value)){
                            foreach ($value as $k => $v){
                                $this->request($key . '.' . $k, $v);
                            }
                        } else {
                            $this->request($key, $value);
                        }
                    }
                }
            } else {
                return array();
            }
        }
        if($attribute !== null){
            $tmp = explode('.', $attribute);
            if($value !== null){
                if($attribute == 'delete' && $value == 'session'){
                    return session_destroy();
                }
                elseif($attribute == 'delete'){
                    $tmp = explode('.', $value);
                    switch(count($tmp)){
                        case 1 :
                            unset($_SESSION[$value]);
                        break;
                        case 2 :
                            unset($_SESSION[$tmp[0]][$tmp[1]]);
                        break;
                        case 3 :
                            unset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]);
                        break;
                        case 4 :
                            unset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]);
                        break;
                        case 5 :
                            unset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]);
                        break;
                    }
                    return true;
                } else {
                    if(is_object($value)){
                        $value = $this->object($value, 'array');
                    }
                    switch(count($tmp)){
                        case 1 :
                            $_SESSION[$attribute] = $value;
                        break;
                        case 2 :
                            $_SESSION[$tmp[0]][$tmp[1]] = $value;
                        break;
                        case 3 :
                            $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]] = $value;
                        break;
                        case 4 :
                            $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]] = $value;
                        break;
                        case 5 :
                            $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]] = $value;
                        break;
                    }
                }
            }
            switch(count($tmp)){
                case 1 :
                    if(isset($_SESSION[$attribute])){
                        return $_SESSION[$attribute];
                    } else {
                        return null;
                    }
                break;
                case 2 :
                    if(isset($_SESSION[$tmp[0]]) && isset($_SESSION[$tmp[0]][$tmp[1]])){
                        return $_SESSION[$tmp[0]][$tmp[1]];
                    } else {
                        return null;
                    }
                break;
                case 3 :
                    if(isset($_SESSION[$tmp[0]]) && isset($_SESSION[$tmp[0]][$tmp[1]]) && isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]])){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]];
                    } else {
                        return null;
                    }
                break;
                case 4 :
                    if(
                        isset($_SESSION[$tmp[0]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]];
                    } else {
                        return null;
                    }
                break;
                case 5 :
                    if(
                        isset($_SESSION[$tmp[0]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]];
                    } else {
                        return null;
                    }
                break;
            }
        } else {
            return $_SESSION;
        }
    }

    public function cookie($attribute=null, $value=null, $duration=null){
        if($attribute !== null){
            if($value !== null){
                if($attribute == 'delete'){
                    @setcookie($value, null, 0, "/"); //ends at session
                } else {
                    if($duration === null){
                        $duration = 60*60*24*365*2; // 2 years
                    }
                    @setcookie($attribute, $value, time() +	 $duration, "/");
                }
            } else {
                if(isset($_COOKIE[$attribute])){
                    return $_COOKIE[$attribute];
                } else {
                    return null;
                }
            }
        }
        return $_COOKIE;
    }

    public function referer($referer=null){
        if($referer !== null){
            if($referer == 'create'){
                $referer = $this->request('referer');
                if(empty($referer)){
                    return $this->createReferer();
                } else {
                    return $referer;
                }
            } else {
                $this->request('referer',$referer);
            }
        }
        return $this->request('referer');
    }

    private function createReferer(){
        if(isset($_SERVER['HTTP_REFERER'])){
            return $this->referer($_SERVER['HTTP_REFERER']);
        }
    }

    public function host(){
        if(isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['SERVER_NAME'])){
            $host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
            return $host;
        }
    }

    public function removeHost($value=''){
        $host = $this->host();
        if(empty($host)){
            return $value;
        }
        $value = explode($host, $value, 2);
        $value = implode('', $value);
        return $value;
    }

}
?>