<?php

use Priya\Module\Core\Cli;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_screen_goto($tag=array(), $parser=null){
    $argumentList = $tag['parameter'];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $x = array_shift($argumentList);
    $y = array_shift($argumentList);

    if($parser->data('priya.module.terminal.start') === true){
        $parser->data('priya.module.terminal.cursor.position.x', $x);
        $parser->data('priya.module.terminal.cursor.position.y', $y);
    }
    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    $tag['execute'] = $cli->output($cli->tput('position', array($x, $y)));
    return $tag;
}