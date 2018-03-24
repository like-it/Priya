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
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);
    $width = array_shift($argumentList);
    $height = array_shift($argumentList);
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
    $parser->data('priya.module.terminal.grid', $grid);
    return $tag;
}