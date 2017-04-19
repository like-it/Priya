<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Cli\Application;

use stdClass;
use Priya\Application;
use Priya\Module\Core\Cli;
use Priya\Module\Data;
use Priya\Module\Parser;
use Priya\Module\File;
use Priya\Module\File\Dir;

class Build extends Cli {
    const DIR = __DIR__;

    public function run(){
        $package = $this->parameter(2);
        $target = $this->parameter(3);

        if(empty($package)){
            $this->error('package', true);
        }
        if(file_exists($package) === false){
            $this->error('package', true);
        }
        if(empty($target)){
            $this->error('target', true);
        }
        $this->build($package, $target);
        return $this->result('cli');
    }

    public function build($package='', $target=''){
        $target = File::dir($target);
        $data = new Parser();
        $data->data($this->data());
        $data->data('delete', 'server');
        $data->data('delete', 'autoload');
        $data->read($package);
        $vendor = new stdClass();

        if(isset($this->autoload()->prefixList)){
            foreach ($this->autoload()->prefixList as $load){
                if(empty($load['prefix'])){
                    continue;
                }
                if(empty($load['directory'])){
                    continue;
                }
                if($load['prefix'] != 'Priya'){
                    continue;
                }
                $vendor->Priya = $load['directory'];
                break;
            }
            if(empty($data->data('package'))){
                $this->error('autoload', true);
                return false;
            }
            foreach($data->data('package') as $prefix => $node){
                $vendor->{$prefix} = $node;
            }
        }
        if(file_exists($target) === false){
            mkdir($target, Dir::CHMOD, true);
        }
        $dir = new Dir();
        $dir->ignore($data->data('ignore'));
        foreach($vendor as $prefix => $url){
            $read = $dir->read($url, true);
            if(!empty($read)){
                $temp = explode($this->data('dir.root'), $url, 2);
                array_shift($temp);
                $url_target = $target . implode($this->data('dir.root'), $temp);
                $dir->delete($url_target);
                if(is_dir($url_target) === false){
                    mkdir($url_target, Dir::CHMOD, true);
                }
                foreach($read as $file){
                    $temp = explode($this->data('dir.root'), $file->url, 2);
                    array_shift($temp);
                    $file->target = $target . implode($this->data('dir.root'), $temp);
                }
                $this->copy($read);
            }
        }
        $read = $dir->read($data->data('dir.data'), true);
        if(!empty($read)){
            foreach($read as $file){
                $temp = explode($this->data('dir.root'), $file->url, 2);
                array_shift($temp);
                $file->target = $target . implode($this->data('dir.root'), $temp);
            }
            $this->copy($read);
        }
        $dir->ignore('delete');

        $read = $dir->read($target, true);
        $priya = $this->findPriya($read);
        if(!empty($priya)){
            exec('php ' . $priya->url . ' Config', $output);
            $config = $this->object(implode('', $output));
            $build = new Data();
            $build->data($config);
            $list = $this->createRouteList($read);
            $this->createRoute($list, $build);
            $url = $build->data('dir.data') . Application::CONFIG;
            $data= new Data();
            $data->read($package);

            $config = new Data();
            $config->read($this->data('dir.data') . Application::CONFIG);

            $autoload = $data->data('package');

            $ignore = array();
            $ignore[] = 'ignore';
            $ignore[] = 'package';

            foreach($data->data() as $key => $value){
                if(in_array($key, $ignore)){
                    $data->data('delete', $key);
                }
            }
            $config->data($this->object_merge($config->data(), $data->data()));
            $config->data('autoload', $autoload);
            $config->write($url);

            $public = $build->data('dir.public');
            if(empty($public)){
                $this->error('public', true);
                return false;
            }
            if(is_dir($public) === false){
                mkdir($public, Dir::CHMOD, true);
            }
            elseif(file_exists($public) && is_dir($public) === false){
                $this->error('dir', true);
                return false;
            }
            $list = array();
            $list[] = '.htaccess';
            $list[] = 'index.php';
            foreach ($list as $file){
                if(file_exists($build->data('dir.module.data') . $file) && !file_exists($public . $file)){
                    copy($build->data('dir.module.data') . $file, $public . $file);
                    touch($public . $file, filemtime($build->data('dir.module.data') . $file));
                }
            }
            return true;
        } else {
            $this->error('priya', true);
            //trigger error cannot start priya
            return false;
        }
    }

    public function copy($copy = null){
        krsort($copy);
        foreach ($copy as $file){
            if(isset($file->type) && $file->type == 'dir'){
                if(is_dir($file->target)){
                    continue;
                }
                mkdir($file->target, Dir::CHMOD, true);
            }
        }
        foreach ($copy as $file){
            if(isset($file->type) && $file->type == 'file'){
                $dir = dirname($file->target);
                if(is_dir($dir) === false){
                    mkdir($dir, Dir::CHMOD, true);
                }
                if(file_exists($file->target) && filemtime($file->target) == filemtime($file->url)){
                    continue;
                }
                copy($file->url, $file->target);
                touch($file->target, filemtime($file->url));
            }
        }
    }

    private function createRoute($list=array(), $build=''){
        $data = new Data();
        foreach($list as $nr => $node){
            if($node->url == $this->data('dir.data') . Application::ROUTE){
                continue;
            }
            if(stristr($node->url, $build->data('dir.priya.module')) !== false){
                $url = str_replace($build->data('dir.priya.module'), '', $node->url);
                $tmp = explode('.', $url);
                array_pop($tmp);
                $data->data(implode('/', $tmp) . '.resource', '{$dir.priya.module}' . $url);
            }
            elseif(stristr($node->url, $build->data('dir.vendor'))){
                $url = str_replace($build->data('dir.vendor'), '', $node->url);
                $tmp = explode('.', $url);
                array_pop($tmp);
                $data->data(implode('/', $tmp) . '.resource', '{$dir.vendor}' . $url);
            }
        }
        $data->write($build->data('dir.data') . Application::ROUTE);
    }

    private function findPriya($fileList=array()){
        foreach($fileList as $nr => $node){
            if($node->name == 'Priya.php' && stristr($node->url, 'application')){
                return $node;
            }
        }
        return false;
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
