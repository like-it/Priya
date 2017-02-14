<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 * 	-	all
 *
 * @todo
 */


namespace Priya;

use Priya\Module\Core\Parser;
use Priya\Module\Core\Data;
use Priya\Module\File;
use Priya\Module\Core\Object;

class Application extends Parser {
    const DS = DIRECTORY_SEPARATOR;
    const DIR = __DIR__;
    const ENVIRONMENT = 'development';
    const MODULE = 'Module';
    const DATA = 'Data';
    const BACKUP = 'Backup';
    const PUBLIC_HTML = 'Public';
    const TAG = '0.0.3';
    const CONFIG = 'Config.json';
    const ROUTE = 'Route.json';

    private $autoload;

    public function __construct($autoload=null){
        $this->data('environment', Application::ENVIRONMENT);
        $this->data('module', $this->module());
        $this->data('dir.priya.application',
                dirname(Application::DIR) .
                Application::DS
        );
        $this->data('dir.priya.module',
                dirname($this->data('dir.priya.application')) .
                Application::DS .
                Application::MODULE .
                Application::DS
        );
        $this->data('dir.priya.data',
                $this->data('dir.priya.application') .
                Application::DATA .
                Application::DS
        );
        $this->data('dir.priya.backup',
                $this->data('dir.priya.data') .
                Application::BACKUP .
                Application::DS
        );
        $this->data('dir.vendor',
                dirname(dirname($this->data('dir.priya.application'))) .
                Application::DS
        );
        $this->data('dir.root',
                dirname($this->data('dir.vendor')) .
                Application::DS
        );
        $this->data('dir.data',
                $this->data('dir.root') .
                Application::DATA .
                Application::DS
        );
        $this->read($this->data('dir.data') . Application::CONFIG);

        if(empty($this->data('public_html'))){
            $this->data('public_html', Application::PUBLIC_HTML);
            $this->data('dir.public', $this->data('dir.root') . $this->data('public_html') . Application::DS);
        }
        $this->handler(new Module\Handler($this->data()));
        $this->data('web.root', $this->handler()->web());

        chdir($this->data('dir.priya.application'));
        if(empty($autoload)){
            $autoload = new \Priya\Module\Autoload();
            $autoload->addPrefix('Priya',  dirname(Application::DIR) . Application::DS);
            $autoload->register();
        }
        $autoload->environment($this->data('environment'));
        $this->autoload($autoload);
        $this->autoload()->addPrefix('Vendor', $this->data('dir.vendor'));

        $autoload = $this->data('autoload');
        if(is_object($autoload)){
            foreach($autoload as $prefix => $directory){
                $this->autoload()->addPrefix($prefix, $directory);
            }
        }
        $data = new Data();
        $data->data($this->data());

        $this->route(new Module\Route(
            $this->handler(),
            clone $this->data()
        ));
        $this->route()->create('Application.Help');
        $this->route()->create('Application.Error');
        $this->route()->create('Application.Route');
        $this->route()->create('Application.Install');
        $this->route()->create('Application.Update');
        $this->route()->create('Application.Restore');
        $this->route()->create('Application.Config');
        $this->route()->create('Application.User');
    }

    public function run(){
        if(!headers_sent()){
            header('Last-Modified: '. $this->request('Last-Modified'));
        }
        $request = $this->request('request');
        $tmp = explode('.', $request);
        $ext = strtolower(end($tmp));
        $url = $this->data('dir.vendor') . str_replace('/', Application::DS, $request);

        $allowed_contentType = $this->data('contentType');
        if(empty($allowed_contentType)){
            $allowed_contentType = $this->data('Content-Type');
        }
        if(isset($allowed_contentType->{$ext})){
            $contentType = $allowed_contentType->{$ext};
            header('Content-Type: ' . $contentType);
            if(file_exists($url) && strstr(strtolower($url), strtolower($this->data('public_html'))) !== false){
                if($ext == 'css'){
                    $read = str_replace('/', Application::DS, $request);
                    $read = str_replace(Application::DS . $this->data('public_html') . Application::DS . 'Css' . Application::DS , Application::DS, $read);
                    $read = str_replace('.css', '', $read);
                    $data = new Data();
                    $data->read($read);
                    $parser = new Parser();
                    $file = new File();
                    return $parser->compile($file->read($url), $data->data());
                } else {
                    $file = new File();
                    return $file->read($url);
                }
            }
        }
        $item = $this->route()->run();
        $handler = $this->handler();
        $contentType = $handler->request('Content-Type');
        $result = '';
        if(!empty($item->controller)){
            $controller = new $item->controller($this->handler(), $this->route(), $this->data());
            if(method_exists($controller, $item->function) === false){
                trigger_error('method (' . $item->function . ') not exists in class: (' . get_class($controller) . ')');
            } else {
                $result = $controller->{$item->function}();
            }
        } else {
            if($contentType == 'text/cli'){
                if($this->route()->error('read')){
                    $handler->request('request', 'Application/Error/');
                    $handler->request('id', 2);
                    $this->run();
                } else {
                    $request =  $handler->request('request');
                    if(empty($request)){
                        $handler->request('request', 'Application/Help/');
                        $this->run();
                    } else {
                        $handler->request('route', $handler->request('request'));
                        $handler->request('request', 'Application/Error/');
                        $handler->request('id', 1);
                        $this->run();
                    }
                }
            }
        }
        if(is_object($result) && isset($result->html)){
            if($contentType == 'application/json'){
                return json_encode($result, JSON_PRETTY_PRINT);
            } else {
                return $result->html;
            }
        }
        elseif(is_string($result)){
            if($result != 'text/cli'){
                return $result;
            }
        } else {
            trigger_error('unknown result');
            var_dump($result);
            var_dump($item);
        }
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
}
