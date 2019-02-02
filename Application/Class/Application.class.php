<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya;

use stdClass;
use Exception;
use Priya\Module\File;
use Priya\Module\Handler;
use Priya\Module\Core\Parser;
use Priya\Module\Data;
use Priya\Module\File\Cache;
use Priya\Module\File\Dir;

class Application extends Parser {
    const DS = DIRECTORY_SEPARATOR;
    const DIR = __DIR__;
    const PRIYA = 'Priya';
    const ENVIRONMENT = 'development';
    const MODULE = 'Module';
    const TEMPLATE = 'Template';
    const PLUGIN = 'Plugin';
    const PAGE = 'Page';
    const DATA = 'Data';
    const CACHE = 'Cache';
    const OBJECT = 'Object';
    const BACKUP = 'Backup';
    const PROCESSOR = 'Processor';
    const RESTORE = 'Restore';
    const UPDATE = 'Update';
    const VENDOR = 'Vendor';
    const TEMP = 'Temp';
    const PUBLIC_HTML = 'Public';
    const HOST = 'Host';
    const CSS = 'Css';
    const JAVASCRIPT = 'Javascript';
    const CONFIG = 'Config.json';
    const CUSTOM = 'Custom.json';
    const ROUTE = 'Route.json';
    const CREDENTIAL = 'Credential.json';
    const URL = 'Application';

    const EXCEPTION_DIR_APPLICATION = 'No application directory defined.';
    const EXCEPTION_REQUEST = 'cannot route to SELF';
    const EXCEPTION_APPLICATION_ERROR = 'cannot route to Application/Error/';

    const CACHE_ROUTE = '+ 1 minute'; //rename to OBJECT_ROUTE_CACHE or  OBJECT_ROUTE_INTERVAL
    const CACHE_ROUTE_URL = 'Route.object.php'; //rename to OBJECT_ROUTE

    const OBJECT_INIT_URL = 'Init.object.php';
    const OBJECT_INIT_INTERVAL = '+ 1 minute';

    const ARRAY_INIT = [
        'DS',
        'PRIYA',
        'ENVIRONMENT',
        'MODULE',
        'TEMPLATE',
        'PLUGIN',
        'PAGE',
        'CACHE',
        'DATA',
        'OBJECT',
        'BACKUP',
        'PROCESSOR',
        'RESTORE',
        'UPDATE',
        'VENDOR',
        'TEMP',
        'PUBLIC_HTML',
        'HOST',
        'CSS',
        'JAVASCRIPT',
        'CONFIG',
        'CUSTOM',
        'ROUTE',
        'CREDENTIAL',
        'URL',
        'OBJECT_INIT_URL',
        'OBJECT_INIT_INTERVAL',
    ];

    const NO_CACHE = [
        'time',
        'web',
        'dir.current'
    ];

    public function __construct($autoload=null, $data=null){
        if($data){
            $data = new Data($this->object($data));
        }
        $this->cwd(rtrim(getcwd(), Application::DS) . Application::DS);
//         set_exception_handler(array('Priya\Module\Core','handler_exception'));
//         set_error_handler(array('Priya\Module\Core','handler_error'));
        if(isset($data)){
            $this->init($data);
        } else {
            $this->init();
        }
        if(!$this->data('priya.cache.init.read')){
            $this->dir();
            $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CONFIG;
            $this->read($url);
            $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CUSTOM;
            $this->read($url);
            $this->cli();
            $url = $this->data('dir.data') . Application::CONFIG;
            if(file_exists($url)){
                $this->read($url);
            }
            if(!($this->data('public_html'))){
                $this->data('public_html', $this->data('priya.application.constant.PUBLIC_HTML'));
                $this->data('dir.public', $this->data('dir.root') . $this->data('public_html') . $this->data('dir.ds'));
            }
        } else {
            $this->cli();
        }
        $this->handler(new Module\Handler($this->data()));
        $this->data('web.root', $this->handler()->web());
        $this->autoload($autoload);
        $this->router($this->data('priya.route.cache.url'));
    }

    public function run(){
        $this->data('time.application.run', microtime(true));
        $this->data('time.application.duration', $this->data('time.application.run') - $this->data('time.start'));
        Application::cache('write', $this);
        parent::autoload()->environment($this->data('priya.environment'));

        if(!$this->data('priya.dir.application')){
            throw new Exception(Application::EXCEPTION_DIR_APPLICATION);
        }
        //         chdir($this->data('priya.dir.application')); //keep working dir normal...
        $request = $this->request('request');
        if($request ===  $this->data('parser.request') && $request !== null){
            throw new Exception(Application::EXCEPTION_REQUEST);
        }
        $url = $this->handler()->url();
        $etag = sha1($url);
        $host = Handler::host(false);
        $tmp = explode('?', $url, 2);
        $url = reset($tmp);
        $tmp = explode('.', $url);
        $ext = strtolower(end($tmp));
        if(!empty($this->data('prefix'))){
            $tmp = explode($this->data('prefix'), $url, 2);
            $url = implode('', $tmp);
        }
        $allowed_contentType = $this->data('priya.contentType');
        if(isset($allowed_contentType->{$ext})){
            $path = str_replace('/', Application::DS, Handler::removeHost($this->url('decode', $url)));
            if($host=== false){
                $url = $this->data('dir.vendor') . $path;
            } else{
                $subdomain = Handler::subDomain();
                if($subdomain === false || $subdomain == 'www'){
                    $url_tmp = $this->data('dir.host') . $this->ucfirst(Handler::domain($host) . Application::DS . Handler::extension($host)) . Application::DS . $path;
                    //removed $this-data('public_html') from $url_tmp
                    $dir =  $this->data('dir.host') . ucfirst(Handler::domain($host)) . Application::DS . ucfirst(Handler::extension($host));
                    if(!file_exists($dir)){
                        $domain = Handler::domain();
                        $extension = Handler::extension();
                        $url_tmp = $this->data('dir.host') . ucfirst($domain) . Application::DS . ucfirst($extension) . Application::DS . $this->data('public_html') . Application::DS . $path;
                        $dir = $this->data('dir.host') . ucfirst($domain) . Application::DS . ucfirst($extension);
                    }
                } else {
                    if(strstr($path, $this->data('public_html'))){
                        $url_tmp = $this->data('dir.host') . $this->ucfirst(str_replace('.', Application::DS, $host)) . Application::DS . $path;
                    } else {
                        $url_tmp = $this->data('dir.host') . $this->ucfirst(str_replace('.', Application::DS, $host)) . Application::DS . $this->data('public_html') . Application::DS . $path;
                    }
                    $dir = $this->data('dir.host') . $this->ucfirst(str_replace('.', Application::DS, $host));
                    if(!file_exists($dir)){
                        $domain = Handler::domain();
                        $extension = Handler::extension();
                        $url_tmp = $this->data('dir.host') . ucfirst($domain) . Application::DS . ucfirst($extension) . Application::DS . $path;
                        $dir = $this->data('dir.host') . ucfirst($domain) . Application::DS . ucfirst($extension) . Application::DS . $path;
                    }
                }
                if(!file_exists($dir)){
                    $url = $this->data('dir.vendor') . $path;
                } else {
                    $url = $url_tmp;
                }
            }
            $result = null;
            $contentType = $allowed_contentType->{$ext};

            if(file_exists($url) && strstr(strtolower($url), strtolower($this->data('public_html'))) !== false){
                $mtime = File::mtime($url);
                $this->handler()->since($mtime);

                if(!headers_sent()){
                    $gm = gmdate('D, d M Y H:i:s T', $mtime);
                    $this->header('Last-Modified: '. $gm);
                    $this->header('Content-Type: ' . $contentType);
                    $this->header('ETag: ' . $etag . '-' . $gm);
                    $this->header('Cache-Control: public');
                    $this->header('Access-Control-Allow-Origin: http://' . $host);
                }
                if($ext == 'pcss'){
                    $read = str_replace('/', Application::DS, $request);
                    $read = str_replace(Application::DS . $this->data('public_html') . Application::DS . 'Pcss' . Application::DS , Application::DS, $read);
                    $read = str_replace('.pcss', '', $read);
                    $data = new Data();
                    $data->read($read);
                    $parser = new Parser();
                    $file = new File();
                    $result = $parser->data('object')->compile($file->read($url), $data->data());
                }
                elseif($ext == 'json'){
                    $file = new File();
                    $result = $file->read($url);
                    $object = new stdClass();
                    $object->url = $this->handler()->url();
                    $object = Application::object_merge($object, $this->object($result));
                    $result = $this->object($object, 'json');
                } else {
                    $file = new File();
                    $result = $file->read($url);
                }
            }
            if($result !== null){
                chdir($this->cwd());
                return $result;
            }
        }
        if(!headers_sent()){
            $this->header('Last-Modified: '. $this->request('last-modified'));
            $this->header('Access-Control-Allow-Origin: http://' . $host);
        }
        $item = $this->route()->run();
//         $this->cli(); //why twice -> see constructor
        $handler = $this->handler();
        $contentType = $handler->request('contentType');
        $result = '';
        if($contentType == Handler::CONTENT_TYPE_CLI){
            ob_start();
        }
        if(!empty($item->controller)){
            $controller = new $item->controller($this->handler(), $this->route(), $this->data());
            if(method_exists($controller, $item->function) === false){
                throw new Exception('method (' . $item->function . ') not exists in class: (' . get_class($controller) . ')');
            } else {
                if(method_exists($controller, 'autoload')){
                    $controller->autoload(parent::autoload());
                }
                if(method_exists($controller, 'parser')){
                    $controller->parser('object')->random($this->parser('object')->random());
                }
                $result = $controller->{$item->function}();
                if(method_exists($controller, 'message')){
                    $this->message($controller->message());
                    if(!empty($random)){
                        $message = $controller->message();
                        if(is_object($message)){
                            foreach($message as $attribute => $value){
                                $this->message($random . '.' . $attribute, $value);
                            }
                        }
                    } else {
                        $this->message($controller->error());
                    }
                }
                if(method_exists($controller, 'error')){
                    $this->error($controller->error());
                }
                $contentType = $handler->request('contentType'); //can change in the view
            }
        }
        elseif(!empty($item->url)){
            $this->data('request', $item->request);
            $parser = new \Priya\Module\Parser($this->handler(), $this->route(), $this->data());
            $item->url = $parser->compile($item->url, $this->data(), false, true);
            if(stristr($item->url, Handler::SCHEME_HTTPS)){
                $this->header('Location: ' . $item->url);
                //no http support... (people changed...)
                exit();
            }
            elseif(
                file_exists($item->url) &&
                strstr(strtolower($item->url), strtolower($this->data('public_html'))) !== false
                ){
                    $file = new File();
                    $ext = $file->extension($item->url);
                    if(empty($ext)){
                        $ext = 'txt'; //to handle Licence file
                    }
                    if(isset($allowed_contentType->{$ext})){
                        $contentType = $allowed_contentType->{$ext};
                        $this->header('Content-Type: ' . $contentType);
                        $this->header('Access-Control-Allow-Origin: http://' . $host);
                        $result = $file->read($item->url);
                    } else {
                        throw new  Exception('Content type not allowed...');
                    }
            } else {
                //404
            }
        }
        else {
            if($contentType == Handler::CONTENT_TYPE_CLI){
                if($request == 'Application/Error/'){
                    //                     var_dump($handler->request('route'));
                    //                     die;
                    throw new Exception(Application::EXCEPTION_APPLICATION_ERROR);
                    //bug when dir.data = empty ?
                }
                if($this->route()->error('read')){
                    $handler->request('request', 'Application/Error/');
                    $handler->request('id', 2);
                    $result = $this->run();
                } else {
                    if(empty($request)){
                        $handler->request('request', 'Application/Help/');
                        $result = $this->run();
                    } else {
                        $handler->request('route', $handler->request('request'));
                        $handler->request('request', 'Application/Error/');
                        $handler->request('id', 1);
                        $result = $this->run();
                    }
                }
            }
        }
        if(is_object($result)){
            if($contentType == Handler::CONTENT_TYPE_JSON){
                $result = $this->object($result, 'json');
            } else {
                if(isset($result->html)){
                    $result = $result->html;
                }
            }
        }
        elseif(is_string($result)){
            if($result == Handler::CONTENT_TYPE_CLI){
                $result = ob_get_contents();
                ob_end_clean();
            }
        }
        elseif(is_array($result)){
            var_dump($result);
            die;
        }
        else {
            //          404
        }
        chdir($this->cwd());  //for Parser
        return $result;
    }

    private function ucfirst($dir=''){
        $explode = explode(Application::DS, $dir);
        foreach($explode as $nr => $value){
            $explode[$nr] = ucfirst($value);
        }
        return implode(Application::DS, $explode);
    }

    private function dir(){
        //can change priya.dir.root
        if(!($this->data('priya.dir.root'))){
            $this->data('priya.dir.root',
                dirname($this->data('priya.dir.application')) .
                $this->data('dir.ds')
            );
        }
        //can change priya.dir.module
        //can have changed priya.dir.application so target change
        if(!($this->data('priya.dir.module'))){
            $this->data('priya.dir.module',
                dirname($this->data('priya.dir.application')) .
                $this->data('dir.ds') .
                $this->data('priya.application.constant.MODULE') .
                $this->data('dir.ds')
            );
        }
        //can change priya.dir.data (duplicate?)
        if(!($this->data('priya.dir.data'))){
            $this->data('priya.dir.data',
                $this->data('priya.dir.application') .
                Application::DATA .
                Application::DS
            );
        }
        //can change priya.dir.backup
        //local priya.dir.backup : priya.dir.data/BACKUP constant
        if(!($this->data('priya.dir.backup'))){
            $this->data('priya.dir.backup',
                $this->data('priya.dir.data') .
                $this->data('priya.application.constant.BACKUP') .
                $this->data('dir.ds')
            );
        }
        //can change dir.data
        //local dir.data : dir.root/DATA constant
        if(!($this->data('dir.data'))){
            $this->data('dir.data',
                $this->data('dir.root') .
                $this->data('priya.application.constant.DATA') .
                $this->data('dir.ds')
            );
        }
        //can change priya.dir.restore
        //local priya.dir.restore : priya.dir.data/RESTORE constant
        if(!($this->data('priya.dir.restore'))){
            $this->data('priya.dir.restore',
                $this->data('priya.dir.data') .
                $this->data('priya.application.constant.RESTORE') .
                $this->data('dir.ds')
            );
        }
        //can change priya.dir.update
        //local priya.dir.update : priya.dir.data/UPDATE constant
        if(!($this->data('priya.dir.update'))){
            $this->data('priya.dir.update',
                $this->data('priya.dir.data') .
                $this->data('priya.application.constant.UPDATE') .
                $this->data('dir.ds')
            );
        }
        //might need to move to init (for cache require...)
        //can change priya.dir.public
        //local priya.dir.public : priya.dir.root/PUBLIC_HTML constant
        //possible duplicate of dir.public is this one used ?
        if(!($this->data('priya.dir.public'))){
            $this->data('priya.dir.public',
                $this->data('priya.dir.root') . //dir.root ?
                $this->data('priya.application.constant.PUBLIC_HTML') .
                $this->data('dir.ds')
            );
        }
        //can change dir.host
        //local dir.host : dir.root/HOST constant
        if(!($this->data('dir.host'))){
            $this->data('dir.host',
                $this->data('dir.root') .
                $this->data('priya.application.constant.HOST') .
                $this->data('dir.ds')
            );
        }
    }

    private function init($object=null){
        $this->data('time.start', microtime(true));
        $this->data('module.name', $this->module());
        $this->data('priya.dir.application',
            dirname(Application::DIR) .
            Application::DS
        );
        if(isset($object) && $object->data('priya.cache.config')){
            $this->data('priya.cache.config', Application::object_merge(Cache::CONFIG, $object->data('priya.cache.config')));
        } else {
            $this->data('priya.cache.config', $this->object(Cache::CONFIG));
        }
        $url = $this->data('priya.dir.application') .
        Application::DATA .
        Application::DS .
        Application::OBJECT .
        Application::DS;
        $config = $this->data('priya.cache.config');
        $path = $config->{Application::OBJECT_INIT_INTERVAL};
        $init = new stdClass();
        $init->url =$url . $path . Application::DS . Application::OBJECT_INIT_URL;
        $init->interval = Application::OBJECT_INIT_INTERVAL;
        if(isset($object) && $object->data('priya.cache.init')){
            $this->data('priya.cache.init', Application::object_merge($init, $object->data('priya.cache.init')));
        } else {
            $this->data('priya.cache.init', $init);
        }
        $this->data('time.init.start', microtime(true));
        $cache = Application::cache('read', $this);
        if($cache){
            $this->data(Application::object_merge($this->data(), $this->object($cache)));
        } else {
            $parser = new Parser();
            if(isset($object)){
                $data = $parser->parser($object->data(), Application::object_merge($this->data(), $object->data()));
                $parser->data($data);
                $this->data(Application::object_merge($this->data(), $parser->data()));
                $this->data('delete', 'priya.parser'); //not needed
                /*
                $this->data('priya', Application::object_merge($this->data('priya'), $parser->data('priya')));
                $this->data('dir', Application::object_merge($this->data('dir'), $parser->data('dir')));
                */
            }
            foreach(Application::ARRAY_INIT as $constant){
                if(!$this->data('priya.application.constant.' . $constant)){
                    $this->data('priya.application.constant.' . $constant, constant('Priya\\Application::' . $constant));
                }
            }
            //can change dir.ds & priya.application.constant.DS
            if(!$this->data('dir.ds')){
                $this->data('dir.ds', $this->data('priya.application.constant.DS'));
            }
            //can change priya.environment & priya.application.constant.ENVIRONMENT
            if(!$this->data('priya.environment')){
                $this->data('priya.environment', $this->data('priya.application.constant.ENVIRONMENT'));
            }


            //can change priya.dir.data
            //cannot change the local priya.dir.data
            if(!$this->data('priya.dir.data')){
                $this->data('priya.dir.data',
                    $this->data('priya.dir.application') .
                    Application::DATA .
                    Application::DS
                );
            }

            //can change priya.dir.temp
            //local priya.dir.temp is priya.dir.data/TEMP constant
            if(!$this->data('priya.dir.temp')){
                $this->data('priya.dir.temp',
                    $this->data('priya.dir.data') .
                    $this->data('priya.application.constant.TEMP') .
                    $this->data('dir.ds')
                );
            }

            //can change priya.dir.cache
            //local priya.dir.cache is priya.dir.temp/CACHE constant
            if(!$this->data('priya.dir.cache')){
                $this->data('priya.dir.cache',
                    $this->data('priya.dir.temp') .
                    $this->data('priya.application.constant.CACHE') .
                    $this->data('dir.ds')
                );
            }
            //can change priya.dir.processor
            //local priya.dir.cache is priya.dir.data/PROCESSOR constant
            if(!$this->data('priya.dir.processor')){
                $this->data('priya.dir.processor',
                    $this->data('priya.dir.data') .
                    $this->data('priya.application.constant.PROCESSOR') .
                    $this->data('dir.ds')
                );
            }
            //can change dir.vendor
            //local dir.vendor is dirname dirname priya.dir.application
            if(!$this->data('dir.vendor')){
                $this->data('dir.vendor',
                    dirname(dirname($this->data('priya.dir.application'))) .
                    $this->data('dir.ds')
                );
            }
            //can change dir.root
            //local dir.root is dirname dir.vendor
            if(!$this->data('dir.root')){
                $this->data('dir.root',
                    dirname($this->data('dir.vendor')) .
                    $this->data('dir.ds')
                );
            }
            /*
            $url = $this->data('priya.dir.cache');
            $config = $this->data('priya.cache.config');
            $path = $config->{Application::OBJECT_ROUTE_INTERVAL};
            $route = new stdClass();
            $route->url = $url . $path . Application::DS . Application::OBJECT_ROUTE_URL;
            $route->interval = Application::OBJECT_ROUTE_INTERVAL;
            if($this->data('priya.cache.route')){
                $this->data('priya.cache.route', Application::object_merge($route, $this->data('priya.cache.route')));
            } else {
                $this->data('priya.cache.route', $route);
            }
            */
        }
        /*
        if($this->data('priya.cache.route')){
            $url = $this->data('priya.dir.cache');
            $config = $this->data('priya.cache.config');
            $path = $config->{Application::OBJECT_ROUTE_INTERVAL};
            $route = new stdClass();
            $route->url = $url . $path . Application::DS . Application::OBJECT_ROUTE_URL;
            $route->interval = Application::OBJECT_ROUTE_INTERVAL;
            $this->data('priya.cache.route', $route);
        }
        */
        $this->data('dir.current', $this->cwd());
        $this->data('time.init.end', microtime(true));
    }

    private function router($url=''){
        if(empty($url)){
            $url = $this->data('priya.route.cache.url');
        }
        $start = microtime(true);
        $duration = $start - $this->data('time.start');
        $cache = false;
        $is_cache = true;
        $is_expired = false;
        if($this->data('priya.cache.disable')){
            $is_cache = false;
        }
        if($this->data('priya.route.cache.disable')){
            $is_cache = false;
        }
        if($is_cache){
            $cache = Cache::read($url, $this->data('priya.cache.config.' .  $this->data('priya.route.cache.interval')));
            if($cache === Cache::ERROR_EXPIRE){
                $is_expired = true;
                $cache = Cache::validate($url, $this->data('priya.cache.config.' .  $this->data('priya.route.cache.interval')));
            }
        }
        if($cache){
            $this->route(new Module\Route(
                $this->handler(),
                $this->data(),
                false
            ));
            $this->route()->data($cache);
            $this->route()->data('priya.route.cache.time.start', $start);
            $this->route()->data('priya.route.cache.time.duration', microtime(true) - $start);
        } else {
            $this->route(new Module\Route(
                $this->handler(),
                $this->data()
            ));
            $this->route()->data('priya.route.cache.time.start', $start);
            $this->route()->data('priya.route.cache.time.duration', microtime(true) - $start);
            if($is_cache){
                Cache::write($url,  $this->object($this->route()->data(), 'json'), true);
            }
        }
    }

    public function read($url=''){
        $this->data('time.' . $url . '.start', microtime(true));
        $read = parent::read($url);
        $this->data('time.' . $url . '.end', microtime(true));
        $this->data('time.' . $url . '.duration', $this->data('time.' . $url . '.end') - $this->data('time.' . $url . '.start'));
        return $read;
    }

    public static function cache($type='', $object){
        switch($type){
            case 'read' :
                if($object->data('priya.cache.disable')){
                    return;
                }
                if($object->data('priya.cache.init.disable')){
                    return;
                }
                $read = null;
                $url = $object->data('priya.cache.init.url');
                if(File::exist($url)){
                    $read = File::read($url);
                }
                if($read){
                    $object->data('priya.cache.init.read', true);
                }
                return $read;
            break;
            case 'delete' :
                $url = $object->data('priya.cache.init.url');
                File::delete($url);
                break;
            case 'write' :
                if($object->data('priya.cache.disable')){
                    return;
                }
                if($object->data('priya.cache.init.disable')){
                    return;
                }
                $url = $object->data('priya.cache.init.url');
                if(File::exist($url)){
                    $mtime = File::mtime($url);
                    $time = time();
                    $config = $object->data('priya.cache.config');
                    $duration = isset($config->{$object->data('priya.cache.init.interval')}) ? $config->{$object->data('priya.cache.init.interval')} : Cache::CONFIG[Cache::DEFAULT];
                    $duration = (int) $duration;
                    $interval = strtotime($object->data('priya.cache.init.interval')) - $time;
                    $expire = $mtime + $interval;
                    if($time <= $expire){
                        return Application::CACHE;  //still a valid cache object.
                    }
                } else {
                    $dir = dirname($url);
                    if(!is_dir($dir)){
                        Dir::create($dir, Dir::CHMOD);
                    }
                }
                $data = new Data($object->data());
                if(is_array(Application::NO_CACHE)){
                    foreach(Application::NO_CACHE as $delete){
                        $data->data('delete', $delete);
                    }
                }
                return $data->write($url);
                break;
        }
    }

    public function autoload($autoload=''){
        if(empty($autoload)){
            $autoload = new \Priya\Module\Autoload();
            $autoload->addPrefix('Priya',  dirname(Application::DIR) . Application::DS);
            $autoload->register();
        }
        $autoload->environment($this->data('priya.environment'));
        parent::autoload($autoload);
        parent::autoload()->addPrefix('Vendor', $this->data('dir.vendor'));

        $autoload = $this->data('priya.autoload');
        if(empty($autoload)){
            $autoload = $this->data('autoload');
        }
        //how to name below...
        if(is_object($autoload)){
            foreach($autoload as $prefix => $directory){
                parent::autoload()->addPrefix($prefix, $directory);
            }
        }
    }

    private function cli(){
        $request = $this->request('data');
        if(!empty($request)){
            if(is_array($request) || is_object($request)){
                foreach($request as $attribute){
                    $attribute = explode('=', $attribute, 2);
                    if(isset($attribute[1])){
                        $key = $attribute[0];
                        $value = $attribute[1];

                        if(!empty($key) && isset($value)){
                            $this->request($key, $value);
                            unset($key);
                            unset($value);
                        }
                    }
                }
            }
        }
    }
}