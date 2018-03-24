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

function function_terminal_screen_move_up($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);
    $w = array_shift($argumentList);
    $h = array_shift($argumentList);
    $up = array_shift($argumentList);
    if(intval($up) == 0){
        $up = 1;
    }

    while($y < 0){
        $height = $parser->data('priya.terminal.grid.height');
        if(empty($height)){
            $height = $cli->tput('height');
            $parser->data('priya.terminal.grid.height', $height);
        }
        $y += $height;
    }

    if($parser->data('priya.module.terminal.start') === true){
        $grid = $parser->data('priya.module.terminal.grid');
        for($i= $y; $i <= ($y+$h); $i++){
            $step = $i - $up;
            for($j = $x; $j <= ($x + $w); $j++){
                if(isset($grid[$i][$j]['char'])){
                    $grid[$step][$j]['char'] = $grid[$i][$j]['char'];
                    $grid[$i][$j]['char'] = '>'; //Tag::SPACE;
                }

            }
        }
        $parser->data('priya.module.terminal.grid', $grid);
    }

    $tag[Tag::EXECUTE] = $cli->output($cli->tput('position', array($x, $y)));

//
//     $parser->data('priya.terminal.grid.width', $width);
    return $tag;
}