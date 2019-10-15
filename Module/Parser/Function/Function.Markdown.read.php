<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\File;

function function_markdown_read($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    if(file_exists($argument) === false){
        throw new Exception('Markdown file (' . $argument .') not found');
    }
    $url =  $parser->data('dir.vendor') . 'Parsedown/Parsedown.php';
    require_once $url;
    $parseDown = new Parsedown();
    $read = File::read($argument);
    $function['execute'] = $parseDown->text($read);

    return $function;
}
