<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse\Cast;

function function_debug($tag=array(), $parser=null){
    $argumentList = $tag['parameter'];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $debug = array_shift($argumentList);
    $title= array_shift($argumentList);
    $is_export = array_shift($argumentList);

    echo str_repeat('_', 80) . PHP_EOL;
    echo $parser->data('priya.module.parser.document.url') . ':' . $tag['line'] . ':' . $tag['column'] . PHP_EOL;
    echo str_repeat('_', 80) . PHP_EOL;

    $type = getType($debug);
    if(
        in_array(
            $type,
            array(
                Cast::TYPE_ARRAY,
                Cast::TYPE_OBJECT
            )
        )
    ){
        var_export($debug);
    } else {
        echo $debug;
    }
    echo PHP_EOL;
    echo PHP_EOL;
    $tag['execute'] = '';
    return $tag;
}
