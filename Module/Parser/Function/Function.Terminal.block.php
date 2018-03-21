<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_block($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $block = array_shift($argumentList);
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $content = explode(PHP_EOL, $block->content);

    $parser->data('priya.terminal.screen.width', $cli->tput('width'));;
    $parser->data('priya.terminal.screen.height', $cli->tput('height'));

    $grid = $parser->data('priya.terminal.screen.grid.content');
    if(empty($grid)){
        $color = 9;
        $background = 0;
        $grid = $cli->grid($color, $background);
    }

    /*
    $grid = array();

    for($y=0; $y < $parser->data('priya.terminal.screen.height'); $y++){
        for($x=0; $x < $parser->data('priya.terminal.screen.width'); $x++){
            $char = ' ';
            $color = rand(0, 15);
            $background = rand(0,15);
            $grid[$y][$x]['x'] = $x;
            $grid[$y][$x]['y'] = $y;
            $grid[$y][$x]['color'] = $color;
            $grid[$y][$x]['background'] = $background;
            $grid[$y][$x]['char'] = $char;
        }
    }
    $parser->data('priya.terminal.screen.grid', $grid);
    */
    $cli->screen($parser->data('priya.terminal.screen.grid.content'));


    var_Dump($parser->data('priya.terminal'));
    die;



    $result = '';
    for($y=2; $y < 60; $y++){
        $result .= $cli->tput('position', array(150, $y));
        $result .= $cli->color($block->color, $block->background);
        $result .= '[block()]';
    }

    $counter = 0;
//     $result = '';
//     $result .= $cli->color($block->color, $block->background);

    /*
    for($y = $block->y; $y < $block->height; $y++){
//         $result .= $cli->tput('position', array($block->x, $y));
        if(isset($content[$counter])){
            $row = $content[$counter] . str_repeat(' ', $block->width - strlen($content[$counter]));
//             $cli->output($row);
            $result .= $row;

        } else {
            $row = str_repeat(' ', $block->width);
//             $cli->output($row);
            $result .= $row;
        }
        $result .= PHP_EOL;
        $counter++;
    }
    */
    echo $result;
    $counter++;
    $parser->data('block', $counter);
    usleep(20);
    $result .= PHP_EOL . 'count:' . $parser->data('block') . PHP_EOL;
    $function['execute'] = $result;

//     $function['execute'] = $cli->tput('height');
    return $function;
}