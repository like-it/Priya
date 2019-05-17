<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_color($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $color = array_shift($argumentList);
    $background = array_shift($argumentList);

    $reset = false;
    switch($color){
        case 'white' :
            $color = 15; //15
        break;
        case 'black' :
            $color = 0; //0
        break;
        case 'green' :
            $color = 2; //2
        break;
        case 'red' :
            $color = 1; //1
        break;
        case 'yellow' :
            $color = 3; //3
        break;
        case 'lightgrey' :
        case 'light-grey' :
            $color = 7; //7
        break;
        case 'grey' :
            $color = 8; //7
        break;
        case 'blue' :
            $color = 4; //4
        break;
        case 'green-blue' :
        case 'greenblue' :
            $color = 6; //6
        break;
        case 'light-green-blue' :
        case 'lightgreenblue' :
            $color = 14; //14
        break;
        case 'light-blue' :
        case 'lightblue' :
            $color = 12; //12
        break;
        case 'light-green' :
        case 'lightgreen' :
            $color = 10; //10
        break;
        case 'light-red' :
        case 'lightred' :
            $color = 9; //9
        break;
        case 'light-yellow' :
        case 'lightyellow' :
            $color = 11; //11
        break;
        case 'purple' :        
            $color = 5;  //5
        break;
        case 'light-purple' :        
        case 'lightpurple' :        
            $color = 13;  //5
        break;
        case 'reset' :        
            $reset = true; 
        break;
    }
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $argument = [];
    if($reset === true){
        $command = 'reset';        
    } else {
        $command = 'color';
        $argument[] = $color;
    }
    $function['execute'] = $cli->tput($command, $argument);    
    return $function;
}