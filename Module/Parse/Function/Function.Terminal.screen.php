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

function function_terminal_screen($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $grid = array_shift($argumentList);
    if(empty($grid) && $parser->data('priya.module.terminal.start') === true){
        $grid = $parser->data('priya.module.terminal.grid');
    }
    $timeout = array_shift($argumentList);
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $tag[Tag::EXECUTE] = $cli->screen($grid, $timeout);
    return $tag;
}