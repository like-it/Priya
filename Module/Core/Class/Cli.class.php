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

    public function color($color='', $background=''){
        switch($color){
            case 'red':
                return "\033[31m";
            break;
            case 'green':
                return "\033[32m";
            break;
            case 'end':
                return "\033[0m";
            break;
            break;
        }
    }

    public function read($type='', $url='', $read=''){
        if($type=='input'){
            readline_completion_function(array($this, 'complete'));
            $input = readline($url);
        }
        elseif($type=='input-hidden'){
            echo $url;
            system('stty -echo');
            $input = trim(fgets(STDIN));
            system('stty echo');
            echo PHP_EOL;
        } else {
            return parent::read($type);
        }
        return $input;
    }


    public function complete($text=''){
        var_dump($text);
    }

}