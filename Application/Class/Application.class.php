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
use Priya\Module\File;
use Priya\Module\Handler;
use Priya\Module\Core\Parser;
use Priya\Module\Core\Data;
use Priya\Module\File\Dir;
use Priya\Module\File\Cache;

class Application extends Parser {
    const DS = DIRECTORY_SEPARATOR;
    const DIR = __DIR__;
    const PRIYA = 'Priya';
    const BIN = 'Bin';
    const ENVIRONMENT = 'development';
    const MODULE = 'Module';
    const TEMPLATE = 'Template';
    const PLUGIN = 'Plugin';
    const PAGE = 'Page';
    const DATA = 'Data';
    const BACKUP = 'Backup';
    const RESTORE = 'Restore';
    const UPDATE = 'Update';
    const VENDOR = 'Vendor';
    const TEMP = 'Temp';
    const CACHE = 'Cache';
    const PUBLIC_HTML = 'Public';
    const HOST = 'Host';
    const CSS = 'Css';
    const JAVASCRIPT = 'Javascript';
    const CONFIG = 'Config.json';
    const CUSTOM = 'Custom.json';
    const ROUTE = 'Route.json';
    const CREDENTIAL = 'Credential.json';
    const URL = 'Application';

    public function __construct($autoload=null, $data=null){
        $this->cwd(getcwd());
//         set_exception_handler(array('Priya\Module\Core','handler_exception'));
//         set_error_handler(array('Priya\Module\Core','handler_error'));
        $this->init();
        $url = $this->data('priya.dir.application') .
            Application::DATA .
            Application::DS .
            Cache::CACHE .
            Application::DS .
            Cache::MINUTE .
            Application::DS .
            Application::URL
        ;
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

        if($this->data('priya.dir.application')){
            chdir($this->data('priya.dir.application'));
        }
        $this->autoload($autoload);
        $this->router();
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
        $cache = $this->cache($url, 'route');
        if($cache){
            $this->route(new Module\Route(
                $this->handler(),
                Module\Core::object($cache, 'object'),
                false
            ));
        } else {
            $this->route(new Module\Route(
                $this->handler(),
                clone $this->data()
            ));
            $this->route()->create('Application.Version');
            $this->route()->create('Application.Licence');
            $this->route()->create('Application.Locate');
            $this->route()->create('Application.Config');
            $this->route()->create('Application.Help');
            $this->route()->create('Application.Error');
            $this->route()->create('Application.Route');
            $this->route()->create('Application.Parser');
            $this->route()->create('Application.Cache');
            $this->route()->create('Application.Check');
            $this->route()->create('Application.Install');
            $this->route()->create('Application.Zip');
            $this->route()->create('Test');
        }
        $this->write($url,'route');
    }

    /**
     * only begin & end...
     * {@inheritDoc}
     * @see \Priya\Module\Core\Parser::read()
     */
    public function read($url=''){
        if(file_exists($url) === false){
            return false;
        }
        $mtime = filemtime($url);
        $url_cache = $url . '?mtime=' . $mtime;
        $cache = Cache::read($url_cache);
        if(!$cache){
            $read = parent::read($url);
            $data = new Data();
            $data->data($read);
            $data->data('time.cache', $data->data('time.start'));
            $data->data('delete', 'time.start');
//             Cache::write($url_cache, $data->data());
            return $read;
        }
        $this->data($cache);
        return $cache;
    }

    public function write($url='', $type='data'){
        switch ($type){
            case 'route' :
                $url = $url . '?' . date('YmdHi', $this->route()->data('time.cache')); //every minute;
                Cache::write($url, $this->route()->data());
            break;
            default:
                $data = new Data();
                $data->data($this->data());
                $data->data('time.cache', $data->data('time.start'));
                $url = $url . '?' . date('YmdHi', $data->data('time.cache')); //every minute;
                $data->data('delete','time.start');
                Cache::write($url, $data->data());
        }

    }

    public function cache($url='', $type='data'){
        $url = $url . '?' . date('YmdHi'); //every minute;
        $cache = Cache::read($url);
        switch($type){
            case 'data':
                $this->data(Module\Core::object($cache, 'object'));
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

    public function run($url=''){
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
        $this->write($url);
        parent::autoload()->environment($this->data('priya.environment'));
        if(!$this->data('priya.dir.application')){
            var_dump($this->data());
            die;
        }
        chdir($this->data('priya.dir.application'));
        $request = $this->request('request');
        if($request ===  $this->data('parser.request') && $request !== null){
            trigger_error('cannot route to SELF', E_USER_ERROR);
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
                        $url_tmp = $this->data('dir.host') . $domain . '.' . $extension . Application::DS . $this->data('public_html') . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                        $dir = $this->data('dir.host') . $domain . '.' . $extension;
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
        $this->cli();
        $handler = $this->handler();
        $contentType = $handler->request('contentType');
        $result = '';
        if($contentType == Handler::CONTENT_TYPE_CLI){
            ob_start();
        }
        if(!empty($item->controller)){
            $controller = new $item->controller($this->handler(), $this->route(), $this->data());
            if(method_exists($controller, $item->function) === false){
                trigger_error('method (' . $item->function . ') not exists in class: (' . get_class($controller) . ')');
            } else {
                if(method_exists($controller, 'autoload')){
                    $controller->autoload($this->autoload());
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

            if(file_exists($item->url) && strstr(strtolower($item->url), strtolower($this->data('public_html'))) !== false){
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
                    trigger_error('cannot route to Application/Error/', E_USER_ERROR);
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