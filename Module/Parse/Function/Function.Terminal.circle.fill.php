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

function function_terminal_circle_fill($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if($parser->data('priya.debug2') == true){
//         var_dump($argumentList);
//         die;
    }
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);
    $radius = array_shift($argumentList);
    $color = array_shift($argumentList);
    $background = array_shift($argumentList);
    $tag[Tag::EXECUTE] = '';
    $grid = $parser->data('priya.module.terminal.grid');

    $pos_x = $radius - 1;
    $pos_y = 0;
    $dx = 1;
    $dy = 1;

    $err = $dx - ($radius << 1);

    $width = $parser->data('terminal.screen.width');
    $height = $parser->data('terminal.screen.height');

    while($pos_x >= $pos_y){
        $target_x = $x + $pos_x;
        $target_y = $y + $pos_y / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x + $pos_y;
        $target_y = $y + $pos_x / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x - $pos_y;
        $target_y = $y + $pos_x / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x - $pos_x;
        $target_y = $y + $pos_y / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x - $pos_x;
        $target_y = $y - $pos_y / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x - $pos_y;
        $target_y = $y - $pos_x / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x - $pos_y;
        $target_y = $y - $pos_x / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x + $pos_y;
        $target_y = $y - $pos_x / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        $target_x = $x + $pos_x;
        $target_y = $y - $pos_y / ($width / $height);
        if(isset($grid[$target_y][$target_x])){
            if(isset($color) || ($color == '0' || 0)){
                $grid[$target_y][$target_x]['color'] = $color;
            }
            if(isset($background) || ($background== '0' || 0)){
                $grid[$target_y][$target_x]['background'] = $background;
            }
        }
        if($err <= 0){
            $pos_y++;
            $err += $dy;
            $dy += 2;
        }
        if($err > 0){
            $pos_x--;
            $dx += 2;
            $err += $dx - ($radius << 1);
        }
    }
    $parser->data('priya.module.terminal.grid', $grid);

    while($radius > 0){
        $radius--;
        $tag[Tag::PARAMETER][2] = $radius;
//         var_dump($radius);
        $tag = function_terminal_circle_fill($tag, $parser);
        $radius = $tag[Tag::PARAMETER][2];
        if($radius <= 0){
            break;
        }
    }
    /*
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
            if(isset($background) || ($background == '0' || 0)){
                $grid[$i][$j]['background'] = $background;
//                 if($parser->data('priya.debug2') === true){
//                     echo $i . '.' . $j . '.' . $background . '.' . $color . PHP_EOL;
//                     var_dump($i);
//                     var_dump($j);
//                 }
            }
        }
    }
    */
    if($parser->data('priya.debug2') === true){
//         die;
    }
    $parser->data('priya.module.terminal.cursor.position.x', $x);
    $parser->data('priya.module.terminal.cursor.position.y', $y);
    $parser->data('terminal.cursor.position.x', $x);
    $parser->data('terminal.cursor.position.y', $y);
    //needed in .screen we can do terminal.cursor.position or screen.cursor.position
    //terminal.screen.x && terminal.screen.y ?
//     $parser->data('priya.module.terminal.grid', $grid);
    return $tag;
}