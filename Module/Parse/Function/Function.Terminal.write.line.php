<?php

use Priya\Module\Core\Cli;
use Priya\Module\Parse\Tag;
use Priya\Module\Parse\Cast;
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_terminal_write_line($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if($parser->data('priya.debug4') === true){
//         var_dump($parser->data('priya.debug4'));
//         var_dump($tag);
//         var_dump($argumentList);
//         die;
    }
//     var_dump($argumentList);
    $line = array_shift($argumentList);

    if(is_array($line)){
        var_dump($line);
        die;
    }
    elseif(is_object($line)){
        var_dump($line);
        die;
    }

    if($parser->data('priya.module.terminal.start') === true){
        $grid = $parser->data('priya.module.terminal.grid');
        $x = $parser->data('priya.module.terminal.cursor.position.x');
        $y = $parser->data('priya.module.terminal.cursor.position.y');

        $row = str_split($line, 1);
        $length = count($grid[$y]);
        $counter = 0;
        for($i=0; $i < $length; $i++){
            $grid[$y][$i]['char'] = Cast::SPACE;
        }
        for($i=$x; $i < $length; $i++){
            if(!isset($row[$counter])){
                break;
            }
            $grid[$y][$i]['char'] = $row[$counter];
            $counter++;
        }
        $parser->data('priya.module.terminal.grid', $grid);
        $tag[Tag::EXECUTE] = '';
        $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
        echo $cli->tput('position', array($x, $y));
        $tag[Tag::EXECUTE] = $cli->output($line . PHP_EOL);
        $parser->data('priya.module.terminal.cursor.position.y', $y + 1);
//         $parser->data('priya.module.terminal.cursor.position.x', 0);
//         $parser->data('terminal.cursor.position.x', 0);
        $parser->data('priya.module.terminal.cursor.position.x', $x);
        $parser->data('terminal.cursor.position.x', $x);
        $parser->data('terminal.cursor.position.y', $y + 1);
    } else {
        //not sure if this is working correctly...
        $cli = new Cli($parser->handler(), $parser->route(), $parser->data());
        $tag[Tag::EXECUTE] = $cli->output($line . PHP_EOL);
    }



    return $tag;
}