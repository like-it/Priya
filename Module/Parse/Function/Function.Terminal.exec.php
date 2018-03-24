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

function function_terminal_exec($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];

    $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $exec = array_shift($argumentList);
    $protocol = array_shift($argumentList);

    if($protocol === null){
        $explode = explode('://', $exec, 2);
        if(isset($explode[1])){
            $protocol = $explode[0] . '://';
            $exec = $explode[1];
        } else {
            $protocol = 'http://'; //might become json...
        }
    }
    $explode = explode('&&', $exec);
    $exec = implode('&& priya ' . $protocol, $explode);
    $explode = explode('||', $exec);
    $exec = implode('|| priya ' . $protocol, $explode);
    echo 'Executing...';

    /**
     * or even better:
     *
     * write $parser->data() to memory://exec
     */

    exec('priya ' . $protocol . $exec, $output);

//     var_dump($output);
    /*
    foreach($output as $line){
        $string = '{terminal.write.line("' . $line . '")}';
        $parser->compile($string, $parser->data(), false);
    }
    */
    $tag['execute'] = implode("\n", $output);
    return $tag;
    /*
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
    */
}