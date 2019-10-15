<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */

namespace Priya\Module;

use Priya\Application;

class User extends Data {

    public function validate($username, $password){
        $data = new Data();
        $data->read($this->data('dir.data') . Application::CREDENTIAL);
        $users = $data->data('user');
        if(isset($users) && (is_array($users) || is_object($users))){
            foreach($users as $user){
                if(isset($user->email) && $user->email == $username) {
                    if(isset($user->password)){
                        $verify = password_verify($password, $user->password);
                    } else {
//                         $this->error('pasword-lost', true);
                        return false;
                    }
                    if(empty($verify)){
//                         $this->error('password', true);
                        return false;
                    }
                    unset($user->password);
                    return $user;
                }
                elseif(isset($user->username) && $user->username == $username) {
                    if(isset($user->password)){
                        $verify = password_verify($password, $user->password);
                    } else {
//                         $this->error('pasword-lost', true);
                        return false;
                    }

                    if(empty($verify)){
//                         $this->error('password', true);
                        return false;
                    }
                    unset($user->password);
                    return $user;
                }
            }
        }
//         $this->error('username', true);
        return false;
    }
}