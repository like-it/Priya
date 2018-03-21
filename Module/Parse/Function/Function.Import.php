<?php

use Priya\Application;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_import($tag=array(), $parser=null){
    $argumentList = $tag['parameter'];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $directory = array_shift($argumentList);
    $directory = rtrim($directory, Application::DS) . Application::DS;
    $import = $parser->data('priya.module.parser.import');
    $import[] = $directory;
    $parser->data('priya.module.parser.import', $import);
    $tag['execute'] = '';
    return $tag;
}