<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace Priya\Module\Core;

class Cli extends Result {

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
    }

    public function tput($tput='', $arguments=array()){
        if(!is_array($arguments)){
            $arguments = (array) $arguments;
        }
        switch(strtolower($tput)){
            case 'screen.save' :
            case 'screen.write' :
                $tput = 'smcup';
            break;
            case 'screen.restore' :
                $tput = 'rmcup';
            break;
            case 'home' :
            case 'cursor.home':
                $tput = 'home';
            break;
            case 'cursor.invisible' :
                $tput = 'civis';
            break;
            case 'cursor.normal' :
                $tput = 'cnorm';
            break;
            case 'cursor.save' :
            case 'cursor.write' :
                $tput = 'sc';
            break;
            case 'cursor.restore' :
                $tput = 'rc';
            break;
            case 'color' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 9; //9 = default
                $tput = 'setaf ' . $color;
            break;
            case 'background' :
                $color = isset($arguments[0]) ? (int) $arguments[0] : 0; //9 = default
                $tput = 'setab ' . $color;
                break;
            case 'cursor.up' :
            case 'up' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cuu' . $amount;
            break;
            case 'cursor.down' :
            case 'down' :
                $amount = isset($arguments[0]) ? (int) $arguments[0] : 1;
                $tput = 'cud' . $amount;
            break;
            case 'cursor.position' :
            case 'position' :
                $cols = isset($arguments[0]) ? (int) $arguments[0] : 0; //x
                $rows = isset($arguments[1]) ? (int) $arguments[1] : 0; //y
                $tput = 'cup ' . $rows . ' ' . $cols;
                break;
            case 'rows':
            case 'row':
            case 'height':
                $tput = 'lines';
                break;
            case 'width':
            case 'columns':
            case 'column' :
                $tput = 'cols';
            break;
            case 'default':
            case 'reset':
                $tput  = 'sgr0';
            break;
        }
        ob_start();
        $result = system('tput ' . $tput);
        ob_end_clean();
        return $result;
    }

    public function color($color='', $background=''){
        $result = '';
        if(!empty($color) || ($color === (0 || '0'))){
            if($color == 'reset' || $color == 'default'){
                $result .= "\e[0m";
            } else {
                $result .= "\e[38;5;" . intval($color) . 'm';
            }
        }
        if(!empty($background) || $background == (0 || '0')){
            $result .= "\e[48;5;" . intval($background) . 'm';
        }
        return $result;
    }

    public function read($url='', $text='', $read=''){
        ob_flush();
        if($url=='input'){
            echo $text;
            ob_flush();
//             system('stty -echo');
            $input = trim(fgets(STDIN));
//             system('stty echo');
//             echo PHP_EOL;

//             readline_completion_function(array($this, 'complete'));
//             $input = rtrim(readline($text), ' ');
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

    public function grid($start_x=null, $start_y=null, $width=null, $height=null, $color=9, $background=0){
        $grid = array();
        if($height === null || $height == 'auto'){
            $height =
            null !== $this->data('priya.terminal.screen.grid.height') ?
            $this->data('priya.terminal.screen.grid.height') :
            $this->tput('height')
            ;
        }
        if($width === null || $width == 'auto'){
            $width =
            null !== $this->data('priya.terminal.screen.grid.width') ?
            $this->data('priya.terminal.screen.grid.width') :
            $this->tput('width')
            ;
        }
        if(empty($start_y) && ($start_y !== 0 || $start_y !== '0')){
            $start_y =
                null !== $this->data('priya.terminal.screen.grid.y') ?
                $this->data('priya.terminal.screen.grid.y') :
                0
            ;
        }
        if(empty($start_x) && ($start_x!== 0 || $start_x !== '0')){
            $start_x =
                null !== $this->data('priya.terminal.screen.grid.x') ?
                $this->data('priya.terminal.screen.grid.x') :
                0
            ;
        }
        if(!is_numeric($start_y)){
            $start_y += 0;
        }
        if(!is_numeric($start_x)){
            $start_x += 0;
        }
        for($y=$start_y; $y < ($height + $start_y); $y++){
            for($x=$start_x; $x < ($width + $start_x); $x++){
                $char = ' ';
                $grid[$y][$x]['x'] = $x;
                $grid[$y][$x]['y'] = $y;
                $grid[$y][$x]['color'] = $color;
                $grid[$y][$x]['background'] = $background;
                $grid[$y][$x]['char'] = $char;
            }
        }
        $this->data('priya.terminal.screen.grid.content', $grid);
        return $grid;
    }

    public function screen($grid=array(), $timeout=null){
        $content = array();
        if(!is_array($grid)){
            return;
        }
        $this->output($this->tput('clear'));
        $this->output($this->tput('home'));

        foreach($grid as $y => $list_y){
            $row = '';
            foreach($list_y as $x => $pointer){
                if(!isset($pointer['color'])){
                    var_dump($x);
                    var_dump($y);
                    var_dump($pointer);
                    die;
                }
                $row .= $this->color($pointer['color'], $pointer['background']) . $pointer['char'];
            }
            $content[] = $row;
        }
        $this->output(implode('', $content));
        $this->output($this->tput('reset'));
        $this->output($this->tput('home'));
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
    }

}