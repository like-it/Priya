<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */
namespace Priya\Module\Core;

class Cli extends Result {

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
    }

    public function tput($tput=''){
        switch(strtolower($tput)){
            case 'rows':
                $tput = 'lines';
                break;
            case 'columns':
                $tput = 'cols';
            break;
            case 'default':
                $tput  = 'sgr0';
            break;
        }
        ob_start();
        $result = system('tput ' . $tput);
        ob_end_clean();
        return $result;
    }

    public function color($color='', $background=''){
        ob_flush();
        if(!empty($color) || ($color === 0 || $color ==  '0')){
            if($color == 'reset'){
                echo "\e[0m";
//                 echo "\007";
            } else {
                echo "\e[38;5;" . intval($color) . 'm';
            }
        }
        if(!empty($background) || $background=== (0 || '0')){
            echo "\e[48;5;" . intval($background) . 'm';
        }
        /*
        switch($color){
            case 'red':
                echo "\033[31m";
            break;
            case 'green':
                echo "\033[32m";
            break;
            case 'test':
                echo "\e[38;5;82mHello \e[38;5;198mWorld";
            case 'end':
                echo "\033[0m";
            break;
        }
        */
    }

    public function read($url='', $text='', $read=''){
        ob_flush();
        if($url=='input'){
            readline_completion_function(array($this, 'complete'));
//             $this->color('reset');
//             var_dump('reset');
            //add history as array in this.data('history')
            $input = rtrim(readline($text), ' ');
        }
        elseif($url=='input-hidden'){
            echo $text;
            ob_flush();
            system('stty -echo');
            $input = trim(fgets(STDIN));
            system('stty echo');
            echo PHP_EOL;
        } else {
            return parent::read($url);
        }
        return $input;
    }

    public function write($url='', $text='', $class=''){
        if($url=='output'){
            if(!empty($class)){
                $color = $this->data($class . '.color');
                $background = $this->data($class . '.background.color');
                $this->color($color, $background);
            }
            echo $text;
            $this->color('reset');
            ob_flush();
        } else {
            return parent::write($url);
        }
    }

    public function input($text='', $hidden=false, $timout=false){
        if(empty($hidden)){
            return $this->read('input', $text, $timout);
        } else {
            return $this->read('input-hidden', $text, $timout);
        }
    }

    public function output($text='', $class=''){
        if(is_array($text) || is_object($text)){
            $text = json_encode($text, JSON_PRETTY_PRINT);
        }
        return $this->write('output', $text, $class);

    }

    public function complete($text=''){
        if(empty($text)){
            echo '/';
        }
//         var_dump($text);
    }

}