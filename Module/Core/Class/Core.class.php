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
use Priya\Application;
use Priya\Module\Core\Object;

class Core {
    const FILE = __FILE__;

    private $cwd;
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
            if(isset($exception->xdebug_message)){
                echo $exception->xdebug_message;
            } else {
                var_dump($exception);
                die;
            }
        }
    }

    public static function handler_error($number, $message, $file='', $line=null, $context=array()){
        if($number == 2){
            return;
        }
        $error = array();
        $error['number'] = $number;
        $error['message'] = $message;
        $error['file'] = $file;
        $error['line'] = $line;
        $error['context'] = $context;
        var_dump($error);
        die;

        /*
        $handler = new Handler();
        if($handler->contentType() == Handler::CONTENT_TYPE_JSON){
            echo Core::object($error, 'json');
            return true;
        } else {
            var_dump($error);
            die;
        }
        */
    }

    public static function sentence($sentence=''){
        $sentence= explode('.', $sentence);
        foreach ($sentence as $nr => $part){
            $sentence[$nr] = ucfirst($part);
        }
        $sentence = implode('.', $sentence);
        $sentence= explode('!', $sentence);
        foreach ($sentence as $nr => $part){
            $sentence[$nr] = ucfirst($part);
        }
        $sentence = implode('!', $sentence);
        $sentence= explode('?', $sentence);
        foreach ($sentence as $nr => $part){
            $sentence[$nr] = ucfirst($part);
        }
        $sentence = implode('?', $sentence);
        return $sentence;
    }

    public static function mtime(){
        return filemtime(get_called_class()::FILE);
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
                $value = null;
            }
            return false;
        }
    }

    protected function cwd($cwd=''){
        if(!empty($cwd)){
            $this->cwd = $cwd;
        }
        elseif(empty($this->cwd)){
            $this->cwd = getcwd();
        }
        return $this->cwd;
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
        $module = ltrim(str_replace('\\', Application::DS, $module),  Application::DS);
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
            $this->header('Location: '.$url);
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
                    $delete = $this->deleteMessage($attribute);
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
            if($autoload == 'delete' || $autoload == 'remove'){
                $this->setAutoload(null);
            } else {
                $this->setAutoload($autoload);
            }
        }
        return $this->getAutoload();
    }

    private function setAutoload($autoload=''){
        $this->autoload = $autoload;
    }

    private function getAutoload(){
        return $this->autoload;
    }

    public function debug($output='', $title=null, $isExport=false){
        debug($output, $title, $isExport);
    }

    public function class(){
        $explode = explode('\\', get_called_class());
        return end($explode);
    }

    public function namespace(){
        $explode = explode('\\', get_called_class());
        array_pop($explode);
        if(empty($explode)){
            return '';
        }
        return implode('\\', $explode);
    }

    public function header($string='', $http_response_code=null, $replace=true){
        $handler = $this->handler();
        if(empty($handler)){
            $this->handler(new Handler());
        }
        $this->handler()->header($string, $http_response_code, $replace);
    }

    public static function object($input='', $output='object',$type='root'){
        if(is_bool($input)){
            if($output == 'object' || $output == 'json'){
                $data = new stdClass();
                if(empty($input)){
                    $data->false = false;
                } else {
                    $data->true = true;
                }
                if($output == 'json'){
                    $data = json_encode($data);
                }
                return $data;
            }
            elseif($output == 'array') {
                return array($input);
            } else {
                throw new Exception('unknown output in object');
            }
        }
        if(is_null($input)){
            if($output == 'object'){
                return new stdClass();
            }
            elseif($output == 'array'){
                return array();
            }
            elseif($output == 'json'){
                return '{}';
            }
        }
        if(is_array($input) && $output == 'object'){
            return Core::array_object($input);
        }
        if(is_string($input)){
            $input = trim($input);
            if($output=='object'){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    $input = str_replace(
                        array(
                            "\r",
                            "\n"
                        ),
                        array(
                            '',
                            ''
                        ),
                        $input
                        );
                    $json = json_decode($input);
                    if(json_last_error()){
                        new Exception(json_last_error_msg());
                    }
                    return $json;
                }
                elseif(substr($input,0,1)=='[' && substr($input,-1,1)==']'){
                    $input = str_replace(
                        array(
                            "\r",
                            "\n"
                        ),
                        array(
                            '',
                            ''
                        ),
                        $input
                        );
                    $json = json_decode($input);
                    if(json_last_error()){
                        throw new Exception(json_last_error_msg());
                    }
                    return $json;
                }
            }
            elseif(stristr($output, 'json') !== false){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    $input = json_decode($input);
                }
            }
            elseif($output=='array'){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    return json_decode($input, true);
                }
                elseif(substr($input,0,1)=='[' && substr($input,-1,1)==']'){
                    return json_decode($input, true);
                }
            }
        }
        if(stristr($output, 'json') !== false && stristr($output, 'data') !== false){
            $data = str_replace('"', '&quot;',json_encode($input));
        }
        elseif(stristr($output, 'json') !== false && stristr($output, 'line') !== false){
            $data = json_encode($input);
        } else {
            $data = json_encode($input, JSON_PRETTY_PRINT);
        }
        if($output=='object'){
            return json_decode($data);
        }
        elseif(stristr($output, 'json') !== false){
            if($type=='child'){
                return substr($data,1,-1);
            } else {
                return $data;
            }
        }
        elseif($output=='array'){
            return json_decode($data,true);
        } else {
            throw new Exception('unknown output in object');
        }
    }

    public static function array_object($array=array()){
        $object = new stdClass();
        foreach ($array as $key => $value){
            if(is_array($value)){
                $object->{$key} = Core::array_object($value);
            } else {
                $object->{$key} = $value;
            }
        }
        return $object;
    }

    public static function is_nested_array($array=array()){
        $array = (array) $array;
        foreach($array as $key => $value){
            if(is_array($value)){
                return true;
            }
        }
        return false;
    }

    public function explode_single($delimiter=array(), $string='', $internal=array()){
        $result = array();
        if(is_array($delimiter)){
            foreach($delimiter as $nr => $delim){
                if(strpos($string, $delim) === false){
                    continue; //speed... & always >=2
                }
                $tmp = $this->explode_single($delim, $string, $result);
                foreach ($tmp as $tmp_nr => $tmp_value){
                    $result[] = $tmp_value;
                }
            }
            $list = array();
            foreach ($result as $nr => $part){
                $splitted = false;
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) === false){
                        continue; //speed... & always >=2
                    }
                    $tmp = explode($delim, $part);
                    $splitted = true;
                    foreach($tmp as $part_splitted){
                        $list[$part_splitted][] = $part_splitted;
                    }
                }
                if(empty($splitted)){
                    $list[$part][] = $part;
                }
            }
            foreach($list as $part => $value){
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) !== false){
                        unset($list[$part]);
                    }
                }
            }
            $result = array();
            foreach($list as $part => $value){
                $result[] = $part;
            }
            if(empty($result)){
                $result[] = $string;
            }
            return $result;
        } else {
            $result = explode($delimiter, $string);
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }

    public function explode_multi($delimiter=array(), $string='', $limit=array()){
        $result = array();
        if(!is_array($limit)){
            $limit = explode(',', $limit);
            $value = reset($limit);
            if(count($delimiter) > count($limit)){
                for($i = count($limit); $i < count($delimiter); $i++){
                    $limit[$i] = $value;
                }
            }
        }
        foreach($delimiter as $nr => $delim){
            if(isset($limit[$nr])){
                $tmp = explode($delim, $string, $limit[$nr]);
            } else {
                $tmp = explode($delim, $string);
            }
            if(count($tmp)==1){
                continue;
            }
            foreach ($tmp as $tmp_nr => $tmp_value){
                $result[] = $tmp_value;
            }
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }

    public function object_horizontal($verticalArray=array(), $value=null, $return='object'){
        if(empty($verticalArray)){
            return false;
        }
        $object = new stdClass();
        if(is_object($verticalArray)){
            $attributeList = get_object_vars($verticalArray);
            $list = array_keys($attributeList);
            $last = array_pop($list);
            if($value===null){
                $value = $verticalArray->$last;
            }
            $verticalArray = $list;
        } else {
            $last = array_pop($verticalArray);
        }
        if(empty($last)){
            return false;
        }
        foreach($verticalArray as $key => $attribute){
            if(empty($attribute)){
                continue;
            }
            if(!isset($deep)){
                $object->{$attribute} = new stdClass();
                $deep = $object->{$attribute};
            } else {
                $deep->{$attribute} = new stdClass();
                $deep = $deep->{$attribute};
            }
        }
        if(!isset($deep)){
            $object->$last = $value;
        } else {
            $deep->$last = $value;
        }
        if($return=='array'){
            $json = json_encode($object);
            return json_decode($json,true);
        } else {
            return $object;
        }
    }

    public function object_delete($attributeList=array(), $object='', $parent='', $key=null){
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.', ':', '->'), $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(!empty($attributeList) && is_object($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key})){
                    return $this->object_delete($attribute, $object->{$key}, $object, $key);
                } else {
                    unset($object->{$key}); //to delete nulls
                    return false;
                }
            }
        } else {
            unset($parent->{$key});    //unset $object won't delete it from the first object (parent) given
            return true;
        }
    }

    public function object_set($attributeList=array(), $value=null, $object='', $return='child'){
        if(empty($object)){
            return;
        }
        if(is_string($return) && $return != 'child'){
            if($return == 'root'){
                $return = $object;
            } else {
                $return = $this->object_get($return, $object);
            }
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.', ':', '->'), $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(!empty($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key}) && is_object($object->{$key})){
                    if(empty($attribute) && is_object($value)){
                        foreach($value as $value_key => $value_value){
                            if(isset($object->$key->$value_key)){
                                //                                 unset($object->$key->$value_key);   //so sort will happen, request will tak forever and apache2 crashes needs reboot apache2
                            }
                            $object->{$key}->{$value_key} = $value_value;
                        }
                        return $object->{$key};
                    }
                    return $this->object_set($attribute, $value, $object->{$key}, $return);
                }
                elseif(is_object($attribute)){
                    $object->{$key} = new stdClass();
                    return $this->object_set($attribute, $value, $object->{$key}, $return);
                } else {
                    $object->{$key} = $value;
                }
            }
        }
        if($return == 'child'){
            return $value;
        }
        return $return;
    }

    public function object_get($attributeList=array(), $object=''){
        if(empty($object)){
            return $object;
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.',':', '->'), $attributeList);

            foreach($attributeList as $nr => $attribute){
                if(empty($attribute)){
                    unset($attributeList[$nr]);
                }
            }
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(empty($attributeList)){
            return $object;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key)){
                continue;
            }
            if(isset($object->{$key})){
                return $this->object_get($attributeList->{$key}, $object->{$key});
            }
        }
        return null;
    }

    public static function object_merge(){
        $objects = func_get_args();
        $main = array_shift($objects);
        if(empty($main) && !is_array($main)){
            $main = new stdClass();
        }
        foreach($objects as $nr => $object){
            if(is_array($object)){
                foreach($object as $key => $value){
                    if(is_object($main)){
                        throw new Exception('cannot merge an array with an object');
                    }
                    if(!isset($main[$key])){
                        $main[$key] = $value;
                    } else {
                        if(is_array($value) && is_array($main[$key])){
                            $main[$key] = Core::object_merge($main[$key], $value);
                        } else {
                            $main[$key] = $value;
                        }
                    }
                }
            }
            elseif(is_object($object)){
                foreach($object as $key => $value){
                    if((!isset($main->{$key}))){
                        $main->{$key} = $value;
                    } else {
                        if(is_object($value) && is_object($main->{$key})){
                            $main->{$key} = Core::object_merge($main->{$key}, $value);
                        } else {
                            $main->{$key} = $value;
                        }
                    }
                }
            }
        }
        return $main;
    }

    public function array_trim($array=array(), $split=',', $trim=null){
        if(is_string($array)){
            $array = explode($split, $array);
        }
        foreach($array as $key => $value){
            if(is_array($value)){
                $array[$key] = $this->array_trim($value, $split, $trim);
            } else {
                if($trim === null){
                    $value = trim($value);
                } else {
                    $value = trim($value, $trim);
                }
                if(empty($value)){
                    unset($array[$key]);
                    continue;
                }
                $array[$key] = $value;
            }
        }
        return $array;
    }
}