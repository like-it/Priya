<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Core;

use stdClass;
use Priya\Application;
use Priya\Module\Handler;
use Priya\Module\Route;
use Priya\Module\Autoload;
use Priya\Module\Autoload\Tpl;
use Priya\Module\File;
use Priya\Module\File\Dir;
use Priya\Module\Core\Parser;

class Result extends Parser {
    const DIR = __DIR__;

    private $result;

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
        $this->data('module', $this->module());
        $this->data('web.root', $this->handler()->web());
        if($this->data('web.root') !== false){
            $namespace = explode('\\', $this->data('module'));
            $class = array_pop($namespace);
            $this->data('web.public', $this->data('web.root') . $this->data('public_html') . '/');
            if(empty($namespace)){
                $this->data('web.module.root', $this->data('web.root') . $class . '/');
            } else {
                $this->data('web.module.root', $this->data('web.root') . implode('/', $namespace) . '/');
            }
            $this->data('web.module.public', $this->data('web.module.root') . $this->data('public_html') . '/');
            $this->data('web.module.class', $this->data('web.module.root') . $class . '/');
            $this->data('url', $this->handler()->url());
            $this->data('dir.public', $this->handler()->webRoot());
        } else {
            $this->data('delete', 'web');
        }
        $dir_module = $this->data('dir.module');
        if(empty($dir_module)){
            $this->data('dir.module.root',
                    dirname(dirname(Application::DIR)) .
                    Application::DS .
                    Application::MODULE .
                    Application::DS .
                    $this->module() .
                    Application::DS
            );
            $this->data('dir.module.data',
                    $this->data('dir.module.root') .
                    Application::DATA .
                    Application::DS
            );

            $this->data('dir.module.public',
                    $this->data('dir.module.root') .
                    $this->data('public_html') .
                    Application::DS
            );
        }
        $ignore = array();
        $ignore[] = 'users';
        $ignore[] = 'contentType';
// 		$ignore[] = 'autoload';
        $this->data('ignore', $ignore);
    }

    public function result($type=null, $result=''){
        if($type == 'template'){
            return $this->result($this->template('create', $result));
        }
        elseif($type == 'cli'){
            return $this->result($this->cli('create', $result));
        } else {
            $this->setResult($type);
        }
        return $this->getResult();
    }

    private function setResult($result=''){
        $this->result = $result;
    }

    private function getResult(){
        return $this->result;
    }

    public function template($type=null, $template=null){
        if($type !== null){
            if($type == 'create'){
                $this->setTemplate($this->createTemplate($template));
                return $this->getTemplate();
            } else {
                $this->setTemplate($type);
            }
        }
        return $this->getTemplate();
    }

    private function setTemplate($template=''){
        $this->template = $template;
    }

    private function getTemplate(){
        return $this->template;
    }

    public function cli($cli=null, $template=null){
        if($cli !== null){
            if($cli == 'create'){
                $this->setCli($this->createCli($template));
                return $this->getCli();
            } else {
                $this->setCli($cli);
            }
        }
        return $this->getCli();
    }

    private function setCli($cli=''){
        $this->cli = $cli;
    }

    private function getCli(){
        return $this->cli;
    }

    public function createCli($template=''){
        $template_list = (array) $this->locateTemplate($template, 'tpl.php');
        foreach($template_list as $template){
            require $template;
        }
        return Handler::CONTENT_TYPE_CLI;
    }

    public function createTemplate($template=''){
        $contentType = $this->request('contentType');
        $data = $this->data();
        if(empty($template) && isset($data->contentType) && isset($data->contentType->{$contentType}) && isset($data->contentType->{$contentType}->template)){
            $list = $data->contentType->{$contentType}->template;
            $template_list = array();
            foreach($list as $template){
                $url = $this->locateTemplate($template);
                if(!empty($url)){
                    $template_list[] = $url;
                }
            }
        } else {
            if(is_array($template)){
                foreach($template as $tpl){
                    $url = $this->locateTemplate($tpl);
                    if(!empty($url)){
                        $template_list[] = $url;
                    }
                }
            } else {
                $template_list = (array) $this->locateTemplate($template);
            }
        }
        $url = array_shift($template_list);
        if(empty($url)){
            if(empty($template_list)){
                return $this->template(false);
            } else {
                $url = array_shift($template_list);
            }
        }
        $dir_priya = dirname(dirname(Application::DIR)) . Application::DS;
        $dir_module_smarty =
            $dir_priya .
            'Module' .
            Application::DS .
            'Smarty' .
            Application::DS
        ;

        $dir_cache =
            $dir_module_smarty  .
            Application::DATA .
            Application::DS
        ;
        $dir_compile = $dir_cache . 'Compile' .	Application::DS;
        $dir_cache .=  'Cache' .	Application::DS;
        if(is_dir($dir_compile) === false){
            mkdir($dir_compile, Dir::CHMOD, true);
        }
        if(is_dir($dir_cache) === false){
            mkdir($dir_cache, Dir::CHMOD, true);
        }
        if(is_dir($dir_compile) === false){
            trigger_error('unable to create compile dir', E_USER_ERROR);
        }
        if(is_dir($dir_cache) === false){
            trigger_error('unable to create cache dir', E_USER_ERROR);
        }
        $this->url($url);
        $dir = dirname($url);
        $cwd = getcwd();
        chdir($dir);
        $functions = spl_autoload_functions();
        foreach($functions as $function) {
            spl_autoload_unregister($function);
        }
        restore_error_handler();
        restore_exception_handler();

        $dir_vendor = dirname($dir_priya) . Application::DS;
        $dir_smarty =
            $dir_vendor .
            'Smarty' .
            Application::DS .
            'libs' .
            Application::DS
        ;
        require_once $dir_smarty . 'Smarty.class.php';
        $smarty = new \Smarty();

        $func = spl_autoload_functions();
        if(empty($func)){
            \Smarty_Autoloader::register();
        }
        $dir_template = '';
        $class = get_called_class();
        if($class::DIR){
            $dir_template = dirname($class::DIR) . Application::DS . Application::TEMPLATE . Application::DS;
        }
        $smarty->setTemplateDir($dir_template);
        $smarty->setCompileDir($dir_compile);
        $smarty->setCacheDir($dir_cache);
        $smarty->setConfigDir('');
        $smarty->addPluginsDir($dir_module_smarty . Application::PLUGIN . Application::DS);	//priya plugins...
        $smarty->assign('class', $this->dom_class($class));
        $smarty->assign('template_list', $template_list);

        $plugin_dir = $this->data('smarty.dir.plugin');
        if(!is_array($plugin_dir)){
            $plugin_dir = (array) $plugin_dir;
        }
        foreach($plugin_dir as $location){
            $location = File::dir($location);
            if(is_dir($location)){
                $smarty->addPluginsDir($location);	//own plugins...
            }
        }
        $data = $this->object($this->data(), 'array');

        $ignore = $this->object($this->data('ignore'), 'array');
        if(is_array($data)){
            foreach($data as $key => $value){
                if(in_array($key, $ignore)){
                    continue;
                }
                $smarty->assign($key, $value);
            }
        }
        $smarty->assign('request', $this->object($this->request(), 'array'));
        $session = $this->object($this->session(), 'array');
        $smarty->assign('session', $session);
        if(!empty($session['user'])){
            $smarty->assign('user', $session['user']);
        }
        $error = array();
        if(!empty($session['error'])){
            $error = $session['error'];
            $this->session('delete', 'error');
        }
        $route = $this->route();
        $smarty->assign('route', $this->object($route->data(), 'array'));
        if(get_class($route) == 'Priya\Module\Route'){
            $error = $this->object_merge($error, $this->object_merge($this->object($this->error(), 'array'), $this->object($route->error(), 'array')));
        } else {
            $error = $this->object_merge($error, $this->object($this->error(), 'array'));
        }
        $smarty->assign('error', $error);
        $message = array();
        if(!empty($session['message'])){
            $message = $session['message'];
            $this->session('delete', 'message');
        }
        $message = $this->object_merge($message, $this->object($this->message(), 'array'));
        $smarty->assign('message', $message);
        if(isset($data['contentType']) && isset($data['contentType'][$contentType]) && isset($data['contentType'][$contentType]['script'])){
            $smarty->assign('script', $data['contentType'][$contentType]['script']);
        } else {
            $smarty->assign('script', array());
        }
        if(isset($data['contentType']) && isset($data['contentType'][$contentType]) && isset($data['contentType'][$contentType]['link'])){
            $smarty->assign('link', $data['contentType'][$contentType]['link']);
        } else {
            $smarty->assign('link', array());
        }
        if($contentType == Handler::CONTENT_TYPE_JSON){
            $target = $this->request('target');
            if(empty($target)){
                $target = $this->data('target');
            }
            if(empty($target)){
                $target = 'body';
            }
            $method = $this->request('method');
            if(empty($method)){
                $method = $this->data('method');
            }
            if(empty($method)){
                $method = 'append';
            }
            $smarty->assign('target', $target);
            $smarty->assign('method', $method);
        }
        $smarty->assign('fetch', $url);
        $fetch = trim($smarty->fetch($url));

        set_exception_handler(array('Priya\Module\Core','handler_exception'));
        set_error_handler(array('Priya\Module\Core','handler_error'));
        if($contentType == Handler::CONTENT_TYPE_JSON){
            $object = new stdClass();
            $object->html = $fetch;
            $variable = $smarty->getTemplateVars();

            if(isset($variable['link'])){
                if(is_string($variable['link'])){
                    $variable['link'] = (array) $variable['link'];
                }
                $object->link = $variable['link'];
            } else {
                $object->link = array();
            }
            if(isset($variable['script'])){
                if(is_string($variable['script'])){
                    $variable['script'] = (array) $variable['script'];
                }
                $object->script = $variable['script'];
            } else {
                $object->script = array();
            }
            if(isset($variable['target'])){
                $object->target = $variable['target'];
            }
            if(isset($variable['method'])){
                $object->method = $variable['method'];
            }
            if(isset($variable['refresh'])){
                $object->refresh = $variable['refresh'];
            }
            $result = $this->template($object);
        } else {
            $result = $this->template($fetch);
        }
        $func = spl_autoload_functions();
        foreach($func as $function){
            spl_autoload_unregister($function);
        }
        foreach($functions as $function) {
            spl_autoload_register($function);
        }
        chdir($cwd);
        return $result;
    }

    public function locateTemplate($template='', $extension='tpl', $caller=''){
        $namespace = '';
        if(empty($template)){
            $template = get_called_class();
        }
        $tmp = explode('\\', trim($template,'\\'));
        $class = array_pop($tmp);
        $namespace = implode('\\', $tmp);

        $directory = explode(Application::DS, Application::DIR);
        array_pop($directory);
        array_pop($directory);
        $priya = array_pop($directory);
        $directory = implode(Application::DS, $directory) . Application::DS;
        if(empty($namespace)){
            $namespace = $priya . '\\' . Application::MODULE;
        }
        $directory .= str_replace('\\', Application::DS, $namespace) . Application::DS;
        $tpl = new Tpl();

        if(empty($caller)){
            $caller = get_called_class();
        }
        if($caller::DIR){
            $dir = dirname($caller::DIR) . Application::DS . Application::TEMPLATE . Application::DS;
            $tpl->addPrefix('none', $dir, $extension);
        }
        $autoload = $this->data('autoload');
        if(is_object($autoload) || is_array($autoload)){
            foreach($autoload as $prefix => $dir){
                $tpl->addPrefix($prefix, $dir, $extension);
            }
        }
        $tpl->addPrefix($namespace, $directory, $extension);

        $environment = $this->data('environment');
        if(!empty($environment)){
            $tpl->environment($environment);
        }
        $url = $tpl->tpl_load($template);
        if(empty($url)){
            return false;
        } else {
            return $url;
        }
    }

    public function application(){
        $autoload = new Autoload();
        $autoload->addPrefix('Priya',  $this->data('dir.priya.application'));
        $autoload->register();
        $autoload->environment(Application::ENVIRONMENT);

        $application = new Application($autoload);
        $handler = $application->handler();
        $handler->request($this->request());
        return $application->run();
    }
}