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
use Priya\Module\Core\Data;

class Push extends Cli {
    const DIR = __DIR__;

    public function run(){
        if($this->handler()->method() == Handler::METHOD_CLI){
            $data = new Data();
            $data->read($this->data('dir.data') . Application::CONFIG);
            $major = $data->data('major') ? $data->data('major') : 0;
            $minor = $data->data('minor') ? $data->data('minor') : 0;
            $patch = $data->data('patch') ? $data->data('patch') : -1;
            $patch++;
            $this->data('major', $major);
            $this->data('minor', $minor);
            $this->data('patch', $patch);
            $this->data('version', $this->data('major') . '.' . $this->data('minor') . '.' . $this->data('patch'));
            $data->data('major', $major);
            $data->data('minor', $minor);
            $data->data('patch', $patch);
            $write = $data->write();
            if($write === false){
                $this->error('patch', true);
                return $this->result('cli');
            }
            $this->data('step', 'create');
            $this->cli('create', 'Push');
            $restore = new Restore($this->handler(), $this->route(), $this->data());
            $restore->create();

            $this->data('step', 'push');
            $this->cli('create', 'Push');
            $url = $this->createPush();
            $this->data('step', 'finish');
            return $this->result('cli');
        }
        elseif($this->handler()->method() == Handler::METHOD_POST) {
            set_time_limit($this->data('server.timeout') ? $this->data('server.timeout') : 600);
            $user = new User($this->handler(), $this->route(), $this->data());
            $this->session('user', $user->validate($this->request('user'), $this->request('password')));
            if ($this->permission('has', $this->permission('read'))){
                $upload = $this->upload();
                if(is_array($upload) || is_object($upload)){
                    foreach ($upload as $upload_file){
                        $target = $this->data('dir.priya.restore') . $upload_file['name'];
                        if(file_exists($target)){
                            $this->error('exists', true);
                            continue;
                            //return json failure, should patch first pull the current version maybe only php & json
                        }
                        move_uploaded_file($upload_file['tmp_name'], $target);
                        $restore = new Restore($this->handler(), $this->route(), $this->data());
                        $restore->extract($target, $this->data('dir.root'), true);
                        $this->data('step', 'extract');
                    }
                }

            } else {
                $this->error('permission', true);
            }
            return $this->result('cli');
        }
    }

    private function createPush(){
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
        $filename = $this->data('version') . '.zip';
        if(file_exists($this->data('dir.priya.restore') . $filename) === false){
            $this->error('point', true);
            return false;
        }
        $url = $server . $this->route('priya-push');
        $boundary = 'Priya-boundary-' . md5(time() . '-' . microtime());
        $eol = "\r\n";
        $data = '';
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="user"' . $eol . $eol;
        $data .= $user . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="password"' . $eol . $eol;
        $data .= $password . $eol;
        $data .= '--' . $boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="file[]";';
        $data .= 'filename="' . $filename . '"' . $eol;
        $data .= 'Content-Type: Application/octet-stream' . $eol .$eol;
        $data .= file_get_contents($this->data('dir.priya.restore') . $filename) . $eol;
//         $data .= '--' . $boundary . $eol;
//         $data .= 'Content-Disposition: form-data; name="file[]";';
//         $data .= 'filename="' . $filename . '2"' . $eol;
//         $data .= 'Content-Type: Application/octet-stream' . $eol .$eol;
//         $data .= file_get_contents($this->data('dir.priya.restore') . $filename) . $eol;
        $data .= "--" . $boundary . "--" . $eol . $eol; // finish with two eol's!!

        $params = array('http' => array(
            'method' => 'POST',
            'header' => 'Content-Type: multipart/form-data; boundary=' .
                $boundary . $eol,
            'content' => $data,
            'timeout' => $this->data('server.timeout') ? $this->data('server.timeout') : 600
        ));
        $context = stream_context_create($params);
        $result = file_get_contents($url, false, $context);

        $data = false;
        if(substr($result, 0, 1) == '{' && substr($result, -1) == '}'){
            $data = json_decode($result);
        }
        if(empty($data)){
            $this->message('write');
        } else {
            if(isset($data->error)){
                $this->error($data->error);
            }
        }
    }
}
