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

function function_terminal_screen_goto($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);

    while($y < 0){
        $height = $parser->data('priya.terminal.grid.height');
        if(empty($height)){
            $height = $cli->tput('height');
            $parser->data('priya.terminal.grid.height', $height);
        }
        $y += $height;
    }

    if($parser->data('priya.module.terminal.start') === true){
        $parser->data('priya.module.terminal.cursor.position.x', $x);
        $parser->data('priya.module.terminal.cursor.position.y', $y);
    }

    $tag[Tag::EXECUTE] = $cli->output($cli->tput('position', array($x, $y)));

//
//     $parser->data('priya.terminal.grid.width', $width);
    return $tag;
}