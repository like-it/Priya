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
use Priya\Application;
use Priya\Module\Handler;
use Priya\Module\User;
use Priya\Module\File\Dir;
use Priya\Module\File;

class Pull extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->handler()->method() == Handler::METHOD_CLI){
            $this->data('step', 'download');
            $this->cli('create', 'Pull');
            $url = $this->createPull();
            if(!empty($url)){
                $restore = new Restore($this->handler(), $this->route(), $this->data());
                $this->data('step', 'extract');
                $this->cli('create', 'Pull');
                $restore->extract($url, $this->data('dir.root'), true);
                if(file_exists($url)){
                    unlink($url);
                }
                $this->data('step', 'finish');
            }
            return $this->result('cli');
        }
        elseif($this->handler()->method() == Handler::METHOD_POST) {
            set_time_limit($this->data('server.timeout') ? $this->data('server.timeout') : 600);
            $user = new User($this->handler(), $this->route(), $this->data());
            $this->session('user', $user->validate($this->request('user'), $this->request('password')));
            if ($this->permission('has', $this->permission('read'))){
                header("Connection: Keep-alive");
                $restore = new Restore($this->handler(), $this->route(), $this->data());
                $file = $restore->create($this->data('version') . '.zip');
                if(file_exists($file)){
                    header("Content-Type: application/zip");
                    header("Content-Transfer-Encoding: Binary");
                    header("Content-Length: " . filesize($file));
                    header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
                    readfile($file);
                    die;
                } else {
                    $this->error('write', true);
                }
            } else {
                $this->error('permission', true);
            }
            return $this->result('cli');
        }
    }

    private function createPull(){
        $request = $this->request('data');
        $user = false;
        $password = false;
        if(isset($request['2'])){
            $user = $request['2'];
        }
        if(isset($request['3'])){
            $password = $request['3'];
        }
        if(empty($user)){
            $this->error('user', true);
            return false;
        }
        if(empty($password)){
            $this->error('password', true);
            return false;
        }
        $server = $this->data('server.url');
        if(empty($server)){
            $this->error('server', true);
            return false;
        }
        $url = $server . $this->route('priya-pull');
        $data = array('user' => $user, 'password' => $password);
        $options = array(
            'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($data),
              'timeout' => $this->data('server.timeout') ? $this->data('server.timeout') : 600
             )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $data = false;
        if(substr($result, 0, 1) == '{' && substr($result, -1) == '}'){
            $data = json_decode($result);
        }
        if(empty($data)){
            if(is_dir($this->data('dir.priya.update')) === false){
                mkdir($this->data('dir.priya.update'), Dir::CHMOD, true);
            }
            $url = $this->data('dir.priya.update') . $this->data('version') . '.zip';
            $file = new File();
            $file->write($url, $result);
            return $url;
        } else {
            if(isset($data->error)){
                $this->error($data->error);
            }
        }
        return false;
    }
}
