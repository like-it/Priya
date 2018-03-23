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

function function_terminal_read_line($tag, $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = array_shift($argumentList);
    $hidden = array_shift($argumentList);
    $timeout = array_shift($argumentList);
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    if($parser->data('priya.module.terminal.start' === true)){
        $x = $parser->data('priya.module.terminal.cursor.position.x');
        $y = $parser->data('priya.module.terminal.cursor.position.y');
        $grid = $parser->data('priya.module.terminal.grid');
        $cell = $grid[$y][$x];
        echo $cli->color($cell['color'], $cell['background']);
    }
    $tag[Tag::EXECUTE] = $cli->input($input, $hidden, $timeout) . PHP_EOL;
    return $tag;
}