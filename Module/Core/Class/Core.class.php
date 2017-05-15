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
use Priya\Module\Core\Object;

class Core {
    use Core\Object;

    private $mail;
    private $handler;
    private $route;
    private $post;
    private $error;
    private $message;
    private $autoload;

    public function __construct($handler=null, $route=null){
        $this->handler($handler);
        $this->route($route);
    }

    public static function handler_exception($exception){
        $handler = new Handler();
        if($handler->contentType() == Handler::CONTENT_TYPE_JSON){
            echo Core::object($exception, 'json');
            return true;
        } else {
            var_dump($exception);
            die;
        }
    }


    public static function handler_error($number, $message, $file='', $line=null, $context=array()){
        $error = array();
        $error['number'] = $number;
        $error['message'] = $message;
        $error['file'] = $file;
        $error['line'] = $line;
        $error['context'] = $context;
        $handler = new Handler();
        if($handler->contentType() == Handler::CONTENT_TYPE_JSON){
            echo Core::object($error, 'json');
            return true;
        } else {
            var_dump($error);
            die;
        }
    }

    public function handler($handler=null){
        if($handler !== null){
            $this->setHandler($handler);
        }
        $handler = $this->getHandler();
        if($handler === null){
            $this->setHandler(new Handler());
        }
        return $this->getHandler();
    }

    private function setHandler($handler=''){
        $this->handler = $handler;
    }

    private function getHandler(){
        return $this->handler;
    }

    public function route($route=null, $attribute=null){
        if($route !== null){
            if(is_object($route)){
                $this->setRoute($route);
                return $this->getRoute();
            }
        }
        if($route === null){
            return $this->getRoute();
        } else {
            return $this->getRoute()->route($route, $attribute);
        }
    }

    private function setRoute($route=''){
        $this->route = $route;
    }

    private function getRoute(){
        return $this->route;
    }

    public function request($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        return $this->handler()->request($attribute, $value);
    }

    public function upload($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        return $this->handler()->file($attribute, $value);
    }

    public function parameter($parameter){
        $data = $this->request('data');
        if(is_numeric($parameter)){
            if(isset($data[$parameter])){
                $param = ltrim($data[$parameter],'-');
                return $param;
            } else {
                return false;
            }
        } else {
            foreach($data as $key => $param){
                $param = ltrim($param,'-');
                $tmp = explode('=', $param);
                if(count($tmp) > 1){
                    $param = array_shift($tmp);
                    $value = implode('=', $tmp);
                }
                if(strtolower($param) == strtolower($parameter)){
                    if(isset($value)){
                        return $value;
                    }
                    return true;
                }
            }
            return false;
        }
    }

    public function csrf(){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        return $this->handler()->csrf();
    }

    public function session($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        return $this->handler()->session($attribute, $value);
    }

    public function cookie($attribute=null, $value=null){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        return $this->handler()->cookie($attribute, $value);
    }

    public function module(){
        $module = explode(__NAMESPACE__, get_called_class(), 2);
        if(count($module) == 2){
            array_shift($module);
        }
        $module = implode('', $module);
        $module = str_replace('\\', Application::DS, $module);
        return $module;
    }

    public function dom_class($class=''){
        $class = strtolower($class);
        $class = str_replace(array('\\', '/'), '-', $class);
        return $class;
    }

    public function refresh($url=''){
        $error = $this->error();
        if(!empty($error)){
            $this->session('error', $error);
        }
        $message = $this->message();
        if(!empty($message)){
            $this->session('message', $message);
        }
        $post = $this->post();
        if(!empty($post)){
            $this->session('post', $post);
        }
        $contentType = $this->request('contentType');
        if($contentType == Handler::CONTENT_TYPE_JSON){
            $output = new stdClass();
            $output->refresh = $url;
            echo json_encode($output);
            die;
        } else {
            header('Location: '.$url);
            die;
        }
    }

    public function post($type=null, $attribute=null, $value=null){
        if($type !== null){
            if($type == 'request' && $attribute === null && $value === null){
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
            }
            if($type == 'add' && $attribute !== null && $value !== null){
                $post = $this->post($attribute);
                if(!empty($post) && (!is_array($post) && !is_object($post))){
                    $post = (array) $post;
                    $post[] = $value;
                }
                elseif(!empty($post)){
                    $post = $this->object($post, 'array');
                    $post[] = $value;
                } else {
                    $post = $value;
                }
                $nodeList = $this->post('nodeList');
                if(empty($nodeList) || !is_array($nodeList)){
                    $nodeList = array();
                }
                $nodeList[] = $attribute;
                $this->post('nodeList', $nodeList);
                $this->post($attribute, $post);
            }
            elseif($attribute !== null){
                if($type == 'delete'){
                    return $this->deletePost($value);
                } else {
                    $post = $this->post();
                    if(is_null($post)){
                        $post = $this->post(new stdClass());
                    }
                    $this->object_set($type, $attribute, $this->post());
                }
            } else {
                if(is_string($type)){
                    return $this->object_get($type, $this->post());
                } else {
                    $this->setPost($type);
                    return $this->getPost();
                }
            }
        }
        return $this->getPost();
    }

    private function setPost($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->post)){
                foreach($attribute as $key => $value){
                    $this->post->{$key} = $value;
                }
            }
            elseif(is_array($this->post)){
                foreach($attribute as $key => $value){
                    $this->post[$key] = $value;
                }
            } else {
                $this->post = $attribute;
            }
        } else {
            if(is_object($this->post)){
                $this->post->{$attribute} = $value;
            }
            elseif(is_array($this->post)) {
                $this->post[$attribute] = $value;
            }
        }
    }

    private function getPost($attribute=null){
        if($attribute === null){
            return $this->post;
        }
        if(is_object($this->post)){
            if(isset($this->post->{$attribute})){
                return $this->post->{$attribute};
            } else {
                return false;
            }
        }
        elseif(is_array($this->post)){
            if(isset($this->post[$attribute])){
                return $this->post[$attribute];
            } else {
                return false;
            }
        }
    }

    private function deletePost($attribute=null){
        return $this->object_delete($attribute, $this->post());
    }

    public function error($type=null, $attribute=null, $value=null){
        if($type !== null){
            if($type == 'add' && $attribute !== null && $value !== null){
                $error = $this->error($attribute);
                if(!empty($error) && (!is_array($error) && !is_object($error))){
                    $error = (array) $error;
                    $error[] = $value;
                }
                elseif(!empty($error)){
                    $error = $this->object($error, 'array');
                    $error[] = $value;
                } else {
                    $error = $value;
                }
                $nodeList = $this->error('nodeList');
                if(empty($nodeList) || !is_array($nodeList)){
                    $nodeList = array();
                }
                $nodeList[] = $attribute;
                $this->error('nodeList', $nodeList);
                $this->error($attribute, $error);
            }
            elseif($attribute !== null){
                if($type == 'delete'){
                    $delete = $this->deleteError($attribute);
                    //add delete when parent is empty
                    $nodeList = $this->error('nodeList');
                    if(!empty($nodeList) && is_array($nodeList)){
                        foreach($nodeList as $nr => $node){
                            if($node == $attribute){
                                unset($nodeList[$nr]);
                            }
                        }
                    }
                    if(empty($nodeList) && is_array($nodeList)){
                        $this->error('delete', 'nodeList');
                    } else {
                        $this->error('nodeList', $nodeList);
                    }
                    return $delete;
                } else {
                    $error = $this->error();
                    if(is_null($error)){
                        $error = $this->error(new stdClass());
                    }
                    $this->object_set($type, $attribute, $this->error());
                }
            } else {
                if(is_string($type)){
                    return $this->object_get($type, $this->error());
                } else {
                    $this->setError($type);
                    return $this->getError();
                }
            }
        }
        return $this->getError();
    }

    private function setError($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->error)){
                foreach($attribute as $key => $value){
                    $this->error->{$key} = $value;
                }
            }
            elseif(is_array($this->error)){
                foreach($attribute as $key => $value){
                    $this->error[$key] = $value;
                }
            } else {
                $this->error = $attribute;
            }
        } else {
            if(is_object($this->error)){
                $this->error->{$attribute} = $value;
            }
            elseif(is_array($this->error)) {
                $this->error[$attribute] = $value;
            } else {
                var_dump('setError create object and set object');
            }
        }
    }

    private function getError($attribute=null){
        if($attribute === null){
            return $this->error;
        }
        if(is_object($this->error)){
            if(isset($this->error->{$attribute})){
                return $this->error->{$attribute};
            } else {
                return false;
            }
        }
        elseif(is_array($this->error)){
            if(isset($this->error[$attribute])){
                return $this->error[$attribute];
            } else {
                return false;
            }
        }
    }

    private function deleteError($attribute=null){
        return $this->object_delete($attribute, $this->error());
    }

    public function message($type=null, $attribute=null, $value=null){
        if($type !== null){
            if($type == 'add' && $attribute !== null && $value !== null){
                $message = $this->message($attribute);
                if(!empty($message) && (!is_array($message) && !is_object($message))){
                    $message = (array) $message;
                    $message[] = $value;
                }
                elseif(!empty($message)){
                    $message = $this->object($message, 'array');
                    $message[] = $value;
                } else {
                    $message = $value;
                }
                $nodeList = $this->message('nodeList');
                if(empty($nodeList) || !is_array($nodeList)){
                    $nodeList = array();
                }
                $nodeList[] = $attribute;
                $this->message('nodeList', $nodeList);
                $this->message($attribute, $message);
            }
            elseif($attribute !== null){
                if($type == 'delete'){
                    $delete = $this->deleteMessagedeleteMessage($attribute);
                    //add delete when parent is empty
                    $nodeList = $this->message('nodeList');
                    if(!empty($nodeList) && is_array($nodeList)){
                        foreach($nodeList as $nr => $node){
                            if($node == $attribute){
                                unset($nodeList[$nr]);
                            }
                        }
                    }
                    if(empty($nodeList) && is_array($nodeList)){
                        $this->message('delete', 'nodeList');
                    } else {
                        $this->message('nodeList', $nodeList);
                    }
                    return $delete;
                } else {
                    $message = $this->message();
                    if(is_null($message)){
                        $message = $this->message(new stdClass());
                    }
                    $this->object_set($type, $attribute, $this->message());
                }
            } else {
                if(is_string($type)){
                    return $this->object_get($type, $this->message());
                } else {
                    $this->setMessage($type);
                    return $this->getMessage();
                }
            }
        }
        return $this->getMessage();
    }

    private function setMessage($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            if(is_object($this->message)){
                foreach($attribute as $key => $value){
                    $this->message->{$key} = $value;
                }
            }
            elseif(is_array($this->message)){
                foreach($attribute as $key => $value){
                    $this->message[$key] = $value;
                }
            } else {
                $this->message = $attribute;
            }
        } else {
            if(is_object($this->message)){
                $this->message->{$attribute} = $value;
            }
            elseif(is_array($this->message)) {
                $this->message[$attribute] = $value;
            }
        }
    }

    private function getMessage($attribute=null){
        if($attribute === null){
            return $this->message;
        }
        if(is_object($this->message)){
            if(isset($this->message->{$attribute})){
                return $this->message->{$attribute};
            } else {
                return false;
            }
        }
        elseif(is_array($this->message)){
            if(isset($this->message[$attribute])){
                return $this->message[$attribute];
            } else {
                return false;
            }
        }
    }

    private function deleteMessage($attribute=null){
        return $this->object_delete($attribute, $this->message());
    }

    public function permission($type=null, $permission=null){
        switch($type){
            case 'read':
                return $this->read_permission();
            break;
            case 'has':
                return $this->has_permission($permission);
            break;
            default:
                trigger_error('unknown permission type.');
            break;
        }
    }

    private function read_permission($counter=5){
        $call = explode('\\', get_called_class());
        if(count($call) > 5 && $counter > 4){
            $count = 5;
            $class = array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . 'Permission';
        }
        elseif(count($call) > 4 && $counter > 3){
            $count = 4;
            $class = array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . 'Permission';
        }
        elseif(count($call) > 3 && $counter > 2){
            $count = 3;
            $class = array_shift($call) . '\\' . array_shift($call) . '\\' . array_shift($call) . '\\' . 'Permission';
        }
        elseif(count($call) > 2 && $counter > 1){
            $count = 2;
            $class = array_shift($call) . '\\' . array_shift($call) . '\\' . 'Permission';
        }
        elseif(count($call) > 1){
            $count = 1;
            $class = array_shift($call) . '\\' . 'Permission';
        } else {
            return false;
        }
        if($counter < 1){
            return false;
        }
        $selector = implode('.', $call);
        $selector = strtolower($selector);
        $read = $this->read($class);
        if(empty($read)){
            return $this->read_permission(--$count);
        }
        return $selector;
    }

    private function has_permission($selector=''){

        $rule = $this->data('permission.' . $selector . '.rule');
        $group = $this->data('permission.' . $selector . '.group');

        if(is_array($rule)){
            $ruleList = $rule;
        } else {
            $ruleList = explode(',', $rule);
        }
        if(is_array($group)){
            $groupList = $group;
        } else {
            $groupList = explode(',', $group);
        }
        $user_ruleList = $this->object($this->session('user.rule'),'array');
        $user_groupList = $this->object($this->session('user.group'), 'array');

        foreach($ruleList as $rule){
            $rule = trim(strtolower($rule));
            if(is_array($user_ruleList)){
                foreach($user_ruleList as $user_rule){
                    $user_rule = trim(strtolower($user_rule));
                    if($rule == $user_rule){
                        return true;
                    }
                }
            }
        }
        foreach($groupList as $group){
            if(is_array($user_groupList)){
                $group = trim(strtolower($group));
                foreach($user_groupList as $user_group){
                    $user_group = trim(strtolower($user_group));
                    if($group == $user_group){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function mail(){
        if(get_class($this->mail) != 'Priya\Module\Mail'){
            $this->mail = new Mail($this->data());
        }
        return $this->mail;
    }

    public function autoload($autoload=null){
        if($autoload !== null){
            $this->setAutoload($autoload);
        }
        return $this->getAutoload();
    }

    private function setAutoload($autoload=''){
        $this->autoload = $autoload;
    }

    private function getAutoload(){
        return $this->autoload;
    }

    public function debug($output='',$isExport=false,$isDie=false){
        if($this->handler()->contentType() == Handler::CONTENT_TYPE_JSON){
            echo $this->object($output, 'json');
        } else {
            echo PHP_EOL;
            if(
                in_array($output, array(
                    '***',
                    '!!!',
                    '---',
                    '+++',
                    '___',
                    '===',
                    '^^^',
                    '$$$',
                    '###',
                    '@@@',
                    '%%%',
                    '&&&'
                ))
            ){
                for($i=0; $i< 60; $i++){
                    echo substr($output, 0, 1);
                }
            } else {
                echo '------------------------------------------------------------';
            }
            echo PHP_EOL;
            if(
                !in_array($output, array(
                    '***',
                    '!!!',
                    '---',
                    '+++',
                    '___',
                    '===',
                    '^^^',
                    '$$$',
                    '###',
                    '@@@',
                    '%%%',
                    '&&&'
                ))
            ){
                if(empty($isExport)){
                    var_dump($output);
                } else {
                    var_export($output);
                }
                echo PHP_EOL;
                echo '------------------------------------------------------------';
                echo PHP_EOL;
            }
        }
        if(!empty($isDie)){
            die;
        }

    }
}