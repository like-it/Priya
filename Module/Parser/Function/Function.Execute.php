<?php

use Priya\Module\Core\Cli;
use Priya\Module\File\Dir;
use Priya\Module\File;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_execute($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $execute = array_shift($argumentList);
    //executing in the same .proc to 1 file of the same command
    $proc = $parser->data('priya.dir.processor') . sha1($execute . $parser->random()) . '.proc';

    if(!is_dir($parser->data('priya.dir.processor'))){
        mkdir($parser->data('priya.dir.processor'), Dir::CHMOD, true);
    }

    $exec = $execute .' > ' . $proc;

    exec($exec, $output);

    $function['execute'] = '';//$output;

    $file = new File();
    $file->delete($proc);


    return $function;
}