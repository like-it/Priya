<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;
use Priya\Module\Data;
use Priya\Application;
use Priya\Module\File;
use Priya\Module\Cron;
use Priya\Module\Mail as SendMail;
use Exception;

class Mail extends Cli {
    const NAMESPACE = __NAMESPACE__;
    const NAME = __CLASS__;

    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run($object=null){
        if($object === null){
            $object = $this;
        }
        $class = __CLASS__;
        $object->read($class);
        $command = $object->parameter($class, 1);
        if(in_array($command, $object->data('command'))){
            // do nothing for now...
            // add logger
            // add minesweeper encryption ®aXon
        } else {
            $command = $object->data('default.command');
        }
        if(!method_exists($class, $command)){
            throw new Exception('Command (' . $command . ') not found');
        }
        return $class::{$command}($object);
    }

    public static function info($object){
        $class = __CLASS__;
        $object->data('command', ucfirst(__FUNCTION__));
        return $class::view($object, $object->data('command'));
    }

    public static function config($object){
        $object->input('host: ');
        /*
        Mail::readline('port (587): ');
        Mail::readline('secure: (tls) ');
        Mail::readline('e-mail: ');
        Mail::readline('name: ');
        Mail::readline('username: ');
        Mail::readline('password: ');
        Mail::readline('repeat password: ');
        */
        dd($object->data('mail'));


        //use grid to make in some style...
//         Mail::interactive();
//         $class = __CLASS__;
//         $object->data('command', ucfirst(__FUNCTION__));
//         return $class::view($object, $object->data('command'));
    }

    public static function send($object){
        $to = $object->input('to: ');
        $subject = $object->input('subject: ');
        $body = '<html><head></head><body>';
        $counter = 0;
        while(true){
            if($counter == 0){
                $message = $object->input('message: ');
            } else {
                $message = $object->input('');
            }
            if($message == 'q'){
                break;
            } else {
                $body .= '<p>' . $message . "</p>" . PHP_EOL;
            }
            $counter++;
        }
        $body .= '</body></html>';
        $is_send = false;
        while(true){
            $send = $object->input('send message (y/n): ');
            if($send == 'y'){
                $is_send = true;
                break;
            }
            elseif($send == 'n'){

                break;
            }
        }
        if($is_send === true){
            $mail = new SendMail($object->data());
            $mail->subject($subject);
            $mail->body($body);
            $mail->to($to, $to);
            $is_queued = $mail->send();
            if($is_queued === true){
                return;
            }
            throw new Exception('Mail not send, configuration ok?');
        }
    }

}