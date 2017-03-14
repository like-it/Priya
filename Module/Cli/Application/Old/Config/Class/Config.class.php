<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Cli\Application;
use Priya\Module\Core\Cli;
use Priya\Module\Core\Data;
use Priya\Application;

class Config extends Cli {
    const DIR = __DIR__;

    public function run(){
        $read = $this->read($this->data('dir.data') . Application::CONFIG);
        if(empty($read)){
            $this->error('read', true);
        }
        if($this->parameter('get')){
            $this->createGet();
        }
        elseif($this->parameter('set')){
            $this->createSet();
        }
        elseif($this->parameter('delete')){
            $this->createDelete();
        }
        elseif($this->parameter('create')){
            $this->createConfig($this->parameter('force') ? $this->parameter('force') : false);
            $this->error('delete', 'read');
        }
        return $this->result('cli');
    }

    private function createSet(){
        $data = $this->request('data');
        array_shift($data);
        array_shift($data);
        $method = array_shift($data);
        $attribute = array_shift($data);
        $value = implode(' ', $data);
        if($this->parameter('public_html')){
            /* @todo
            if(is_dir($this->data('dir.root') . $value)){
                //move every file to this directory...
                $this->data($attribute, $value);
                return;
            }
            */
            if(file_exists($this->data('dir.root') . $value)){
                $this->error('public_html', $this->data('dir.root') . $value);
                return;
            }
            rename($this->data('dir.root') . $this->data('public_html'), $this->data('dir.root') . $value);
            $this->data($attribute, $value);
            $data = new Data();
            $data->read($this->data('dir.data') . Application::CONFIG);
            $data->data($attribute, $value);
            $this->createConfig(true, $data);
        }
        elseif($this->parameter('environment')){
            $this->data($attribute, $value);
            $data = new Data();
            $data->read($this->data('dir.data') . Application::CONFIG);
            $data->data($attribute, $value);
            $this->createConfig(true, $data);
        } else {
            $this->data($attribute, $value);
            $data = new Data();
            $data->read($this->data('dir.data') . Application::CONFIG);
            $data->data($attribute, $value);
            $this->createConfig(true, $data);
        }

    }

    private function createGet(){
        $data = $this->request('data');
        array_shift($data);
        array_shift($data);
        $method = array_shift($data);
        $attribute = implode('.', $data);
        $this->data('attribute', $this->data($attribute));
        return $this->data('attribute');
    }

    private function createDelete(){
        $data = $this->request('data');
        array_shift($data);
        array_shift($data);
        $method = array_shift($data);
        $attribute = array_shift($data);
        $value = implode(' ', $data);
        if($this->parameter('public_html')){
                $this->error('public_html', 'Cannot delete public_html.');
                return;
        } else {
            $this->data('delete', $attribute);
            $data = new Data();
            $data->read($this->data('dir.data') . Application::CONFIG);
            $data->data('delete', $attribute);
            $this->createConfig(true, $data);
        }

    }

    public function createConfig($force=false, $data=null){
        if($data === null){
            $data = new Data();
            $data->data('public_html', Application::PUBLIC_HTML);
            $data->data('environment', Application::ENVIRONMENT);
        }
        $dir_web = $this->data('dir.public');

        if(file_exists($this->data('dir.data') . Application::CONFIG) && empty($force)){
            $this->error('file_exists', true);
            return false;
        }
        $data_dir_web = $data->data('dir.public');
        if($data_dir_web === null){
            $data->data('dir.public', '{$dir.root}{$public_html}/');
        }

        $data->data('tag', $this->createTag(Application::TAG));
        $data->data('download.url', 'https://bitbucket.org/remco-pc/home.local/get/' . Application::TAG .'.zip');
        if(is_dir($this->data('dir.data')) === false){
            mkdir($this->data('dir.data'), 0740, true);
        }
        return $data->write($this->data('dir.data') . Application::CONFIG);
    }

    private function createTag($tag='0.0.1'){
        $tmp = explode('.', $tag);
        $day = array_pop($tmp);
        $month = array_pop($tmp);
        if($month > 0){
            $minor = 12;
        }
        $version = array_pop($tmp);
        if($version > 0){
            $month = 12;
        }
        $list = array();
        for($d=1; $d <= $day; $d++){
            for($m=0; $m <= $month; $m++){
                for($v=0; $v <= $version; $v++){
                    $list[] = $v . '.' . $m . '.' . $d;
                }
            }
        }
        return $list;
    }

}
