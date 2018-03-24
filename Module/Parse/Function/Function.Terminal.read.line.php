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
//     $debug = debug_backtrace(true);
//     var_dump($debug[0]['args']);
    $execute = $cli->input($input, $hidden, $timeout) . PHP_EOL;
    $tag[Tag::EXECUTE] = $execute;
    /*
    if(!empty($tag[Tag::ATTRIBUTE])){
        $parser->data($tag[Tag::ATTRIBUTE], $execute);
        var_dump($parser->data($tag[Tag::ATTRIBUTE]));
//         die;
    }
    */
    /*
    if($parser->data('priya.module.terminal.start' === true)){
        $grid = $parser->data('priya.module.terminal.grid');
    }
    */
    return $tag;
}