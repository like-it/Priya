<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_grid($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);
    $width = array_shift($argumentList);
    $height = array_shift($argumentList);
    $color = array_shift($argumentList);
    $background = array_shift($argumentList);
    if(empty($width)){
        $width = $parser->data('priya.terminal.grid.width');
    }
    if(empty($width) || $width == 'auto'){
        $width = $cli->tput('width');
        $parser->data('priya.terminal.grid.width', $width);
    }
    if(empty($height)){
        $height = $parser->data('priya.terminal.grid.height');
    }
    if(empty($height) || $height == 'auto'){
        $height= $cli->tput('height');
        $parser->data('priya.terminal.grid.height', $height);
    }
    if(empty($color) && $color !== (0 || '0')){
        $color = $parser->data('priya.terminal.grid.color');
    }
    if(empty($color) && $color !== (0 || '0')){
        $color = $cli->tput('color');
        $parser->data('priya.terminal.grid.color', $color);
    }
    if(empty($background) && $background !== (0 || '0')){
        $background = $parser->data('priya.terminal.grid.background');
    }
    if(empty($background) && $background !== (0 || '0')){
        $background = $cli->tput('background');
        $parser->data('priya.terminal.grid.background', $background);
    }
    $grid = $cli->grid($x, $y, $width, $height, $color, $background);
    $function['execute'] = $grid;
    return $function;
}