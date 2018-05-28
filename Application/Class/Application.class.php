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
use Priya\Module\Core\Data;
use Priya\Module\File\Cache;

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

    const CACHE_ROUTE = '+ 1 minute';

    public function __construct($autoload=null, $data=null){
        $this->cwd(rtrim(getcwd(), Application::DS) . Application::DS);
//         set_exception_handler(array('Priya\Module\Core','handler_exception'));
//         set_error_handler(array('Priya\Module\Core','handler_error'));
        $this->init();

        $url = $this->data('priya.dir.cache') .
            Cache::MINUTE .
            Application::DS .
            Application::URL
        ;
        /*
        $cache = $this->cache($url);
        if($cache){
            $this->cli();
            $this->handler(new Module\Handler($this->data()));
            if($this->data('priya.dir.application')){
                chdir($this->data('priya.dir.application'));
            }
            $this->autoload($autoload);
            $this->router();
            return;
        }
        */
        $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CONFIG;
        $this->read($url);
        $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CUSTOM;
        $this->read($url);
        $this->data(Application::object_merge($this->data(),$this->object($data)));
        $this->cli();
        $this->dir();
        $url = $this->data('dir.data') . Application::CONFIG;
        if(file_exists($url)){
            $this->read($url);
        }
        if(empty($this->data('public_html'))){
            $this->data('public_html', Application::PUBLIC_HTML);
            $this->data('dir.public', $this->data('dir.root') . $this->data('public_html') . Application::DS);
        }

        $this->handler(new Module\Handler($this->data()));
        $this->data('web.root', $this->handler()->web());

        /*
        if($this->data('priya.dir.application')){
            chdir($this->data('priya.dir.application'));
        }
        */
        $this->autoload($autoload);
        $this->router();
    }

    public function run($url=''){
        $this->data('time.application.run', microtime(true));
        $this->data('time.application.duration', $this->data('time.application.run') - $this->data('time.start'));
        if(empty($url)){
            $url = $this->data('priya.dir.application') .
            Application::DATA .
            Application::DS .
            Cache::CACHE .
            Application::DS .
            Cache::MINUTE .
            Application::DS .
            Application::URL
            ;
        }
        //want to write to cache here...
        //         $this->write($url);//(for what purpose?)
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
            $host = $this->handler()->host(false);
            if($host=== false){
                $url = $this->data('dir.vendor') . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
            } else{
                $subdomain = $this->handler()->subDomain();
                if($subdomain === false || $subdomain == 'www'){
                    $url_tmp = $this->data('dir.host') . str_replace('www.', '', $host) . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                    //removed $this-data('public_html') from $url_tmp
                    $dir =  $this->data('dir.host') . str_replace('www.', '', $host);
                    if(!file_exists($dir)){
                        $domain = $this->handler()->domain();
                        $extension = $this->handler()->extension();
                        $url_tmp = $this->data('dir.host') . $domain . '.' . $extension . Application::DS . $this->data('public_html') . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                        $dir = $this->data('dir.host') . $domain . '.' . $extension;
                    }
                } else {
                    $url_tmp = $this->data('dir.host') . $host . Application::DS . $this->data('public_html') . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                    $dir = $this->data('dir.host') . $host ;
                    if(!file_exists($dir)){
                        $domain = $this->handler()->domain();
                        $extension = $this->handler()->extension();
                        $url_tmp = $this->data('dir.host') . $domain . '.' . $extension . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                        $dir = $this->data('dir.host') . $domain . '.' . $extension . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                    }
                }
                if(!file_exists($dir)){
                    $url = $this->data('dir.vendor') . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
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
        }
        $item = $this->route()->run();
//         var_dump($item);
        $this->cli(); //why twice -> see constructor
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
            if(
                file_exists($item->url) &&
                strstr(strtolower($item->url), strtolower($this->data('public_html'))) !== false
                ){
                    $file = new File();
                    $ext = $file->extension($item->url);
                    if(empty($ext)){
                        $ext = 'txt'; //to handle Licence file
                    }
                    $contentType = $allowed_contentType->{$ext};

                    $this->header('Content-Type: ' . $contentType);
                    $result = $file->read($item->url);
            } else {
                //404
            }
        }
        else {
            if($contentType == Handler::CONTENT_TYPE_CLI){
                if($request == 'Application/Error/'){
                    var_dump($this->route()->data());
                    var_dump($this->route($request));
                    die;
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

    private function dir(){
        if(empty($this->data('priya.dir.root'))){
            $this->data('priya.dir.root',
                dirname($this->data('priya.dir.application')) .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.module'))){
            $this->data('priya.dir.module',
                dirname($this->data('priya.dir.application')) .
                Application::DS .
                Application::MODULE .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.data'))){
            $this->data('priya.dir.data',
                $this->data('priya.dir.application') .
                Application::DATA .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.backup'))){
            $this->data('priya.dir.backup',
                $this->data('priya.dir.data') .
                Application::BACKUP .
                Application::DS
            );
        }
        if(empty($this->data('dir.data'))){
            $this->data('dir.data',
                $this->data('dir.root') .
                Application::DATA .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.restore'))){
            $this->data('priya.dir.restore',
                $this->data('priya.dir.data') .
                Application::RESTORE .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.update'))){
            $this->data('priya.dir.update',
                $this->data('priya.dir.data') .
                Application::UPDATE .
                Application::DS
            );
        }
        if(empty($this->data('priya.dir.public'))){
            $this->data('priya.dir.public',
                $this->data('priya.dir.root') .
                Application::PUBLIC_HTML .
                Application::DS
            );
        }
        if(empty($this->data('dir.host'))){
            $this->data('dir.host',
                $this->data('dir.root') .
                Application::HOST .
                Application::DS
            );
        }
    }

    private function init(){
        $this->data('time.start', microtime(true));
        $this->data('priya.environment', Application::ENVIRONMENT);
        $this->data('module.name', $this->module());
        $this->data('dir.ds', Application::DS);
        $this->data('priya.dir.application',
            dirname(Application::DIR) .
            Application::DS
        );
        $this->data('priya.dir.data',
            dirname(Application::DIR) .
            Application::DS .
            Application::DATA .
            Application::DS
        );
        $this->data('priya.dir.cache',
            $this->data('priya.dir.data') .
            Cache::CACHE .
            Application::DS
        );
        $this->data('priya.dir.processor',
            $this->data('priya.dir.data') .
            Application::PROCESSOR .
            Application::DS
        );
        $this->data('dir.vendor',
            dirname(dirname($this->data('priya.dir.application'))) .
            Application::DS
        );
        if(stristr($this->data('dir.vendor'), Application::VENDOR) === false){
            $this->data('dir.vendor',
                dirname($this->data('priya.dir.application')) .
                Application::DS
            );
            $this->data('dir.root', $this->data('dir.vendor'));
        } else {
            $this->data('dir.root',
                dirname($this->data('dir.vendor')) .
                Application::DS
            );
        }
        $this->data('dir.current', $this->cwd());
    }

    private function router($url=''){
        if(empty($url)){
            $url = $this->data('priya.dir.application') .
                Application::DATA .
                Application::DS .
                Cache::CACHE .
                Application::DS .
                Cache::MINUTE .
                Application::DS .
                Application::ROUTE
            ;
        }
        $serialize = str_replace('.json', '.serialize', $url);

        $start = microtime(true);
//         $route = Cache::deserialize($serialize, '+220 minutes');
        $route = false;
        if($route){
            $route->handler($this->handler());
            $this->route($route);

            $this->route()->data('time.route.start', $start);
            $this->data('time.route.start', $this->route()->data('time.route.start'));
            $this->data('time.route.cache', $this->route()->data('time.route.cache'));
            $this->data('time.route.url', $serialize);
            $this->data('time.route.duration', microtime(true) - $start);

            var_dump($this->data('time'));
            die;
        }
        $cache = Cache::read($url, Application::CACHE_ROUTE);
        if($cache === Cache::ERROR_EXPIRE){
            $cache = Cache::validate($url, Application::CACHE_ROUTE);

        }
        if($cache){
            $this->route(new Module\Route(
                $this->handler(),
                $cache,
                false
            ));
            $this->route()->data('time.route.start', $start);
            $this->data('time.route.start', $this->route()->data('time.route.start'));
            $this->data('time.route.cache', $this->route()->data('time.route.cache'));
            $this->data('time.route.url', Cache::url($url, '.json'));
            $this->data('time.route.duration', microtime(true) - $start);
        } else {
            $route = new Data();
            $route->data('time', $this->data('time'));
            $route->data('priya', $this->data('priya'));
            $route->data('dir', $this->data('dir'));

            $this->route(new Module\Route(
                $this->handler(),
                $route->data()
            ));

            foreach($this->data('priya.route.default') as $default){
                $this->route()->create($default);
            }
            $this->route()->data('time.route.cache', $start);
            $time_start = $this->route()->data('time.start');
            $this->route()->data('delete', 'time.start');

            Cache::write($url, $this->route()->data(), true);
//             Cache::serialize($this->route(), $serialize);
            $this->route()->data('time.start', $time_start);
            $this->data('time.route.start', $start);
        }
    }

    public function read($url=''){
        $this->data('time.' . $url . '.start', microtime(true));
        $read = parent::read($url);
        $this->data('time.' . $url . '.end', microtime(true));
        $this->data('time.' . $url . '.duration', $this->data('time.' . $url . '.end') - $this->data('time.' . $url . '.start'));
        return $read;
    }

    public function cache($url='', $type='data'){
        $url = $url . '?' . date('YmdHi'); //every minute;
        $cache = Cache::read($url);
        switch($type){
            case 'data':
                $this->data(Module\Core::object($cache, 'object'));
            break;
            case 'url':
                return Cache::url($url, '.json');
            break;
        }
        return $cache;
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