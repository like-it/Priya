<?php

use Priya\Module\Core\Cli;
use Priya\Module\Parse\Tag;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_square($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if($parser->data('priya.debug') == '123'){
        $tag[Tag::EXECUTE] = '';
        if(empty($width)){
            return $tag;
        }
    }

    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);
    $width = array_shift($argumentList);
    $height = array_shift($argumentList);


    if(substr($y, -1) == '%'){
        $percentage = (int) substr($y, 0, -1);
        $percentage_y = $percentage;
        $max = $parser->data('priya.terminal.grid.height');
        $y = (int) ceil($max * ($percentage / 100));
        if($y > $max){
            $y = $max;
        }
    }
    if(substr($x, -1) == '%'){
        $percentage = (int) substr($x, 0, -1);
        $percentage_x = $percentage;
        $max = $parser->data('priya.terminal.grid.width');
        $x = (int) ceil($max * ($percentage / 100));
        if($x > $max){
            $x = $max;
        }
    }

    if(substr($height, -1) == '%'){
        $percentage = (int) substr($height, 0, -1);
        $max = $parser->data('priya.terminal.grid.height');
        $height = (int) ceil($max * ($percentage / 100));
        if($height + $y > $max){
            $height = $max - $y;
        }
    }
    if(substr($width, -1) == '%'){
        $percentage = (int) substr($width, 0, -1);
        $max = $parser->data('priya.terminal.grid.width');
        //if $x + $width == 100 percentage width ==

        $width = (int) ceil($max * ($percentage / 100));

        if($width + $x > $max){
            $width = $max - $x;
        }
    }
    $color = array_shift($argumentList);
    $background = array_shift($argumentList);
    $tag[Tag::EXECUTE] = '';
    if(empty($width)){
        return $tag;
    }
    if(empty($height)){
        return $tag;
    }
    $grid = $parser->data('priya.module.terminal.grid');
    for($i = $y; $i < ($y + $height); $i++){
        if(!isset($grid[$i])){
            continue;
        }
        for($j = $x; $j < ($x + $width); $j++ ){
            if(!isset($grid[$i][$j])){
                continue;
            }
            if(isset($color) || ($color == '0' || 0)){
                $grid[$i][$j]['color'] = $color;
            }
            if(isset($background)|| ($background == '0' || 0)){
                $grid[$i][$j]['background'] = $background;
            }
        }
    }
    $parser->data('priya.module.terminal.cursor.position.x', $x);
    $parser->data('priya.module.terminal.cursor.position.y', $y);
    //needed in .screen we can do terminal.cursor.position or screen.cursor.position
    //terminal.screen.x && terminal.screen.y ?
    $parser->data('priya.module.terminal.grid', $grid);
    return $tag;
}