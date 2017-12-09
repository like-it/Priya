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
use Priya\Module\Core\Object;
use Priya\Module\File\Dir;

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

    public function __construct($autoload=null, $data=null){
        $this->cwd(getcwd());
//         set_exception_handler(array('Priya\Module\Core','handler_exception'));
//         set_error_handler(array('Priya\Module\Core','handler_error'));
        $this->data('time.start', microtime(true));
        $this->data('priya.environment', Application::ENVIRONMENT);
        $this->data('module.name', $this->module());
        $this->data('dir.ds', Application::DS);
        $this->data('priya.dir.application',
            dirname(Application::DIR) .
            Application::DS
        );
        $this->data('dir.vendor',
            dirname(dirname($this->data('priya.dir..application'))) .
            Application::DS
        );
        if(stristr($this->data('dir.vendor'), Application::VENDOR) === false){
            $this->data('dir.vendor',
                dirname($this->data('priya.dir..application')) .
                Application::DS
            );
            $this->data('dir.root', $this->data('dir.vendor'));
        } else {
            $this->data('dir.root',
                dirname($this->data('dir.vendor')) .
                Application::DS
            );
        }
        $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CONFIG;
        if(file_exists($url)){
            $this->read($url);
        }
        $url = dirname(Application::DIR) . Application::DS . Application::DATA . Application::DS . Application::CUSTOM;
        if(file_exists($url)){
            $this->read($url);
        }
        $this->data(Application::object_merge($this->data(),$this->object($data)));
        $this->cli();
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
        $url = $this->data('dir.data') . Application::CONFIG;
        if(file_exists($url)){
            $this->read($url);
        }

        if(empty($this->data('public_html'))){
            $this->data('public_html', Application::PUBLIC_HTML);
            $this->data('dir.public', $this->data('dir.root') . $this->data('public_html') . Application::DS);
        }
        if(empty($this->data('dir.host'))){
            $this->data('dir.host',
                $this->data('dir.root') .
                Application::HOST .
                Application::DS
            );
        }
        $this->handler(new Module\Handler($this->data()));
        $this->data('web.root', $this->handler()->web());

        if($this->data('priya.dir.application')){
            chdir($this->data('priya.dir.application'));
        }
        if(empty($autoload)){
            $autoload = new \Priya\Module\Autoload();
            $autoload->addPrefix('Priya',  dirname(Application::DIR) . Application::DS);
            $autoload->register();
        }
        $autoload->environment($this->data('priya.environment'));
        $this->autoload($autoload);
        $this->autoload()->addPrefix('Vendor', $this->data('dir.vendor'));

        $autoload = $this->data('autoload');
        if(is_object($autoload)){
            foreach($autoload as $prefix => $directory){
                $this->autoload()->addPrefix($prefix, $directory);
            }
        }
        $this->route(new Module\Route(
            $this->handler(),
            clone $this->data()
        ));
        $this->route()->create('Application.Version');
        $this->route()->create('Application.Locate');
        $this->route()->create('Application.Config');
        $this->route()->create('Application.Help');
        $this->route()->create('Application.Error');
        $this->route()->create('Application.Route');
        $this->route()->create('Application.Parser');
        $this->route()->create('Application.Cache');
        $this->route()->create('Application.Check');
        $this->route()->create('Application.Install');
        $this->route()->create('Test');
    }

    public function run(){
        $this->autoload()->environment($this->data('priya.environment'));
        chdir($this->data('priya.dir.application'));
        $request = $this->request('request');
        if($request ===  $this->data('parser.request') && $request !== null){
            trigger_error('cannot route to SELF', E_USER_ERROR);
        }
        $url = $this->handler()->url();
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
                    $url_tmp = $this->data('dir.host') . str_replace('www.', '', $host) . Application::DS . $this->data('public_html') . Application::DS . str_replace('/', Application::DS, $this->handler()->removeHost($this->url('decode', $url)));
                    $dir =  $this->data('dir.host') . str_replace('www.', '', $host);
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
                if(!headers_sent()){
                    header('Last-Modified: '. filemtime($url));
                    header('Content-Type: ' . $contentType);
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
            header('Last-Modified: '. $this->request('lastModified'));
        }
        $item = $this->route()->run();
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
            }
        }
        elseif(!empty($item->url)){
            if(file_exists($item->url) && strstr(strtolower($item->url), strtolower($this->data('public_html'))) !== false){
               $file = new File();
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
            if($result != Handler::CONTENT_TYPE_CLI){
                $result = $result;
            } else {
                $result = ob_get_contents();
                ob_end_clean();
            }
        } else {
//          404
        }
        chdir($this->cwd());  //for Parser
        return $result;
    }

    public function page($request=''){
        $this->data('request', $request);
        $this->data('priya.dir.page', $this->data('priya.dir.root') . Application::PAGE . Application::DS);
        $this->data('module.dir.page', $this->data('module.dir.root') . Application::PAGE . Application::DS);

        $result = new stdClass();

        $parser = new \Priya\Module\Parser();
        $parser->data($this->data());
        $file = new \Priya\Module\File();
        $url = $this->data('module.dir.page') . 'Request.priya';
        if(file_exists($url)){
            var_dump('parse this one');
        } else {
            $url = $this->data('priya.dir.page') . 'Request.priya';
            if(file_exists($url)){
                $parser->data('input', $file->read($url));
                $parser->data($parser->compile($parser->data(), $parser->data()));

                var_dump($parser->data());

                var_Dump($parser->data('script'));
                die;

                $result->script[] = $parser->data('input');

                if($this->data('request.contentType') == Handler::CONTENT_TYPE_JSON){
                    $result = $this->object($result, 'json');
                } else {

                }

            } else {
                var_dump('parse not found');
            }
        }
        return $result;
    }

    private function cli(){
        $request = $this->request('data');
        if(!empty($request)){
            if(is_array($request) || is_object($request)){
                $key = false;
                $value = null;
                foreach($request as $attribute){
                    if(!empty($key)){
                        $value = $attribute;
                    }
                    $attribute = explode('=', $attribute, 2);
                    if(count($attribute) == 2){
                        switch($attribute[0]){
                            case 'dir.data':
                                $key = $attribute[0];
                                $value = $attribute[1];
                            break;
                        }
                    } else {
                        switch($attribute[0]){
                            case 'dir.data':
                                $key = $attribute[0];
                                continue;
                            break;
                        }
                    }

                    if(!empty($key) && isset($value)){
                        $this->data($key, $value);
                        unset($key);
                        unset($value);
                    }
                }
            }
        }
    }
}