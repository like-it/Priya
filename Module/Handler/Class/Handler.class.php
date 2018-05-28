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

class Handler extends \Priya\Module\Core\Data{
    const CONTENT_TYPE_CSS = 'text/css';
    const CONTENT_TYPE_HTML = 'text/html';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_CLI = 'text/cli';
    const CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    const METHOD_CLI = 'CLI';
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    const SCHEME_HTTP = 'http';
    const SCHEME_HTTPS = 'https';

    private $request;
    private $file;
    private $contentType;
    private $method;

    public function __construct($handler=null, $route=null, $data=null){
        $this->data($handler);
        $this->input('create');
        $this->contentType('create');
        $this->method('create');
        $this->lastModified('create');
        $this->referer('create');
        $this->file('create');
        $this->microtime('create');
        $this->key('create');
    }

    public function key($attribute=null){
        if($attribute == 'create'){
            $request = $this->request();
            unset($request->{'last-modified'});
            unset($request->time);
            $key = sha1(Core::object($request, 'json'));
            $this->request('key', $key);
        }
    }


    public function microtime($attribute=null){
           if($attribute == 'create'){
                if(
                    isset($_SERVER) &&
                    isset($_SERVER['REQUEST_TIME_FLOAT'])
                ){
                    $this->request('time', $_SERVER['REQUEST_TIME_FLOAT']);
                } else {
                    $this->request('time', microtime(true));
                }
           }
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
                    return $value;
                } else {
                    $this->object_set($attribute, $value, $this->request());
                    return $value;
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
        }
        elseif(isset($this->request->{$attribute})){
            return $this->request->{$attribute};
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

    public function file($attribute=null, $value=null){
        if($attribute !== null){
            if($attribute == 'create'){
                return $this->createFile($value);
            }
            if($value !== null){
                if($attribute=='delete'){
                    return $this->deleteFile($value);
                } else {
                    $this->object_set($attribute, $value, $this->file());
                }
            } else {
                if(is_string($attribute)){
                    return $this->object_get($attribute, $this->file());
                } else {
                    $this->setFile($attribute);
                    return $this->getFile();
                }
            }
        }
        return $this->getFile();
    }

    private function setFile($attribute='', $value=null){
        if(is_array($attribute) || is_object($attribute)){
            $this->file = $attribute;
        } else {
            if(is_object($this->file)){
                $this->file->{$attribute} = $value;
            } else {
                $this->file[$attribute] = $value;
            }

        }
    }

    private function getFile($attribute=null){
        if($attribute === null){
            if(is_null($this->file)){
                $this->file = new stdClass();
            }
            return $this->file;
        }
        if(isset($this->file[$attribute])){
            return $this->file[$attribute];
        }
        elseif(isset($this->file->{$attribute})){
            return $this->file->{$attribute};
        } else {
            return false;
        }
    }

    private function deleteFile($attribute=''){
        return $this->object_delete($attribute, $this->file());
    }

    private function createFile(){
        $nodeList = array();
        if(isset($_FILES)){
            foreach ($_FILES as $category => $list){
                if(is_array($list)){
                    foreach($list as $attribute => $subList){
                        if(is_array($subList)){
                            foreach ($subList as $nr => $value){
                                $nodeList[$nr][$attribute] = $value;
                            }
                        } else {
                            $nodeList[] = $list;
                            break;
                        }
                    }
                }
            }
        }
        return $this->file($nodeList);
    }

    public function lastModified(){
        $this->request('last-modified', gmdate('D, d M Y H:i:s T'));
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
        $contentType = Handler::CONTENT_TYPE_HTML;
        if(isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == Handler::CONTENT_TYPE_JSON){
            $contentType = Handler::CONTENT_TYPE_JSON;
        }
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            $contentType = Handler::CONTENT_TYPE_JSON;
        }
        if(isset($_SERVER['HTTP_ACCEPT']) && stristr($_SERVER['HTTP_ACCEPT'], Handler::CONTENT_TYPE_CSS)){
            $contentType = Handler::CONTENT_TYPE_CSS;
        }
        $host = $this->host();
        if(empty($host) || $host == 'http:///'){
            $contentType = Handler::CONTENT_TYPE_CLI;
        }
        $ct = $this->request('contentType');
        if($ct){
            $contentType = $ct;
        } else {
            $request = $this->request('request');
            $tmp = explode('.', $request);
            $ext = strtolower(end($tmp));

            $allowed_contentType = $this->data('priya.contentType');
            if(isset($allowed_contentType->{$ext})){
                $contentType = $allowed_contentType->{$ext};
            }
        }
        $this->request('contentType',$contentType);
        return $this->contentType($contentType);
    }

    public function method($method=null){
        if($method !== null){
            if($method == 'create'){
                return $this->createMethod();
            } else {
                $this->setMethod($method);
            }
        }
        return $this->getMethod();
    }

    private function setMethod($method=''){
        $this->method = $method;
    }

    private function getMethod(){
        return $this->method;
    }

    private function createMethod(){
        if(isset($_SERVER['REQUEST_METHOD'])){
            $method = $_SERVER['REQUEST_METHOD'];
        } else{
            $contentType = $this->contentType();
            if(stristr($contentType, strtolower(Handler::METHOD_CLI))){
                $method = Handler::METHOD_CLI;
            } else {
                $method = Handler::METHOD_GET;
            }
        }
        return $this->method($method);
    }

    public function input(){
        global $argc, $argv;

        $node = array();
        $input =
            htmlspecialchars(
                htmlspecialchars_decode(
                    implode(
                        '',
                        file('php://input')
                    ),
                    ENT_NOQUOTES
                ),
                ENT_NOQUOTES,
                'UTF-8'
            )
        ;
        $request = $_REQUEST;
        if(!empty($request) && !empty($_SERVER['QUERY_STRING'])){
            $query = $_SERVER['QUERY_STRING'];
            $temp = explode('&', $query);
            $request = array();
            foreach($temp as $nr => $value){
                $explode = explode('=', $value, 2);
                if(isset($explode[1])){
                    $request[$explode[0]] = $explode[1];
                } else {
                    $request[$explode[0]] = '';
                }
            }
        }
        $previous = key($request);
        foreach($request as $key => $value){
            if(empty($value)){
                $request[$previous] .= '&' . $key;
            } else {
                $previous = $key;
            }
        }
        //dropzone extra form field
        if(is_array($_REQUEST)){
            foreach($_REQUEST as $key => $value){
                if(!isset($request[$key]) && isset($value)){
                    $request[$key] = $value;
                }
            }
        }
        if(empty($input) && !empty($request)){
            $input =
                htmlspecialchars(
                    json_encode(
                        array(
                            'nodeList' => array(0 => $request)
                        )
                    ),
                    ENT_NOQUOTES,
                    'UTF-8'
                )
            ;
        }
        elseif(!empty($input) && !empty($request)){
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
                $input->nodeList[] = $request;
                $input = json_encode($input);
            } else {
                $input = $old;
                $input->nodeList = $this->object($old->nodeList, 'array');
                if(!is_array($input->nodeList)){
                    $input->nodeList = (array) $input->nodeList;
                }
                $input->nodeList[] = $request;        //strange but works...
                $input = json_encode($input);
            }
        }
        elseif(!empty($input) && empty($request)){
            $old = json_decode($input);
            if(!isset($old->nodeList)){
                $input = new stdClass();
                $input->nodeList = array();
                foreach($old as $key => $node){
                    if(is_numeric($key)){
                        $input->nodeList[] = $node;
                    } else {
                        $input->nodeList[] = array($key => $node);
                    }
                }
                $input = json_encode($input);
            }
        }
        $data = json_decode($input);
        if(empty($data) && !empty($argv)){
            $attribute = $argv;
            foreach($attribute as $nr => $value){
                if($value=== '""' || $value=== ''){
                    unset($attribute[$nr]);
                    continue;
                }
                $attribute[$nr] = trim($value);
//                 $attribute[$nr] = trim(escapeshellarg($value), '\''); //causes error
            }
            $data = new stdClass();
            $data->nodeList = array();
            $object = new stdClass();
            $object->data =  $attribute;
            $data->nodeList[] = $object;
            $object = new stdClass();
            $object->file =
                Application::DIR .
                Application::DS .
                basename(array_shift($attribute))
            ;
            $data->nodeList[] = $object;
            if(count($argv) >= 1){
                $object = new stdClass();
                $object->request = str_replace('\\','/',array_shift($attribute));
                $data->nodeList[] = $object;
            }
        }
        $this->request('create',$data);
    }

    public function webRoot(){
        if(empty($_SERVER['DOCUMENT_ROOT'])){
            return false;
        }
        return str_replace(
            array('/', '\\'),
            Application::DS,
            $_SERVER['DOCUMENT_ROOT'] . Application::DS
        );
    }

    public static function web($host=''){
        if(empty($host)){
            $host = $_SERVER['HTTP_HOST'];
        }
        if(empty($host)){
            return false;
        }
        $scheme = Handler::scheme();
        return
            $scheme .
            '://' .
            $_SERVER['HTTP_HOST'] .
            '/'
        ;
    }

    public static function domain($host=''){
        if(empty($host)){
            $host = $_SERVER['HTTP_HOST'];
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) >= 2){
            array_pop($explode);
            return array_pop($explode);
        }
        return false;
    }

    public static function subdomain($host=''){
        if(empty($host)){
            $host = $_SERVER['HTTP_HOST'];
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) > 2){
            array_pop($explode);
            array_pop($explode);
            return implode('.', $explode);
        }
        return false;
    }

    public static function extension($host=''){
        if(empty($host)){
            $host = $_SERVER['HTTP_HOST'];
        }
        if(empty($host)){
            return false;
        }
        $explode = explode('.', $host);
        if(count($explode) > 1){
            return array_pop($explode);
        }
        return false;
    }

    public static function scheme(){
        $scheme = Handler::SCHEME_HTTP;
        if(!empty($_SERVER['REQUEST_SCHEME'])){
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } else {
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
                $scheme = Handler::SCHEME_HTTPS;
            }
        }
        return $scheme;
    }

    public function url($url=null, $attribute=null){
        $scheme = $this->scheme();
        if(empty($scheme)){
            return false;
        }
        $url = parent::url($url, $attribute);
        if($url === null){
            if(empty($_SERVER['HTTP_HOST'])){
                return false;
            }
            if(!isset($_SERVER['REQUEST_URI'])){
                return false;
            }
            $url =
            $scheme .
            '://' .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']
            ;
        }
        return parent::url($url, $attribute);
    }

    public function csrf(){
        $session = $this->session();
        if(!empty($session['csrf'])){
            return $session['csrf'];
        }
    }
    public function session($attribute=null, $value=null){
        if($attribute == 'has'){
            return isset($_SESSION);
        }
        elseif($attribute == 'close'){
            session_write_close();
            return;
        }
        if(!isset($_SESSION)){
            session_start();
            $_SESSION['id'] = session_id();
            if(empty($_SESSION['csrf'])){
                $_SESSION['csrf'] =
                    rand(1000,9999) . '-' .
                    rand(1000,9999) . '-' .
                    rand(1000,9999) . '-' .
                    rand(1000,9999)
                ;
            }
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
                            unset(
                                $_SESSION
                                [$value]
                            );
                        break;
                        case 2 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                            );
                        break;
                        case 3 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                            );
                        break;
                        case 4 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                            );
                        break;
                        case 5 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                            );
                        break;
                        case 6 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                                [$tmp[5]]
                            );
                        break;
                        case 7 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                                [$tmp[5]]
                                [$tmp[6]]
                            );
                        break;
                        case 8 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                                [$tmp[5]]
                                [$tmp[6]]
                                [$tmp[7]]
                            );
                        break;
                        case 9 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                                [$tmp[5]]
                                [$tmp[6]]
                                [$tmp[7]]
                                [$tmp[8]]
                            );
                        break;
                        case 10 :
                            unset(
                                $_SESSION
                                [$tmp[0]]
                                [$tmp[1]]
                                [$tmp[2]]
                                [$tmp[3]]
                                [$tmp[4]]
                                [$tmp[5]]
                                [$tmp[6]]
                                [$tmp[7]]
                                [$tmp[8]]
                                [$tmp[9]]
                            );
                        break;
                    }
                    return true;
                } else {
                    if(is_object($value)){
                        $value = $this->object($value, 'array');
                    }
                    switch(count($tmp)){
                        case 1 :
                            $_SESSION
                            [$attribute] = $value;
                        break;
                        case 2 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]] = $value;
                        break;
                        case 3 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]] = $value;
                        break;
                        case 4 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]] = $value;
                        break;
                        case 5 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]] = $value;
                        break;
                        case 6 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]]
                            [$tmp[5]] = $value;
                        break;
                        case 7 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]]
                            [$tmp[5]]
                            [$tmp[6]] = $value;
                        break;
                        case 8 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]]
                            [$tmp[5]]
                            [$tmp[6]]
                            [$tmp[7]] = $value;
                        break;
                        case 9 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]]
                            [$tmp[5]]
                            [$tmp[6]]
                            [$tmp[7]]
                            [$tmp[8]] = $value;
                        break;
                        case 10 :
                            $_SESSION
                            [$tmp[0]]
                            [$tmp[1]]
                            [$tmp[2]]
                            [$tmp[3]]
                            [$tmp[4]]
                            [$tmp[5]]
                            [$tmp[6]]
                            [$tmp[7]]
                            [$tmp[8]]
                            [$tmp[9]] = $value;
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
                    if(
                        isset($_SESSION[$tmp[0]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]];
                    } else {
                        return null;
                    }
                break;
                case 3 :
                    if(
                        isset($_SESSION[$tmp[0]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                        isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]])
                    ){
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
                case 6 :
                    if(
                    isset($_SESSION[$tmp[0]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]];
                    } else {
                        return null;
                    }
                break;
                case 7 :
                    if(
                    isset($_SESSION[$tmp[0]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]];
                    } else {
                        return null;
                    }
                break;
                case 8 :
                    if(
                    isset($_SESSION[$tmp[0]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]];
                    } else {
                        return null;
                    }
                break;
                case 9 :
                    if(
                    isset($_SESSION[$tmp[0]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]][$tmp[8]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]][$tmp[8]];
                    } else {
                        return null;
                    }
                break;
                case 10 :
                    if(
                    isset($_SESSION[$tmp[0]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]][$tmp[8]]) &&
                    isset($_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]][$tmp[8]][$tmp[9]])
                    ){
                        return $_SESSION[$tmp[0]][$tmp[1]][$tmp[2]][$tmp[3]][$tmp[4]][$tmp[5]][$tmp[6]][$tmp[7]][$tmp[8]][$tmp[9]];
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
                    $result = @setcookie($value, null, 0, "/"); //ends at session
                    if(!empty($result) && $this->method() == Handler::METHOD_CLI){
                        unset($_COOKIE[$value]);
                    }
                    return $result;
                } else {
                    if($duration === null){
                        $duration = 60*60*24*365*2; // 2 years
                    }
                    $result = @setcookie($attribute, $value, time() +     $duration, "/");
                    if(!empty($result) && $this->method() == Handler::METHOD_CLI){
                        $_COOKIE[$attribute] = $value;
                    }
                }
                if(isset($_COOKIE[$attribute])){
                    return $_COOKIE[$attribute];
                } else {
                    return null;
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
        return false;
    }

    public static function host($include_scheme = true){
        if(isset($_SERVER['HTTP_HOST'])){
            $domain = $_SERVER['HTTP_HOST'];
        }
        elseif(isset($_SERVER['SERVER_NAME'])){
            $domain = $_SERVER['SERVER_NAME'];
        } else {
            $domain = '';
        }
        if($include_scheme) {
            $scheme = Handler::scheme();
            $host = '';
            if(isset($scheme) && isset($domain)){
                $host = $scheme . '://' . $domain . '/';
            }
        } else {
            $host = $domain;
        }
        return $host;
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

    public function header($string='', $http_response_code=null, $replace=true){
        if(empty($string)){
            return;
        }
        if($http_response_code){
            header($string, $replace, $http_response_code);
        } else {
            header($string, $replace);
        }
    }

    public function since($mtime=''){
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
            if(strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime){
                $this->header('HTTP/1.1 304 Not Modified');
                $this->header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', $mtime));
                $this->header('If-Modified-Since: ' . $_SERVER['HTTP_IF_MODIFIED_SINCE']);
                exit;
            }
        }
    }
}