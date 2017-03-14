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
    const RESTORE = 'Restore';
    const UPDATE = 'Update';
    const PUBLIC_HTML = 'Public';
    const CONFIG = 'Config.json';
    const ROUTE = 'Route.json';
    const CREDENTIAL = 'Credential.json';

    public function __construct($autoload=null, $data=null){
        set_exception_handler(array('Priya\Module\Core','handler_exception'));
        set_error_handler(array('Priya\Module\Core','handler_error'));
        $this->data($this->object($data));
        $this->cli();
        $this->data('environment', Application::ENVIRONMENT);
        $this->data('module', $this->module());
        $this->data('dir.priya.application',
            dirname(Application::DIR) .
            Application::DS
        );
        $this->data('dir.priya.root',
            dirname($this->data('dir.priya.application')) .
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
        if(empty($this->data('dir.data'))){
            $this->data('dir.data',
                $this->data('dir.root') .
                Application::DATA .
                Application::DS
           );
        }
        if(empty($this->data('dir.priya.restore'))){
            $this->data('dir.priya.restore',
                $this->data('dir.priya.data') .
                Application::RESTORE .
                Application::DS
           );
        }
        if(empty($this->data('dir.priya.update'))){
            $this->data('dir.priya.update',
                $this->data('dir.priya.data') .
                Application::UPDATE .
                Application::DS
            );
        }
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
        $this->route()->create('Application.Restore');
        $this->route()->create('Application.Pull');
        $this->route()->create('Application.Push');
//         $this->route()->create('Application.Install');
//         $this->route()->create('Application.Update');
//         $this->route()->create('Application.Config');
//         $this->route()->create('Application.User');
    }

    public function run(){
        if(!headers_sent()){
            header('Last-Modified: '. $this->request('lastModified'));
        }
        $request = $this->request('request');

        $tmp = explode('.', $request);
        $ext = strtolower(end($tmp));
        $url = $this->data('dir.vendor') . str_replace('/', Application::DS, $request);

        $allowed_contentType = $this->data('contentType');
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
        $contentType = $handler->request('contentType');
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
                if($request == 'Application/Error/'){
                    trigger_error('cannot route to Application/Error/', E_USER_ERROR);
                }
                if($this->route()->error('read')){
                    $handler->request('request', 'Application/Error/');
                    $handler->request('id', 2);
                    $this->run();
                } else {
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
//             trigger_error('unknown result');
            var_dump($result);
            var_dump($item);
        }
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
