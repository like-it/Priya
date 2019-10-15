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
use Priya\Module\File\Dir;

class Core {
    const FILE = __FILE__;
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;
    const NAME_DECENDENT = Core::NAME;

    const DS = Application::DS;
    const ATTRIBUTE_EXPLODE = [
        '.',
        ':',
        '->'
    ];
    const TEMP =  Application::DS . 'tmp' . Application::DS . 'priya' . Application::DS;

    const SHELL_DETACHED = 'detached';
    const SHELL_PROCESS = 'process';
    const SHELL_PROCESS_END = 'end';
    const SHELL_PROCESS_DELIMITER = ';';

    const EXCEPTION_PERMISSION_TYPE = 'unknown permission type.';
    const EXCEPTION_MERGE_ARRAY_OBJECT = 'cannot merge an array with an object.';
    const EXCEPTION_KEY_ARRAY_OBJECT = 'cannot create object from array with empty key.';
    const EXCEPTION_OBJECT_OUTPUT = 'unknown output in object.';

    const OUTPUT_MODE_IMPLICIT = 'implicit';
    const OUTPUT_MODE_EXPLICIT = 'explicit';
    const OUTPUT_MODE_DEFAULT = CORE::OUTPUT_MODE_EXPLICIT;

    const OUTPUT_MODE = [
        Core::OUTPUT_MODE_IMPLICIT,
        Core::OUTPUT_MODE_EXPLICIT,
    ];

    const MODE_INTERACTIVE = CORE::OUTPUT_MODE_IMPLICIT;
    const MODE_PASSIVE = CORE::OUTPUT_MODE_EXPLICIT;

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
    public static function decode($html=''){
        return htmlspecialchars_decode(
            $html,
            ENT_NOQUOTES
        );
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
        $class = get_called_class();
        $file = $class::FILE;
        return filemtime($file);
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
            if(is_object($route) && is_a($route, 'Priya\Module\Route')){
                $this->setRoute($route);
                return $this->getRoute();
            }
            elseif(is_object($route)){
                $debug = debug_backtrace(true);
                var_dump($debug);
                die;
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

    /*
    public static function is($type='typeof', $subject=null, $value=null){
        switch(strtolower($type)){
            case 'typeof' :
                return is_a($subject, $value);
        }
    }
    */

    public function parameter($parameter, $offset=0){
        $data = $this->request('data');
        $result = null;
        $value = null;
        if(is_string($parameter) && stristr($parameter, '\\')){
            //classname adjustment
            $parameter = basename(str_replace('\\', '//', $parameter));
        }
        if(is_numeric($parameter)){
            if(isset($data[$parameter])){
                $param = ltrim($data[$parameter], '-');
                $result = $param;
            } else {
                $result = null;
            }
        } else {
            if(
                is_array($data) ||
                is_object($data)
            ){
                foreach($data as $key => $param){
                    $param = ltrim($param, '-');
                    $param = rtrim($param);
                    $tmp = explode('=', $param);
                    if(count($tmp) > 1){
                        $param = array_shift($tmp);
                        $value = implode('=', $tmp);
                    }
                    if(strtolower($param) == strtolower($parameter)){
                        if($offset !== 0){
                            if(isset($data[($key + $offset)])){
                                $value = rtrim(ltrim($data[($key + $offset)], '-'));
                            } else {
                                $result = null;
                                break;
                            }
                        }
                        if(isset($value) && $value !== null){
                            $result = $value;
                        } else {
                            $result = true;
                            return $result;
                        }
                        break;
                    }
                    $value = null;
                }
            }
        }
        if($result === null || is_bool($result)){
            return $result;
        }
        return trim($result);
    }

    protected function cwd($cwd=''){
        if(!empty($cwd)){
            $this->cwd = $cwd;
        }
        elseif(empty($this->cwd)){
            $this->cwd = getcwd() . Application::DS;
            if($this->cwd == Application::DS . Application::DS){
                $this->cwd = Application::DS;
            }
        }
        return $this->cwd;
    }

    //make static, didnt i do that already somewhere?
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

    //make static
    public function module(){
        $module = explode(__NAMESPACE__, get_called_class(), 2);
        if(count($module) == 2){
            array_shift($module);
        }
        $module = implode('', $module);
        $module = ltrim(str_replace('\\', Application::DS, $module),  Application::DS);
        return $module;
    }

    //make static
    public function dom_class($class=''){
        $class = strtolower($class);
        $class = str_replace(array('\\', '/'), '-', $class);
        return $class;
    }
    //make static
    public static function refresh($object, $url=''){
        /* disabled error & message */
        /*
        $error = $this->error();
        if(!empty($error)){
            $this->session('error', $error);
        }
        $message = $this->message();
        if(!empty($message)){
            $this->session('message', $message);
        }
        */
        /*
        $post = $this->post();
        if(!empty($post)){
            $this->session('post', $post);
        }
        */
        $contentType = $object->request('contentType');
        if($contentType == Handler::CONTENT_TYPE_JSON){
            $output = new stdClass();
            $output->refresh = $url;
            echo json_encode($output);
            die;
        } else {
            $object->header('Location: '.$url);
            die;
        }
    }

    //remove, old method?
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

    //remove old method?
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

    //remove old method?
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

    //remove old method ?
    private function deletePost($attribute=null){
        return $this->object_delete($attribute, $this->post());
    }

    public static function error($object, $error){
        if(is_string($object)){
            $debug = debug_backtrace(true);
            var_dump($debug);
            die;
        }
        $list = $object->data('priya.error');
        if(empty($list)){
            $list = [];
        }
        $list[] = $error;
        $object->data('priya.error', $list);
        return $object; //bug in php, converted to stdClass
    }

    //restore function for input fields


    /*
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

    //restore for input fields
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

    //restore for input fields
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

    //restore for input fields
    private function deleteError($attribute=null){
        return $this->object_delete($attribute, $this->error());
    }
    */

    //restore for input fields message system, or do i do it different
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

    //restore for message
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
    //restore for message system
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

    //restore for message system
    private function deleteMessage($attribute=null){
        return $this->object_delete($attribute, $this->message());
    }

    //restore for permission system
    public function permission($type=null, $permission=null){
        switch($type){
            case 'read':
                return $this->read_permission();
            break;
            case 'has':
                return $this->has_permission($permission);
            break;
            default:
                throw new Exception(Core::EXCEPTION_PERMISSION_TYPE);
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
        //read unavailable here, move...
        $read = $this->read($class);
        if(empty($read)){
            return $this->read_permission(--$count);
        }
        return $selector;
    }


    private function has_permission($selector=''){
        //data unavailable here, move...
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

    //object autoload statics ?
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
    public static function debug($output='', $title=null, $isExport=false){
        debug($output, $title, $isExport);
    }
    public static function php_class(){
        $explode = explode('\\', get_called_class());
        return end($explode);
    }
    public static function php_namespace(){
        $explode = explode('\\', get_called_class());
        array_pop($explode);
        if(empty($explode)){
            return '';
        }
        return implode('\\', $explode);
    }

    //might still be cheap with object static
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
                throw new Exception(Core::EXCEPTION_OBJECT_OUTPUT);
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
                    /* why replace newlines ?
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
                    */
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
            throw new Exception(Core::EXCEPTION_OBJECT_OUTPUT);
        }
    }
    public static function array_object($array=array()){
        $object = new stdClass();
        foreach ($array as $key => $value){
            /** slows down...
            if(empty($key)){
                throw new Exception(EXCEPTION_KEY_ARRAY_OBJECT);
            }
            **/
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
        foreach($array as $value){
            if(is_array($value)){
                return true;
            }
        }
        return false;
    }
    //make static
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

    //make static too (still cheap and not re-usable of missing documentation, read code
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

    //make static, still cheap
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
        if(empty($last) && $last != '0'){
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
            $attributeList = $this->explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
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
    public static function object_is_empty($object=null){
        if(!is_object($object)){
            return true;
        }
        $is_empty = true;
        foreach ($object as $attribute => $value){
            $is_empty = false;
            break;
        }
        return $is_empty;
    }
    public function object_has($attributeList=array(), $object=''){
        if(Core::object_is_empty($object)){
            if(empty($attributeList)){
                return true;
            }
            return false;
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
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
            return true;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key)){
                continue;
            }
            if(property_exists($object,$key)){

                var_dump($attributeList);
                $get = $this->object_has($attributeList->{$key}, $object->{$key});
                var_dump($key);
                var_dump($get);
                if($get === false){
                    return false;
                }

                return true;
            }
        }
        return false;
    }
    public function object_get($attributeList=array(), $object=''){
        if(Core::object_is_empty($object)){
            if(empty($attributeList)){
                return $object;
            }
            return null;
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(Core::ATTRIBUTE_EXPLODE, $attributeList);
            foreach($attributeList as $nr => $attribute){
                if(empty($attribute) && $attribute != '0'){
                    unset($attributeList[$nr]);
                }
            }
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }

        // die;
        if(empty($attributeList)){
            return $object;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key) && $key != 0){
                continue;
            }
            if(isset($object->{$key})){
                return $this->object_get($attributeList->{$key}, $object->{$key});
            }
        }
        return null;
    }
    public static function object_extend(){
        $objects = func_get_args();
        $main = array_shift($objects);
        foreach($objects as $nr => $object){
            foreach($object as $attribute => $value){
                if(isset($main->{$attribute})){
                    continue;
                }
                $main->{$attribute} = $value;
            }
        }
        return $main;
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
                        throw new Exception(Core::EXCEPTION_MERGE_ARRAY_OBJECT);
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
                            $main->{$key} = Core::object_merge(clone $main->{$key}, clone $value);
                        } else {
                            $main->{$key} = $value;
                        }
                    }
                }
            }
        }
        return $main;
    }

    //make static
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
    public static function copy($copy=null){
        return unserialize(serialize($copy));
    }
    public static function key_last($record=[]){
        if(empty($record)){
            return;
        }
        $end = end($record);
        $key = key($record);
        return $key;
    }
    public static function key_first($record=[]){
        if(empty($record)){
            return;
        }
        $end = reset($record);
        $key = key($record);
        return $key;
    }
    public static function array_shift(&$array, $preserve_keys=false){
        if($preserve_keys === true){
            $reset = reset($array);
            $key = key($array);
            unset($array[$key]);
            return $reset;
        } else {
            return array_shift($array);
        }
    }

    public static function detach($command){
        return Core::execute($command, $output, Core::SHELL_DETACHED);
    }

    public static function async($object, $command){
        if(stristr($command, '&') === false){
            $command .= ' &';
        }
        return Core::execute($object, $command, $output, Core::SHELL_PROCESS);
    }

    public static function execute($object, $command, &$output=[], $type=null){
        if($output === null){
            $output = [];
        }
        $result = [
            'pid' => getmypid()
        ];

        $hash = $object->data('priya.application.binary.command');
        /**
         * hash, currently main thread needs also the command which will be executed
         *
         */
        $hash = $hash . '-' . $command;
        $hash = sha1($hash);

        $dir_output = Core::TEMP . 'Output' . Application::DS;
        $dir_pid = Core::TEMP . 'Pid' .  Application::DS;
        Dir::create($dir_output, Dir::CHMOD);
        Dir::create($dir_pid, Dir::CHMOD);

        $result['dir_pid'] =  $dir_pid;
        $result['dir_output'] = $dir_output;
        if(
            in_array(
                $type,
                [
                    Core::SHELL_DETACHED,
                    Core::SHELL_PROCESS
                ]
                )
            ){
                $pid = pcntl_fork();
                switch($pid) {
                    // fork errror
                    case -1 :
                        return false;
                    case 0 :
                        //in child process
                        //create a seperate process to execute another process (async);


                        $url_output = $dir_output . $hash;
                        $url_pid = $dir_pid . $hash;
                        $exec = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $url_output, $url_pid);
                        exec($exec);
                        $read = trim(File::read($url_pid));
                        File::write(
                            $url_pid,
                            $read .
                            Core::SHELL_PROCESS_DELIMITER .
                            $object->data('time.start') .
                            Core::SHELL_PROCESS_DELIMITER .
                            microtime(true) .
                            Core::SHELL_PROCESS_DELIMITER .
                            Core::SHELL_PROCESS_END .
                            PHP_EOL
                        );
                        die;
                        if($type != Core::SHELL_PROCESS){
                            //                         echo implode(PHP_EOL, $output) . PHP_EOL;
                        }
                        $output = [];
                        exit();
                    default :
                        if($type == Core::SHELL_PROCESS){
                            pcntl_waitpid(0, $status, WNOHANG);
                            $status = pcntl_wexitstatus($status);
                            $child = [
                                'status' => $status,
                                'pid' => $pid,
                                'hash' => $hash,
                            ];
                            $result['child'] = $child;
                            return $result;
                        }
                        // main process (parent)
                        while (pcntl_waitpid(0, $status) != -1) {
                            //add max execution time here / time outs etc..
                            $status = pcntl_wexitstatus($status);
                            $child = [
                                'status' => $status,
                                'pid' => $pid,
                                'hash' => $hash
                            ];
                            $result['child'] = $child;
                        }
                }
                return $result;
        } else {
            return exec($command, $output);
        }
    }

    public static function execute_shell($command, &$output=[], $type=null){
        if($output === null){
            $output = [];
        }
        $result = [
            'pid' => getmypid()
        ];
        if(
            in_array(
                $type,
                [
                    Core::SHELL_DETACHED,
                    Core::SHELL_PROCESS
                ]
            )
        ){
            $pid = pcntl_fork();
            switch($pid) {
                // fork errror
                case -1 :
                    return false;
                case 0 :
                    //in child process
                    //create a seperate process to execute another process (async);
                    exec($command, $output);
                    if($type != Core::SHELL_PROCESS){
//                         echo implode(PHP_EOL, $output) . PHP_EOL;
                    }
                    $output = [];
                    exit();
                default :
                    if($type == Core::SHELL_PROCESS){
                        pcntl_waitpid(0, $status, WNOHANG);
                        $status = pcntl_wexitstatus($status);
                        $child = [
                            'status' => $status,
                            'pid' => $pid
                        ];
                        $result['child'] = $child;
                        return $result;
                    }
                    // main process (parent)
                    while (pcntl_waitpid(0, $status) != -1) {
                        //add max execution time here / time outs etc..
                        $status = pcntl_wexitstatus($status);
                        $child = [
                            'status' => $status,
                            'pid' => $pid
                        ];
                        $result['child'] = $child;
                    }
            }
            return $result;
        } else {
            return exec($command, $output);
        }
    }

    public static function output_mode($mode = null){
        if(!in_array($mode, Core::OUTPUT_MODE)){
            $mode = Core::OUTPUT_MODE_DEFAULT;
        }
        switch($mode){
            case  Core::MODE_INTERACTIVE :
                ob_implicit_flush(true);
                ob_end_flush();
            break;
            case  Core::MODE_INTERACTIVE :
                ob_implicit_flush(false);
                ob_end_flush();
            break;
            default :
                ob_implicit_flush(false);
                ob_end_flush();
        }
    }

    public static function interactive(){
        return Core::output_mode(Core::MODE_INTERACTIVE);
    }

    public static function passive(){
        return Core::output_mode(Core::MODE_PASSIVE);
    }

    public static function uuid(){
        $data = openssl_random_pseudo_bytes(16);
        assert(strlen($data) == 16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function pid($parent=false){
        if($parent === true){
            return posix_getppid();
        }
        return getmypid();
    }

    public static function username(){
        exec('whoami', $output);
        return implode(PHP_EOL, $output);
    }

    public static function binary(){
        $pid = Core::pid(true);
        exec('ps -p ' . $pid .' -u', $output);

        if(isset($output[0])){
            $row_header = $output[0];
        }
        if(isset($output[1])){
            $row = $output[1];
        } else {
            var_dump('parent');
            die;
        }
        $row_header = str_replace('     ', '    ', $row_header);
        $row_header = str_replace('    ', '   ', $row_header);
        $row_header = str_replace('   ', '  ', $row_header);
        $row_header = str_replace('  ', ' ', $row_header);

        $row = str_replace('     ', '    ', $row);
        $row = str_replace('    ', '   ', $row);
        $row = str_replace('   ', '  ', $row);
        $row = str_replace('  ', ' ', $row);

        $header = [];
        $record = [];
        $explode = explode(' ', $row_header);
        foreach($explode as $nr => $column){
            if($column == ''){
                continue;
            }
            $header[$nr] = strtolower($column);
        }
        $explode = explode(' ', $row);
        $record[$header[0]] = $explode[0];
        $record[$header[2]] = $explode[2];
        $record[$header[3]] = $explode[3];
        $record[$header[4]] = $explode[4];
        $record[$header[5]] = $explode[5];
        $record[$header[6]] = $explode[6];
        $record[$header[7]] = $explode[7];
        $record[$header[9]] = $explode[8];
        $record[$header[10]] = $explode[9];
        $record[$header[11]] = $explode[10];
        $record['tree'] = [];
        $user_bin = null;
        $bin = null;
        for($i = 10; $i < count($explode); $i++){
            if(!isset($explode[$i])){
                continue;
            }
            if(stristr($explode[$i], '/usr/bin/') !== false){
                $user_bin = $explode[$i];
            }
            elseif(stristr($explode[$i], '/bin/') !== false){
                $bin = $explode[$i];
            }
            if($i > 10){
                $record['tree'][] = $explode[$i];
            }
        }
        $record[$header[12]] = implode(' ', $record['tree']);
        $user = $record['user'];
        $record['user'] = [];
        $record['user']['name'] = $record['user'];
        $record['user']['execute'] = File::basename($user_bin);
        $record['execute'] = File::basename($bin);
        return Core::object($record);
    }
}