<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-01-10
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module;

use stdClass;
use Priya\Application;
use Priya\Module\Core\Data;
use PHPMailer\PHPMailer\PHPMailer;

class Mail extends Data{
    private $mailer;

    public function __construct($handler=null, $route=null, $data=null){
        $this->data($handler);
        $this->mailer('create');
    }

    public function mailer($mailer=null){
        if($mailer != null){
            if($mailer == 'create'){
                return $this->createMailer();
            } else {
                $this->setMailer($mailer);
            }
        }
        return $this->getMailer();
    }

    private function setMailer($mailer=''){
        $this->mailer = $mailer;
    }

    private function getMailer(){
        return $this->mailer;
    }

    private function createMailer(){
        $debug = $this->data('mail.debug');
        $host = $this->data('mail.host');
        $port = $this->data('mail.port');
        $secure = $this->data('mail.secure');
        $username = $this->data('mail.username');
        $password = $this->data('mail.password');
        if(empty($host)){
            return false;
        }
        if(empty($port)){
            return false;
        }
        if(empty($secure)){
            return false;
        }
        if(empty($username)){
            return false;
        }
        if(empty($password)){
            return false;
        }
        $this->setMailer(new PHPMailer());
        $this->mailer()->isSMTP();
        $this->mailer()->isHTML(true);
        if($debug !== false && $debug !== null){
            $this->mailer()->SMTPDebug = $debug;
        }
        $this->mailer()->Host = $host;
        $this->mailer()->Port = $port;
        $this->mailer()->SMTPSecure = $secure;
        $this->mailer()->SMTPAuth = true;
        $this->mailer()->Username = $username;
        $this->mailer()->Password = $password;
        return $this->getMailer();
    }

    public function __call($name, $arguments){
        $mail = $this->mailer();
        $method = false;
        if(method_exists($mail, $name)){
            $method = $name;
        }
        $name = ucfirst($name);
        if(method_exists($mail, $name)){
            $method = $name;
        }
        if(!empty($method)){
            switch(count($arguments)){
                case 1:
                    return $mail->{$method}($arguments[0]);
                break;
                case 2:
                    return $mail->{$method}($arguments[0], $arguments[1]);
                break;
                case 3:
                    return $mail->{$method}($arguments[0], $arguments[1], $arguments[2]);
                break;
                case 4:
                    return $mail->{$method}($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                break;
            }
        } else {
            return;
        }
    }

    public function send(){
        if(!$this->mailer()->send()) {
            $this->error('mail', $this->mailer()->ErrorInfo);
            return false;
        } else {
            $this->message('mail', true);
            return true;
        }
    }

    public function from($email='', $name=null){
        if($name !== null){
            return $this->mailer()->setFrom($email, $name);
        } else {
            return $this->mailer()->setFrom($email);
        }
    }

    public function to($email='', $name=null){
        if($name !== null){
            return $this->mailer()->addAddress($email, $name);
        } else {
            return $this->mailer()->addAddress($email);
        }
    }

    public function subject($subject=''){
        return $this->mailer()->Subject = $subject;
    }

    public function body($body=''){
        return $this->mailer()->Body = $body;
    }

    public function altBody($altBody=''){
        return $this->mailer()->AltBody = $altBody;
    }
}
?>