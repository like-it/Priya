<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

use Priya\Module\Parser;

$parse = function(){
    $url = $this->parameter('url') ? $this->parameter('url') : $this->parameter(3);

    if(empty($url) || file_exists($url) === false){
        $url = $this->data('dir.module.data') . "Parser.json";
    }
    $parser = new Parser($this->data());
    $parser->route($this->route());
    $read = $parser->read($url);
    echo $this->object($parser->data('test'), 'json');
};
$parse();