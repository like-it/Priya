<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Cli\Application;
use Priya\Module\Core\Data;
use Priya\Module\Core\Cli;
use Priya\Module\File\Dir;
use Priya\Application;

class Install extends Cli {
    const DIR = __DIR__;

    private $fileList;

    public function run(){
        if(empty($this->data('dir.data'))){
            $this->error('data', 'Corrupted data, cannot install...');
            $this->data('step', 'error-fatal');
            return $this->result('cli');
        }
        if(file_exists($this->data('dir.data') . Application::CONFIG) === false){
            $this->data('step', 'config');
            $this->cli('create', 'Install');

            $config = new Config($this->handler(), $this->route());
            $config->data($this->data());
            $config->createConfig();
            $this->data('step', 'config-created');
            $this->cli('create', 'Install');
            $this->read($this->data('dir.data') . Application::CONFIG);
        }
        if(file_exists($this->data('dir.data') . Application::CONFIG) === false){
            $this->error('read', $this->data('dir.data') . Application::CONFIG);
            $this->data('step', 'fail');
        }
        if(empty($this->data('dir.public'))){
            $this->error('data', 'Corrupted data, cannot install...');
            $this->data('step', 'error-fatal');
            return $this->result('cli');
        }
        if(file_exists($this->data('dir.public'))){
            $this->data('step', 'public-html-exists');
            $this->cli('create', 'Install');
        } else {
            $this->data('step', 'public-html-create');
            mkdir($this->data('dir.public'), 0777, true);
            $this->cli('create', 'Install');
        }
        $this->data('step', 'file-copy');
        $this->cli('create', 'Install');
        copy($this->data('dir.module.data') . '.htaccess', $this->data('dir.public') . '.htaccess');
        $this->data('step', 'file-copy');
        $this->cli('create', 'Install');
        copy($this->data('dir.module.data') . 'index.php', $this->data('dir.public') . 'index.php');
        $this->data('step', 'module-symlink');
        $this->cli('create', 'Install');
        $fileList = $this->fileList('create', $this->data('dir.root'));
        $list = $this->createPublicModuleList($fileList);
        $this->createSymlink($list);
        $list = $this->createRouteList($fileList);
        $this->createRoute($list);
        $this->data('step', 'route-finish');
        $this->cli('create', 'Install');
        $this->data('step', 'public-html-finish');
        $this->cli('create', 'Install');
        $this->data('step', 'install-finish');
        return $this->result('cli');
        /*
        $this->cli('create', 'Install');
        $this->data('step', 'download');
        $this->cli('create', 'Install');
        $this->data('step', 'download-complete');
        $this->data('tag', '0.0.4');
        $this->cli('create', 'Install');
        $this->data('step', 'download-failure');
        $this->cli('create', 'Install');
        $this->data('step', 'tag');
        $this->cli('create', 'Install');
        $this->data('step', 'install');
        $this->cli('create', 'Install');
        $this->data('step', 'install-complete');
        return $this->result('cli');
        */
    }

    public function fileList($fileList=null, $dir=null){
        if($fileList !== null){
            if($fileList == 'create'){
                return $this->createFileList($dir);
            } else {
                $this->setFileList($fileList);
            }
        }
        return $this->getFileList();
    }

    private function createFileList($directory=''){
        if(is_dir($directory) === false){
            return false;
        }
        $dir = new Dir();
        $dir->ignore('.git');
        $list = $dir->read($directory, true);
        return $this->fileList($list);
    }

    private function setFileList($fileList=array()){
        $this->fileList = $fileList;
    }

    private function getFileList(){
        return $this->fileList;
    }

    private function createSymlink($list=array()){
        foreach($list as $nr => $node){
            $this->data('node', $node);
            if(file_exists($this->data('dir.public') . $node->module) === false){
                if(is_link($this->data('dir.public') . $node->module)){
                    unlink($this->data('dir.public') . $node->module);
                }
//                 exec('ln -s ' . $node->url . ' ' . $this->data('dir.public') . $node->module);
                $this->data('step', 'symlink-create');
                $this->cli('create', 'Install');
            } else {
                if(is_link($this->data('dir.public') . $node->module) && readlink($this->data('dir.public') . $node->module) == $node->url){
                    $this->data('step', 'symlink-exists');
                    $this->cli('create', 'Install');
                } else {
                    $this->data('step', 'symlink-error');
                    $this->cli('create', 'Install');
                }
            }
        }
    }

    private function createRoute($list=array()){
        $data = new Data();
        if(file_exists($this->data('dir.data') . Application::ROUTE)){
            $data->read($this->data('dir.data') . Application::ROUTE);
        }
        foreach($list as $nr => $node){
            if($node->url == $this->data('dir.data') . Application::ROUTE){
                continue;
            }

            if(stristr($node->url, $this->data('dir.priya.module')) !== false){
                $url = str_replace($this->data('dir.priya.module'), '', $node->url);
                $tmp = explode('.', $url);
                array_pop($tmp);
                $data->data(implode('/', $tmp) . '.resource', '{$dir.priya.module}' . $url);
            }
            elseif(stristr($node->url, $this->data('dir.vendor'))){
                $url = str_replace($this->data('dir.vendor'), '', $node->url);
                $tmp = explode('.', $url);
                array_pop($tmp);
                $data->data(implode('/', $tmp) . '.resource', '{$dir.vendor}' . $url);
            }
        }
        $data->write($this->data('dir.data') . Application::ROUTE);
    }

    private function createPublicModuleList($fileList=array()){
        $nodeList = array();
        foreach($fileList as $nr => $node){
            if($node->type != 'dir'){
                continue;
            }
            $tmp = explode(Application::DS, trim(ltrim($node->url, $this->data('dir.priya.module')),Application::DS) . Application::DS);
            $module = array_shift($tmp);
            $url = implode(Application::DS, $tmp);
            if($url == Application::DATA . Application::DS . Application::PUBLIC_HTML . Application::DS){
                $node->module = $module;
                $nodeList[] = $node;
            }
        }
        return $nodeList;
    }

    private function createRouteList($fileList=array()){
        $nodeList = array();
        foreach($fileList as $nr => $node){
            if($node->name == 'Route.json'){
                $nodeList[] = $node;
            }
        }
        return $nodeList;
    }
}
