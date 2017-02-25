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
            $this->createPull();
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
                    header("Content-Length: ".filesize($file));
                    header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
                    readfile($file);
                    die;
                }
            } else {
                $this->error('permission', true);
            }
        }
        //post to the server
        /*
        if($this->parameter('create')){
            $this->createPoint();
        }
        if($this->parameter('list')){
            $this->createList();
            $this->cli('create', 'List');
        }
        if($this->parameter('point')){
            $this->restorePoint();
        }
        $this->data('step', 'download');
        $this->cli('create', 'Restore');
        $this->data('step', 'download-complete');
        $this->data('tag', '0.0.4');
        $this->cli('create', 'Restore');
        $this->data('step', 'download-failure');
        $this->cli('create', 'Restore');
        $this->data('step', 'tag');
        $this->cli('create', 'Restore');
        $this->data('step', 'install');
        $this->cli('create', 'Restore');
        $this->data('step', 'install-complete');
        */
        return $this->result('cli');
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
            return;
        }
        if(empty($password)){
            return;
        }
        $url = $this->data('server.url') . $this->route('priya-pull');
        $data = array('user' => $user, 'password' => $password);
        $options = array(
            'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($data),
              'timeout' => $this->data('server.timeout') ? $this->data('server.timeout') : 5
             )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if(is_dir($this->data('dir.priya.update')) === false){
            mkdir($this->data('dir.priya.update'), Dir::CHMOD, true);
        }
        $url = $this->data('dir.priya.update') . $this->data('version') . '.zip';
        $file = new File();
        $file->write($url, $result);
    }
}
